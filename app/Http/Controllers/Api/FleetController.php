<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fleet;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FleetController extends Controller
{
    private const PERMISSION_ENTITY = 'fleet';

    // GET /fleets
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $modelPermissionsFilter = $user->getModelPermissionsFilter(self::PERMISSION_ENTITY);
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        $tenantId = $user->tenant->id;
        if ($request->has('tenantId') && $user->isSuperAdminUser()) {
            $tenantId = $request->get('tenantId');
        }
        return Fleet::findByName($tenantId, $request->query('filterByName'), $request->query('filterByBranch'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage, $modelPermissionsFilter);
    }

    // GET /fleets/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__, $id)) {
            abort(401, 'Unauthorized');
        }
        $model = Fleet::with('branch')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }


    // POST /fleets
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('fleets')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['required',$uniqueTenantRule,'string'],
            'fleet_number' => ['required',$uniqueTenantRule,'string'],
            'branch_id' => ['required','integer'],
        ];
        $validated = $request->validate($fields);

        $model = new Fleet($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /fleets/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__, $id)) {
            abort(401, 'Unauthorized');
        }
        $model = Fleet::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        $uniqueTenantRule = Rule::unique('fleets')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['string'],
        ];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->fleet_number != $request->get('fleet_number') && !is_null($request->get('fleet_number'))) {
            $fields['fleet_number'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->branch_id != $request->get('branch_id')) {
            $fields['branch_id'] = ['required','integer'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /fleets/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = Fleet::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
