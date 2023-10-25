<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Vehicle;
use App\Models\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    private const PERMISSION_ENTITY = 'warranty';
    private const PERMISSION_FLEET = 'fleet';

    // GET /warrenties
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fleetPermissionsFilter = $user->getModelPermissionsFilter(self::PERMISSION_FLEET);
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return Warranty::findByName($user->tenant->id, $request->query('filterByName'), $request->query('filterByVehicle'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage, $fleetPermissionsFilter);
    }

    // GET /warrenties/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Warranty::with('vehicle')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->vehicle->fleet->id)) {
            abort(401, 'Unauthorized');
        }
        return response()->json($model);
    }


    // POST /warrenties
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'vehicle_id' => ['required','integer'],
            'name' => ['required','string'],
            'desc' => ['required','string'],
            'ending_date' => ['date'],
            'ending_mileage' => ['integer'],
            'mileage_total' => ['integer'],
        ];
        $validated = $request->validate($fields);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
            abort(403, 'Access denied');
        }

        $model = new Warranty($validated);
        $model->tenant_id = $user->tenant->id;
        $model->vehicle_id = $vehicle->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /warrenties/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Warranty::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->vehicle->fleet->id)) {
            abort(401, 'Unauthorized');
        }

        $fields = [
            'vehicle_id' => ['integer'],
            'name' => ['string'],
            'desc' => ['string'],
            'ending_date' => ['date','nullable'],
            'ending_mileage' => ['integer','nullable'],
            'mileage_total' => ['integer','nullable'],
        ];
        if ($model->vehicle_id != $request->get('vehicle_id') && !is_null($request->get('vehicle_id'))) {
            $vehicle = Vehicle::findOrFail($request->get('vehicle_id'));
            if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
                abort(403, 'Access denied');
            }
            $fields['vehicle_id'] = ['required','integer'];
        }

        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /warrenties/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = Warranty::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
