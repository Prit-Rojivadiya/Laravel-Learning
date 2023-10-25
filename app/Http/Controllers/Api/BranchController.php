<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    private const PERMISSION_ENTITY = 'branch';

    // GET /branches
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
        return Branch::findByName($tenantId, $request->query('filterByName'), $request->query('filterByClient'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage, $modelPermissionsFilter);
    }

    // GET /branches/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__, $id)) {
            abort(401, 'Unauthorized');
        }
        $model = Branch::with('client')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }


    // POST /branches
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('branches')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['required',$uniqueTenantRule,'string'],
            'client_id' => ['required','integer'],
        ];
        $validated = $request->validate($fields);

        $model = new Branch($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /branches/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__, $id)) {
            abort(401, 'Unauthorized');
        }
        $model = Branch::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        $uniqueTenantRule = Rule::unique('branches')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['string'],
        ];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->client_id != $request->get('client_id')) {
            $fields['client_id'] = ['required','integer'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /branches/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = Branch::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
