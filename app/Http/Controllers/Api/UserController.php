<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const PERMISSION_ENTITY = 'user';

    // GET /users
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }

        $tenantId = $request->has('filterByTenant') ? $request->get('filterByTenant') : null;
        //Default to current tenant. Do not allow cross tenant user queries, except from TranzIT special tenant
        if (!Tenant::allowedAccessTranzITOnly($user)) {
            $tenantId = $user->tenant->id;
        }

        //server side pagination
        $paginateOptions = null;
        $paginate = $request->has('paginate') ? $request->get('paginate') : false;
        $sort = $request->has('_sort') ? $request->get('_sort') : null;
        $sortDir = $request->has('_sort_dir') ? $request->get('_sort_dir') : 'desc';
        $page = $request->has('_page') ? $request->get('_page') : 1;
        $itemsPerPage = $request->has('_items_per_page') ? $request->get('_items_per_page') : 100000;
        if ($paginate === 'false') {
            $paginate = false;
        }
        if ($paginate) {
            $paginateOptions['sort'] = $sort;
            $paginateOptions['sortDir'] = $sortDir;
            $paginateOptions['page'] = $page;
            $paginateOptions['itemsPerPage'] = $itemsPerPage;
        }

        $name = $request->has('filterByName') ? $request->get('filterByName') : null;
        $email = $request->has('filterByEmail') ? $request->get('filterByEmail') : null;
        return User::index($tenantId, $name, $email, $paginateOptions);
    }

    // GET /users/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }

        $model = User::findOrFail($id);

        if (!Tenant::allowedAccessTranzITOnly($user)) {
            if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
                abort(403, 'Access denied');
            }
        }
        $tUser = ['tenant' => $model->tenant->name, 'tenant_id' => $model->tenant->id, 'id' => $model->id, 'name' => $model->name, 'email' => $model->email];
        return response()->json($tUser);
    }

    // POST /users
    //TODO: Allow users to be created and password's set from a user invite link and/or registration page
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }

        $fields = [
            'name' => ['required','string', 'required'],
            'email' => ['required','string', 'email'],
        ];

        //Test email unique per tenant
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                function($attribute, $value, $fail) use($request) {
                    $duplicateEmailCount = User::whereRaw('LOWER(email) = ?', strtolower($request->get('email')))
                        ->where('tenant_id', $request->user()->tenant->id)
                        ->count();
                    if ($duplicateEmailCount > 0) {
                        $fail('The email "' . $value . '"" has already been taken. Please enter a different email');
                    }
                }
            ],
        ]);
        $validator->validate();

        $validated = $request->validate($fields);
        $model = new User($validated);
        $model->tenant_id = $user->tenant->id;
        $model->password = bcrypt(Str::random(20)); //Randomly set password here, will be changed later
        $model->save();

        if (!is_null($request->get('role'))) {
            $roles = [$request->get('role')]; //Assume one role assignment per user in this update() call.
            $model->assignNewRoles($roles);
        }

        return response()->json($model);
    }

    // PUT /users/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }

        $model = User::findOrFail($id);

        if (!Tenant::allowedAccessTranzITOnly($user)) {
            if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
                abort(403, 'Access denied');
            }
        }

        $fields = [];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = ['required','string'];
        }
        if ($model->email != $request->get('email') && !is_null($request->get('email'))) {
            $fields['email'] = ['unique:users', 'string','email'];
            $validator = Validator::make($request->all(), [
                'email' => [
                    'required',
                    function($attribute, $value, $fail) use($request) {
                        $duplicateEmailCount = User::whereRaw('LOWER(email) = ?', strtolower($request->get('email')))
                            ->where('tenant_id', $request->user()->tenant->id)
                            ->count();
                        if ($duplicateEmailCount > 0) {
                            $fail('The email "'.$value.'"" has already been taken. Please enter a different email');
                        }
                    }
                ],
            ]);
            $validator->validate();
        }

        $fieldsToUpdate = $request->validate($fields);

        if (isset($fieldsToUpdate['email'])) {
            $fieldsToUpdate['email'] = strtolower($fieldsToUpdate['email']);
        }

        if (sizeof($fieldsToUpdate) > 0) {
            $model->update($fieldsToUpdate);
        }

        if (!is_null($request->get('role'))) {
            $roles = [$request->get('role')]; //Assume one role assignment per user in this update() call.
            $model->assignNewRoles($roles);
        }

        return response()->json(['success' => false]);
    }


    // DELETE /users/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = User::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }

    // GET /users/role/{id}
    public function role(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, 'manage any')) {
            abort(401, 'Unauthorized');
        }
        $query = Role::query()
            ->select('roles.name as roleName')
            ->join('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->where('roles.name', '!=', 'super-admin')
            ->where('model_has_roles.model_id', $id);
        $roles = $query->get();
        return $roles;
    }



}
