<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\EngineManufacturer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EngineManufacturerController extends Controller
{
    private const PERMISSION_ENTITY = 'engine_manufacturer';

    // GET /engine_manufacturers
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return EngineManufacturer::findByName($user->tenant->id, $request->query('filterByName'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage);
    }

    // GET /engine_manufacturers/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = EngineManufacturer::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }


    // POST /engine_manufacturers
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('engine_manufacturers')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['required',$uniqueTenantRule,'string'],
        ];
        $validated = $request->validate($fields);

        $model = new EngineManufacturer($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /engine_manufacturers/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = EngineManufacturer::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        $uniqueTenantRule = Rule::unique('engine_manufacturers')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['string'],
        ];
        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = ['required',$uniqueTenantRule,'string'];
        }

        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /engine_manufacturers/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = EngineManufacturer::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
