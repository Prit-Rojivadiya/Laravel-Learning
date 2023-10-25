<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Models\PreventiveMaintenance;
use App\Models\PreventiveMaintenanceTemplate;
use App\Models\Tenant;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class PreventiveMaintenanceController extends Controller
{
    private const PERMISSION_ENTITY = 'preventive_maintenance';
    private const PERMISSION_FLEET = 'fleet';

    // GET /preventive_maintenances
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

        $clientId = $request->has('filterByClient') ? $request->get('filterByClient') : null;
        $branchId = $request->has('filterByBranch') ? $request->get('filterByBranch') : null;
        $fleetId = $request->has('filterByFleet') ? $request->get('filterByFleet') : null;
        $vehicleId = $request->has('filterByVehicle') ? $request->get('filterByVehicle') : null;
        $name = $request->has('filterByName') ? $request->get('filterByName') : null;
        $status = $request->has('filterByStatus') ? $request->get('filterByStatus') : null;
        $pmDueTypeId = $request->has('filterByPMDueType') ? $request->get('filterByPMDueType') : null;
        $includeMeterReading = $request->has('includeMeterReading') ? $request->get('includeMeterReading') : false;
        if ($includeMeterReading == 'true') {
            $includeMeterReading = true ;
        }
        else {
            $includeMeterReading = false;
        }
        return PreventiveMaintenance::index($user->tenant->id, $clientId, $branchId, $fleetId, $vehicleId, $name, $status, $includeMeterReading, $pmDueTypeId, $paginateOptions, $fleetPermissionsFilter);
    }

    // GET /preventive_maintenances/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = PreventiveMaintenance::with(['vehicle','PreventiveMaintenanceTemplate','repairOrder','repairOrderType','systemMeterType','systemPMDueType'])->findOrFail($id);
        $latestMeterReading = MeterReading::latestByVehicle($model->vehicle_id);
        if ($latestMeterReading->count() == 1) {
            $model->latestMeterReading = $latestMeterReading[0]->meter_reading;
        }
        else {
            $model->latestMeterReading = null;
        }

        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->vehicle->fleet->id)) {
            abort(401, 'Unauthorized');
        }

        return response()->json($model);
    }

    // POST /preventive_maintenances
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'name' => ['string'],
            'vehicle_id' => ['required','integer'],
            'preventive_maintenance_template_id' => ['required','integer'],
            'repair_order_type_id' => ['required','integer'],
            'repair_order_id' => ['integer'],
            'system_meter_type_id' => ['required','integer'],
            'system_p_m_due_type_id' => ['required','integer'],
            'recurring' => ['required','boolean'],
            'length_meters' => ['required','integer'],
            'length_days' => ['required','integer'],
            'start_date' => ['required','date'],
            'due_date' => ['date'],
            'completed_date' => ['date'],
            'start_at_meter' => ['required','integer'],
            'due_at_meter' => ['integer'],
            'completed_at_meter' => ['integer','nullable'],
            'desc' => ['string','nullable'],
        ];
        $validated = $request->validate($fields);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
            abort(403, 'Access denied');
        }
        $pm_template = PreventiveMaintenanceTemplate::findOrFail($validated['preventive_maintenance_template_id']);
        if (!Tenant::allowedAccess($user->tenant, $pm_template->tenant)) {
            abort(403, 'Access denied');
        }

        $model = new PreventiveMaintenance($validated);
        $model->tenant_id = $user->tenant->id;
        $model->vehicle_id = $vehicle->id;
        $model->preventive_maintenance_template_id = $pm_template->id;
        $model->save();

        if($model->completed_date) {
            $model->closePM();
        }

        return response()->json($model);
    }

    // PUT /preventive_maintenances/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = PreventiveMaintenance::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->vehicle->fleet->id)) {
            abort(401, 'Unauthorized');
        }

        $pmAlreadyClosed = false;
        if ($model->completed_date) {
            $pmAlreadyClosed = true;
        }

        $fields = [
            'name' => ['string'],
            'recurring' => ['boolean'],
            'length_meters' => ['integer'],
            'length_days' => ['integer'],
            'start_date' => ['date'],
            'due_date' => ['date'],
            'completed_date' => ['date','nullable'],
            'start_at_meter' => ['integer'],
            'due_at_meter' => ['integer'],
            'completed_at_meter' => ['integer','nullable'],
            'desc' => ['string','nullable'],
        ];

        if ($model->preventive_maintenance_template_id != $request->get('preventive_maintenance_template_id')) {
            $fields['preventive_maintenance_template_id'] = ['required','integer'];
        }
        if ($model->repair_order_type_id != $request->get('repair_order_type_id')) {
            $fields['repair_order_type_id'] = ['required','integer'];
        }
        if ($model->repair_order_id != $request->get('repair_order_id')) {
            $fields['repair_order_id'] = ['required','integer'];
        }
        if ($model->system_meter_type_id != $request->get('system_meter_type_id')) {
            $fields['system_meter_type_id'] = ['required','integer'];
        }
        if ($model->system_p_m_due_type_id != $request->get('system_p_m_due_type_id')) {
            $fields['system_p_m_due_type_id'] = ['required','integer'];
        }
        if ($model->vehicle_id != $request->get('vehicle_id') && !is_null($request->get('vehicle_id'))) {
            $vehicle = Vehicle::findOrFail($request->get('vehicle_id'));
            if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
                abort(403, 'Access denied');
            }
            $fields['vehicle_id'] = ['required','integer'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);

        if ($model->completed_date && !$pmAlreadyClosed) {
            $model->closePM(null, null, true);
        }

        return response()->json($model);
    }

    // DELETE /preventive_maintenances/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = PreventiveMaintenance::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
