<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    private const PERMISSION_ENTITY = 'vehicle';
    private const PERMISSION_FLEET = 'fleet';

    // GET /vehicles
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
        $filterByName = $request->has('filterByName') ? $request->get('filterByName') : null;

        return Vehicle::findByName($user->tenant->id, $filterByName, $clientId, $branchId, $fleetId, $sort, $sortDir, $itemsPerPage, $fleetPermissionsFilter);

    }

    // GET /vehicles/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Vehicle::with('fleet', 'vehicleType', 'engineManufacturer')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->fleet->id)) {
            abort(401, 'Unauthorized');
        }

        return response()->json($model);
    }

    // POST /vehicles
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'vehicle_number' => ['required','string',Rule::unique('vehicles')->where(function ($query) use ($request) {
                return $query->where('fleet_id', $request->get('fleet_id'));
            })],
            'fleet_id' => ['required','integer'],
            'vehicle_type_id' => ['required','integer'],
            'year' => ['numeric'],
            'make' => ['string'],
            'model' => ['string'],
            'vin' => ['string'],
            'tire_size' => ['string'],
            'license_plate_number' => ['string'],
            'license_state' => ['string'],
            'engine_serial_number' => ['string'],
            'purchase_price' => ['numeric'],
            'in_service_date' => ['date'],
            'engine_manufacturer_id' => ['integer'],
        ];
        $validated = $request->validate($fields);

        $model = new Vehicle($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /vehicles/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Vehicle::with('fleet')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->fleet->id)) {
            abort(401, 'Unauthorized');
        }

        $fields = [
            'year' => ['numeric','nullable'],
            'make' => ['string','nullable'],
            'model' => ['string','nullable'],
            'vin' => ['string','nullable'],
            'tire_size' => ['string','nullable'],
            'license_plate_number' => ['string','nullable'],
            'license_state' => ['string','nullable'],
            'engine_serial_number' => ['string','nullable'],
            'purchase_price' => ['numeric','nullable'],
            'in_service_date' => ['date','nullable'],
            'engine_manufacturer_id' => ['integer','nullable'],
        ];

        if ($model->vehicle_number != $request->get('vehicle_number') && !is_null($request->get('vehicle_number'))) {
            $fields['vehicle_number'] = ['string', Rule::unique('vehicles')->where(function ($query) use ($request) {
                return $query->where('fleet_id', $request->get('fleet_id'));
            })];
        }
        if ($model->fleet_id != $request->get('fleet_id')) {
            $fields['fleet_id'] = ['required','integer'];
        }
        if ($model->vehicle_type_id != $request->get('vehicle_type_id')) {
            $fields['vehicle_type_id'] = ['required','integer'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /vehicles/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = Vehicle::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
