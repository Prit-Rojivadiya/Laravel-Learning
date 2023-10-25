<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    private const PERMISSION_ENTITY = 'client';

    // GET /clients
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $modelPermissionsFilter = $user->getModelPermissionsFilter(self::PERMISSION_ENTITY);
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        if ($request->get('paginate') == 'false' || $request->get('paginate') == false) {
            $itemsPerPage = 10000;  //TODO: Not sure if this is right, used when view queries for dropdown lists
        }
        $tenantId = $user->tenant->id;
        if ($request->has('tenantId') && $user->isSuperAdminUser()) {
            $tenantId = $request->get('tenantId');
        }
        return Client::findByName($tenantId, $request->query('filterByName'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage, $modelPermissionsFilter);
    }

    // GET /clients/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__, $id)) {
            abort(401, 'Unauthorized');
        }
        $model = Client::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }


    // POST /clients
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('clients')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['required',$uniqueTenantRule,'string'],
            'abbrv' => ['required',$uniqueTenantRule,'string'],
        ];
        $validated = $request->validate($fields);

        $model = new Client($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /clients/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__, $id)) {
            abort(401, 'Unauthorized');
        }
        $model = Client::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        $uniqueTenantRule = Rule::unique('clients')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['string'],
            'abbrv' => ['string'],
        ];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->abbrv != $request->get('abbrv') && !is_null($request->get('abbrv'))) {
            $fields['abbrv'] = [$uniqueTenantRule, 'string'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /clients/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = Client::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }

    // PUT /clients/movetotenant/{id}/{newtenantid}
    public function movetotenant(Request $request, $id, $newtenantid)
    {
        $user = $request->user();
        if (!Tenant::allowedAccessTranzITOnly($user)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_ENTITY, 'store')) {
            abort(401, 'Unauthorized');
        }
        $model = Client::findOrFail($id);
        $model->movetotenant($newtenantid);

        return response()->json($model);
    }

}
