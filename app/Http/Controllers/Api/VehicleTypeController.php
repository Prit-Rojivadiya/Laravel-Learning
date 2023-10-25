<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleTypeController extends Controller
{
    private const PERMISSION_ENTITY = 'vehicle_type';

    // GET /vehicle_types
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return VehicleType::findByName($user->tenant->id, $request->query('filterByName'), $request->query('filterBySystemVehicleType'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage);
    }

    // GET /vehicle_types/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = VehicleType::with('systemVehicleType')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }


    // POST /vehicle_types
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('vehicle_types')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['required',$uniqueTenantRule,'string'],
            'system_vehicle_type_id' => ['required','integer'],
            'desc' => ['required','string'],
        ];
        $validated = $request->validate($fields);

        $model = new VehicleType($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /vehicle_types/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = VehicleType::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        $uniqueTenantRule = Rule::unique('vehicle_types')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['string'],
            'desc' => ['string'],
        ];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->system_vehicle_type_id != $request->get('system_vehicle_type_id')) {
            $fields['system_vehicle_type_id'] = ['required','integer'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /vehicle_types/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = VehicleType::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
