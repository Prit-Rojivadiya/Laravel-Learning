<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenance;
use App\Models\RepairOrder;
use App\Models\Tenant;
use App\Models\Vehicle;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RepairOrderController extends Controller
{
    private const PERMISSION_ENTITY = 'repair_order';
    private const PERMISSION_FLEET = 'fleet';

    // GET /repair_orders
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fleetPermissionsFilter = $user->getModelPermissionsFilter(self::PERMISSION_FLEET);

        //server side pagination
        $paginateOptions = null;
        $paginate = $request->has('paginate') ? $request->get('paginate') : false;
        $sort = $request->has('_sort') ? $request->get('_sort') : null;
        $sortDir = $request->has('_sort_dir') ? $request->get('_sort_dir') : 'desc';
        $page = $request->has('_page') ? $request->get('_page') : 1;
        $itemsPerPage = $request->has('_items_per_page') ? $request->get('_items_per_page') : 100000;
        if($paginate === 'false') {
            $paginate = false;
        }
        if ($paginate) {
            $paginateOptions['sort'] = $sort;
            $paginateOptions['sortDir'] = $sortDir;
            $paginateOptions['page'] = $page;
            $paginateOptions['itemsPerPage'] = $itemsPerPage;
        }

        $roNumber = $request->has('filterByRONumber') ? $request->get('filterByRONumber') : null;
        $invoiceNumber = $request->has('filterByInvoiceNumber') ? $request->get('filterByInvoiceNumber') : null;
        $clientId = $request->has('filterByClient') ? $request->get('filterByClient') : null;
        $branchId = $request->has('filterByBranch') ? $request->get('filterByBranch') : null;
        $fleetId = $request->has('filterByFleet') ? $request->get('filterByFleet') : null;
        $vehicleId = $request->has('filterByVehicle') ? $request->get('filterByVehicle') : null;
        $desc = $request->has('filterByName') ? $request->get('filterByName') : null;
        $repairOrderStatusId = $request->has('filterByStatus') ? $request->get('filterByStatus') : null;

        return RepairOrder::index($user->tenant->id, $roNumber, $invoiceNumber, $clientId, $branchId, $fleetId, $vehicleId, $desc, $repairOrderStatusId, $paginateOptions, $fleetPermissionsFilter);
    }

    // GET /repair_orders/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        /** @var RepairOrder $model */
        $model = RepairOrder::with(['vehicle','vendor','preventiveMaintenances','repairOrderStatus'])->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->vehicle->fleet->id)) {
            abort(401, 'Unauthorized');
        }

        $invoices = [];
        foreach ($model->getMedia('invoices') as $invoice) {
            $invoices[] = [
                'id' => $invoice->getKey(),
                'name' => $invoice->file_name,
                'url' => $invoice->getTemporaryUrl(Carbon::now()->addHours(5)),
            ];
        }

        $images = [];
        foreach ($model->getMedia('images') as $image) {
            $images[] = [
                'id' => $image->getKey(),
                'name' => $image->file_name,
                'is_image' => strpos($image->mime_type, 'image') !== false,
                'url' => $image->getTemporaryUrl(Carbon::now()->addHours(5)),
            ];
        }

        $model->setAttribute('invoices', $invoices);
        $model->setAttribute('images', $images);

        return response()->json($model);
    }

    // POST /repair_orders
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'desc' => ['required','string'],
            'vehicle_id' => ['required','integer'],
            'vendor_id' => ['required','integer'],
            'repair_order_status_id' => ['required','integer'],
            'needs_approval' => ['boolean','nullable'],
            'approval_received_date' => ['date', 'nullable'],
            'start_date' => ['date','nullable'],
            'completed_date' => ['date','nullable'],
            'meter_reading' => ['integer','nullable'],
            'meter_reading_id' => ['integer','nullable'],
            'invoice_number' => ['string','nullable'],
            'copy_of_purchase_order' => ['string','nullable'],
            'total_price' => ['numeric','nullable'],
            'notes' => ['string','nullable'],
        ];
        $validated = $request->validate($fields);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
            abort(403, 'Access denied');
        }
        $vendor = Vendor::findOrFail($request->get('vendor_id'));
        if (!Tenant::allowedAccess($user->tenant, $vendor->tenant)) {
            abort(403, 'Access denied');
        }
        $pm = null;

        $newRoNumber = $user->tenant->incRONumber();
        $roIsUnique = false;
        $loopCheck = 0;
        $loopCheckCount = 100000;
        while (!$roIsUnique && $loopCheck < $loopCheckCount ) {
            $query = RepairOrder::where('repair_orders.ro_number', $newRoNumber)->withTrashed();
            $tRO = $query->get();
            if ($tRO->count() == 0) {
                $roIsUnique = true;
            }
            else {
                $newRoNumber = $user->tenant->incRONumber();
            }
            $loopCheck = $loopCheck+1;
        }
        if ($loopCheck >= $loopCheckCount) {
            abort(403, 'Failed to get unique RO Number for this tenant');
        }

        $model = new RepairOrder($validated);
        $model->ro_number = $newRoNumber;
        $model->tenant_id = $user->tenant->id;
        $model->vehicle_id = $vehicle->id;
        $model->vendor_id = $vendor->id;
        $model->save();
        if (isset($model->meter_reading)) {
            //Now create or update the related meter event
            $meterReading = $model->updateMeterReading();
            $model->meter_reading_id = $meterReading->id;
            $model->save();
        }
        $linkToPMId = $request->get('linkToPMId');
        if ($linkToPMId) {
            //Link the PM to the new Repair Order
            $pm = PreventiveMaintenance::find($request->get('linkToPMId'));
            if ($pm) {
                $pm->repair_order_id = $model->id;
                $pm->save();
            }
        }
        if($model->completed_date) {
            $model->closeLinkedPMs();
        }

        return response()->json($model);
    }

    // PUT /repair_orders/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = RepairOrder::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->vehicle->fleet->id)) {
            abort(401, 'Unauthorized');
        }

        $fields = [
            'desc' => ['string'],
            'needs_approval' => ['boolean','nullable'],
            'approval_received_date' => ['date', 'nullable'],
            'start_date' => ['date','nullable'],
            'completed_date' => ['date','nullable'],
            'meter_reading' => ['integer','nullable'],
            'meter_reading_id' => ['integer','nullable'],
            'invoice_number' => ['string','nullable'],
            'copy_of_purchase_order' => ['string','nullable'],
            'total_price' => ['numeric','nullable'],
            'notes' => ['string','nullable'],
        ];

        if ($model->repair_order_status_id != $request->get('repair_order_status_id')) {
            $fields['repair_order_status_id'] = ['required','integer'];
        }
        if ($model->vehicle_id != $request->get('vehicle_id') && !is_null($request->get('vehicle_id'))) {
            $vehicle = Vehicle::findOrFail($request->get('vehicle_id'));
            if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
                abort(403, 'Access denied');
            }
            $fields['vehicle_id'] = ['required','integer'];
        }
        if ($model->vendor_id != $request->get('vendor_id') && !is_null($request->get('vendor_id'))) {
            $vendor = Vendor::findOrFail($request->get('vendor_id'));
            if (!Tenant::allowedAccess($user->tenant, $vendor->tenant)) {
                abort(403, 'Access denied');
            }
            $fields['vendor_id'] = ['required','integer'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        if (isset($model->meter_reading)) {
            //Now create or update the related meter event
            $meterReading = $model->updateMeterReading();
            $model->meter_reading_id = $meterReading->id;
            $model->save();
        }
        $linkToPMId = $request->get('linkToPMId');
        if ($linkToPMId) {
            //Link the PM to the new Repair Order
            $pm = PreventiveMaintenance::find($request->get('linkToPMId'));
            if ($pm) {
                $pm->repair_order_id = $model->id;
                $pm->save();
            }
        }
        if($model->completed_date) {
            $model->closeLinkedPMs();
        }

        return response()->json($model);
    }

    // DELETE /repair_orders/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = RepairOrder::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }

    // POST /repair_orders/v1/import
    // API to receive RO's from external api calls, such as intelgic for automated data entry
    public function importv1(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, 'store')) {
            abort(401, 'Unauthorized');
        }

        $results = [
            "success" => true
        ];

        Log::info("RO importv1() request body: " . json_encode($request->all(), JSON_PRETTY_PRINT));
        return response()->json($results);
    }


}
