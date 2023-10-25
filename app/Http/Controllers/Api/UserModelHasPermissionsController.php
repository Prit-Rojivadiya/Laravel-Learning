<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserModelHasPermissions;
use Illuminate\Http\Request;

class UserModelHasPermissionsController extends Controller
{

    private const PERMISSION_ENTITY = 'user_model_has_permission';

    // GET /user_model_has_permissions
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        #$tenantId = $user->tenant->id;

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

        $tUserId = $request->has('userId') ? $request->get('userId') : null;
        $modelType = $request->has('modelType') ? $request->get('modelType') : null;
        $byType = $request->has('byType') ? $request->get('byType') : false;

        $tUser = User::findOrFail($tUserId);
        $tenantId = $tUser->tenant->id;

        return UserModelHasPermissions::index($tenantId, $tUserId, $modelType, $byType, $paginateOptions);
    }

    // POST /user_model_has_permissions
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'userId' => ['required','integer'],
            'modelType' => ['required','string'],
        ];
        $validated = $request->validate($fields);

        $tUser = User::findOrFail($validated['userId']);
        if (!Tenant::allowedAccess($user->tenant, $tUser->tenant) && !$user->isSuperAdminUser()) {
            abort(403, 'Access denied');
        }
        $modelType = $request->get('modelType');
        $permissions = $request->get('permissions') ? $request->get('permissions') : [];


        //First delete previous permissions entries for this user and modelType
        UserModelHasPermissions::where('user_id', $tUser->id)
            ->where('model_type', $modelType)
            ->forceDelete();

        //Now add fresh entries
        $results = [];
        foreach ($permissions as $modelPermission) {
            $model = new UserModelHasPermissions();
            $model->user_id = $tUser->id;
            $model->model_type = $modelType;
            $model->model_id = $modelPermission;
            $model->tenant_id = $tUser->tenant->id;
            $model->save();
            array_push($results, $model);
        }

        return response()->json($results);
    }

}
