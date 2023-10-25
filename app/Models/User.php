<?php

namespace App\Models;

use App\Http\Controllers\Api\PositionsController;
use App\SysType;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;

    protected $guarded = [];

    private $cachedPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'tenant_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function prepareForApiResponse()
    {
        $this->permissions = $this->buildAllPermissions();
        $this->userRoles = $this->getRoleNames();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hasCachedPermissionTo($permission)
    {


        if ($this->isSuperAdminUser()) {
            return true;
        }

        $userRoles = $this->getRoleNames();



        $this->buildAllPermissions();

        return in_array($permission, $this->cachedPermissions);
    }

    public function buildAllPermissions()
    {


        if ($this->cachedPermissions === null) {
            $this->cachedPermissions = [];

            $roles = $this->roles()->with('permissions')->get();



            foreach ($roles as $role) {


                foreach ($role->permissions as $permission) {
                    $this->cachedPermissions[] = $permission->name;
                }
            }
        }



        return $this->cachedPermissions;
    }

    public function hasPermission($entity, $action, $id = null)
    {

        //         $user = $this;

        // // Check if the user has the super-admin role
        // if ($user->hasRole('super-admin')) {
        //     // The user has the super-admin role

        // }else{

        // }

        $permission = false;
        if (!$entity || !$action) {
            return false;
        }

        if ($action === 'index') {
            $permissionName = "view any $entity";
        } elseif ($action === 'show') {
            $permissionName = "view $entity";
        } elseif ($action === 'store') {
            $permissionName = "manage $entity";
        } elseif ($action === 'update') {
            $permissionName = "manage any $entity";
        } elseif ($action === 'delete') {
            $permissionName = "manage any $entity";
        } else {
            $permissionName = "$action $entity";
        }




        if ($this->hasCachedPermissionTo($permissionName)) {

            $permission = true;
        } else {

            $permission = false;
        }

        // Check permission on the specific instance if it was passed in
        $modelPermissionsFilter = $this->getModelPermissionsFilter($entity);
        if ($permission && $id && $modelPermissionsFilter) {

            if (in_array($id, $modelPermissionsFilter)) {

                $permission = true;
            } else {

                $permission = false;
            }
        }


        return $permission;
    }

    public function getModelPermissionsFilter($entity)
    {
        $modelPermissionsFilter = [-1];  //default to none
        $userRoles = $this->getRoleNames();

        if ($userRoles->contains('super-admin') || $userRoles->contains('tenant-admin')) {
            $modelPermissionsFilter = null;  //allow all, don't filter
            return $modelPermissionsFilter;
        }

        //Get allowed clients
        $query = UserModelHasPermissions::query()
            ->select('model_id')
            ->where('user_id', $this->id)
            ->where('tenant_id', $this->tenant->id)
            ->where('model_type', 'App\Models\Client');
        $allowedClientIds = $query->get();

        //Get allowed branches
        $query = UserModelHasPermissions::query()
            ->select('model_id')
            ->where('user_id', $this->id)
            ->where('tenant_id', $this->tenant->id)
            ->where('model_type', 'App\Models\Branch');
        $allowedBranchIds = $query->get();

        //Get allowed fleets
        $query = UserModelHasPermissions::query()
            ->select('model_id')
            ->where('user_id', $this->id)
            ->where('tenant_id', $this->tenant->id)
            ->where('model_type', 'App\Models\Fleet');
        $allowedFleetIds = $query->get();

        $results = null;
        if ($entity === 'client') {
            if ($userRoles->contains('client-manager')) {
                // All the allowed clients
                $results = $allowedClientIds;
            } elseif ($userRoles->contains('branch-manager')) {
                // All the clients for the allowed branches
                $query = Client::query()
                    ->select('clients.id as model_id')
                    ->distinct()
                    ->join('branches', 'branches.client_id', '=', 'clients.id')
                    ->whereIn('branches.id', $allowedBranchIds);
                $results = $query->get();
            } elseif ($userRoles->contains('fleet-manager')) {
                // All the clients for the allowed fleets
                $query = Client::query()
                    ->select('clients.id as model_id')
                    ->distinct()
                    ->join('branches', 'branches.client_id', '=', 'clients.id')
                    ->join('fleets', 'fleets.branch_id', '=', 'branches.id')
                    ->whereIn('fleets.id', $allowedFleetIds);
                $results = $query->get();
            }
        } elseif ($entity === 'branch') {
            if ($userRoles->contains('client-manager')) {
                // All the branches for the allowed clients
                $query = Branch::query()
                    ->select('branches.id as model_id')
                    ->distinct()
                    ->join('clients', 'clients.id', '=', 'branches.client_id')
                    ->whereIn('clients.id', $allowedClientIds);
                $results = $query->get();
            } elseif ($userRoles->contains('branch-manager')) {
                // All the allowed branches
                $results = $allowedBranchIds;
            } elseif ($userRoles->contains('fleet-manager')) {
                // All the branches for the allowed fleets
                $query = Branch::query()
                    ->select('branches.id as model_id')
                    ->distinct()
                    ->join('fleets', 'fleets.branch_id', '=', 'branches.id')
                    ->whereIn('fleets.id', $allowedFleetIds);
                $results = $query->get();
            }
        } elseif ($entity === 'fleet') {
            if ($userRoles->contains('client-manager')) {
                // All the fleets for the allowed clients
                $query = Fleet::query()
                    ->select('fleets.id as model_id')
                    ->distinct()
                    ->join('branches', 'branches.id', '=', 'fleets.branch_id')
                    ->join('clients', 'clients.id', '=', 'branches.client_id')
                    ->whereIn('clients.id', $allowedClientIds);
                $results = $query->get();
            } elseif ($userRoles->contains('branch-manager')) {
                // All the fleets for the allowed branches
                $query = Fleet::query()
                    ->select('fleets.id as model_id')
                    ->distinct()
                    ->join('branches', 'branches.id', '=', 'fleets.branch_id')
                    ->whereIn('branches.id', $allowedBranchIds);
                $results = $query->get();
            } elseif ($userRoles->contains('fleet-manager')) {
                // All the allowed fleets
                $results = $allowedFleetIds;
            }
        } else {
            $results = null;
        }

        if ($results) {
            $resultsAsArray = $results->toArray();
            foreach ($resultsAsArray as $result) {
                array_push($modelPermissionsFilter, $result['model_id']);
            }
        } else {
            $modelPermissionsFilter = [-1];
        }

        return $modelPermissionsFilter;  //null means allow all, don't filter, otherwise it's an array of allowed ids.
    }

    public function assignNewRoles($roles)
    {
        // TODO: handle multiple role assignment.
        // For now, business rules are each user is assigned one role (hierarchy structure of permissions)
        // Exception are super-admin's that are only set via database seeds.
        $newRole = $roles[0];
        $userRoles = $this->getRoleNames();
        if (!$userRoles->contains($newRole)) {
            // assign the new role
            if ($newRole != 'none') {
                $this->assignRole($newRole);
            }
            // remove other roles
            foreach ($userRoles as $uRole) {
                if ($uRole != 'super-admin' && $uRole != $newRole) {
                    $this->removeRole($uRole);
                }
            }
        }
    }

    public function isSuperAdminUser()
    {

        $userRoles = $this->getRoleNames();

        if ($userRoles->contains('super-admin')) {
            return true;
        } else {
            return false;
        }
    }

    public static function index($tenantId, $name = null, $email = null, $paginateOptions = null)
    {
        $query = User::query()
            ->select('users.id', 'users.name', 'users.email', 'tenants.name as tenantName')
            ->join('tenants', 'users.tenant_id', '=', 'tenants.id');

        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(users.name) LIKE ?", '%' . $name . '%');
            });
        }
        if ($email) {
            $query->where(function ($q) use ($email) {
                $q->whereRaw("LOWER(users.email) LIKE ?", '%' . $email . '%');
            });
        }
        if ($tenantId) {
            $query->where('users.tenant_id', $tenantId);
        }

        $paginate = false;
        $sort = null;
        $sortDir = 'desc';
        $page = 1;
        $itemsPerPage = 100;

        if ($paginateOptions) {
            $paginate = true;
            if (isset($paginateOptions['sort'])) {
                $sort = $paginateOptions['sort'];
            }
            if (isset($paginateOptions['sortDir'])) {
                $sortDir = $paginateOptions['sortDir'];
            }
            if (isset($paginateOptions['page'])) {
                $page = $paginateOptions['page'];
            }
            if (isset($paginateOptions['itemsPerPage'])) {
                $itemsPerPage = $paginateOptions['itemsPerPage'];
            }
            if ($sort) {
                $query->orderBy('users.' . $sort, $sortDir);
            }
        }
        return $paginate ? $query->paginate($itemsPerPage) : $query->get();
    }
}
