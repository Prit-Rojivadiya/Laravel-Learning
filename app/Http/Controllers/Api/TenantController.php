<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    private const PERMISSION_ENTITY = 'tenant';

    // GET /tenants
    public function index(Request $request)
    {
        $user = $request->user();
        if (!Tenant::allowedAccessTranzITOnly($user)) {
            // Just return current user's tenant only by default
            $tTenant = ['id' => $user->tenant->id, 'name' => $user->tenant->name];
            return [$tTenant];
            //abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }

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

        $name = $request->has('filterByName') ? $request->get('filterByName') : null;
        $tenants = Tenant::index($user->tenant->id, $name, $paginateOptions);

        $includeDefaultUser = $request->has('defaultUser') ? $request->get('defaultUser') : false;
        if ($includeDefaultUser) {
            foreach ($tenants as $tTenant) {
                if ($tTenant->default_user_id) {
                    $tUser = User::find($tTenant->default_user_id);
                    if ($tUser) {
                        $tTenant->default_user_name = $tUser->name;
                    }
                }
            }
        }
        return $tenants;
    }

    // GET /tenants/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!Tenant::allowedAccessTranzITOnly($user)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Tenant::findOrFail($id);
        return response()->json($model);
    }


    // POST /tenants
    public function store(Request $request)
    {
        $user = $request->user();
        if (!Tenant::allowedAccessTranzITOnly($user)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'name' => ['required','string', 'required'],
            'abbrv' => ['required','string', 'required'],
        ];
        $validated = $request->validate($fields);
        $model = new Tenant($validated);
        $model->ro_number = 1; //set default ro_number
        $model->save();

        $model->populateTenantDefaults();

        return response()->json($model);
    }

    // PUT /tenants/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!Tenant::allowedAccessTranzITOnly($user)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Tenant::findOrFail($id);
        $fields = [
            'name' => ['string'],
            'abbrv' => ['string'],
        ];
        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = ['string', 'required'];
        }
        if ($model->abbrv != $request->get('abbrv') && !is_null($request->get('abbrv'))) {
            $fields['abbrv'] = ['string', 'required'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);

        return response()->json($model);
    }

    // DELETE /tenants/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = Tenant::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }

    // GET /tenants/name/{id}
    public function name(Request $request, $id)
    {
        $user = $request->user();
        if ($user->tenant->id !== (int) $id) {
            abort(403, 'Access denied');
        }
        $model = Tenant::findOrFail($id);
        $tTenant = ['id' => $model->id, 'name' => $model->name, 'logo_path' => $model->logo_path];
        return response()->json($tTenant);
    }

    // PUT /tenants/defaults/{id}
    public function defaults(Request $request, $id)
    {
        $user = $request->user();
        if (!Tenant::allowedAccessTranzITOnly($user)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_ENTITY, 'store')) {
            abort(401, 'Unauthorized');
        }
        $model = Tenant::findOrFail($id);
        $defaultSourceTenantId = $request->has('defaultSourceTenantId') ? $request->get('defaultSourceTenantId') : null;
        $model->populateTenantDefaults($defaultSourceTenantId);

        return response()->json($model);
    }



}
