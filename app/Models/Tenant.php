<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements \Stancl\Tenancy\Contracts\Tenant
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','abbrv'];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'abbrv',
            'created_at',
            'udpated_at',
            'deleted_at',
            'ro_number'
        ];
    }

    public static function allowedAccess($userTenant, $modelTenant)
    {
        if ($userTenant->id == $modelTenant->id || $modelTenant->id == 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function allowedAccessTranzITOnly($user)
    {
        if ($user->tenant->abbrv == "TranzIT"  || $user->tenant->id == 1 || $user->isSuperAdminUser()) {
            return true;
        } else {
            return false;
        }
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function incRONumber()
    {
        $newRONumber = $this->ro_number;
        $this->ro_number = $newRONumber+1;
        $this->save();
        return $newRONumber;
    }

    public static function index($tenantId, $name = null, $paginateOptions = null)
    {
        $query = Tenant::query()
            ->select('tenants.*');

        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%')
                    ->orWhereRaw("LOWER(abbrv) LIKE ?", '%' . $name . '%')
                ;
                if ((int) $name !== 0) {
                    $q->orWhereRaw("tenants.id = ?", $name);
                }
            });
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
                $query->orderBy('tenants.' . $sort, $sortDir);
            }
        }
        return $paginate ? $query->paginate($itemsPerPage) : $query->get();
    }

    public function populateTenantDefaults($defaultSourceTenantId = null)
    {
        $tenant = $this;
        if ($defaultSourceTenantId == null) {
            $defaultSourceTenantId = (Tenant::where('name', 'TranzIT')->firstOrFail())->id;
        }

        // Create TranzIT Tenant Admin User, unless one exists already
        $email = strtolower('admin+' . $tenant->abbrv . '@tranzitfleet.com');
        $pwd = Str::random(15);

        $uniqueEmail = User::where('email', $email)->first();
        if (!$uniqueEmail) {
            $user = \App\Models\User::create([
                'name' => 'Tenant Admin ' . $tenant->abbrv,
                'tenant_id' => $tenant->id,
                'email' => $email,
                'password' => bcrypt($pwd),
            ]);
            $user->assignRole('tenant-admin');
        }

        // Copy Vehicle Types
        $defaultData = VehicleType::where('tenant_id', $defaultSourceTenantId)->get();
        foreach ($defaultData as $source) {
            $name = $source->name;
            $uniqueEntry = VehicleType::where('name', $name)->where('tenant_id', $tenant->id)->first();
            if (!$uniqueEntry) {
                $vehicle_type_1 = \App\Models\VehicleType::create([
                    'name' => $source->name,
                    'tenant_id' => $tenant->id,
                    'desc' => $source->desc,
                    'system_vehicle_type_id' => $source->system_vehicle_type_id,
                ]);
            }
        }

        // Copy Repair Order Types
        $defaultData = RepairOrderType::where('tenant_id', $defaultSourceTenantId)->get();
        foreach ($defaultData as $source) {
            $name = $source->name;
            $uniqueEntry = RepairOrderType::where('name', $name)->where('tenant_id', $tenant->id)->first();
            if (!$uniqueEntry) {
                $repair_order_type = \App\Models\RepairOrderType::create([
                    'name' => $source->name,
                    'code' => $source->code,
                    'desc' => $source->desc,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        // Copy Repair Order Status
        $defaultData = RepairOrderStatus::where('tenant_id', $defaultSourceTenantId)->get();
        foreach ($defaultData as $source) {
            $name = $source->name;
            $uniqueEntry = RepairOrderStatus::where('name', $name)->where('tenant_id', $tenant->id)->first();
            if (!$uniqueEntry) {
                $repair_order_status_n = \App\Models\RepairOrderStatus::create([
                    'name' => $source->name,
                    'desc' => $source->desc,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        // Copy Line Item Types
        $defaultData = LineItemType::where('tenant_id', $defaultSourceTenantId)->get();
        foreach ($defaultData as $source) {
            $name = $source->name;
            $uniqueEntry = LineItemType::where('name', $name)->where('tenant_id', $tenant->id)->first();
            if (!$uniqueEntry) {
                $line_item_type1 = \App\Models\LineItemType::create([
                    'name' => $source->name,
                    'code' => $source->code,
                    'desc' => $source->desc,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        // Copy Line Item Categories
        $defaultData = LineItemCategory::where('tenant_id', $defaultSourceTenantId)->with(['lineItemType'])->get();
        foreach ($defaultData as $source) {
            $name = $source->name;
            $lineItemTypeName = $source->lineItemType->name;
            $uniqueEntry = LineItemCategory::where('name', $name)->where('tenant_id', $tenant->id)->first();
            if (!$uniqueEntry) {
                $lineItemTypeId = LineItemType::where('name', $lineItemTypeName)->where('tenant_id', $tenant->id)->firstOrFail()->id;
                $line_item_category1 = \App\Models\LineItemCategory::create([
                    'name' => $source->name,
                    'code' => $source->code,
                    'desc' => $source->desc,
                    'line_item_type_id' => $lineItemTypeId,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        // Copy Fuel Type
        $defaultData = FuelType::where('tenant_id', $defaultSourceTenantId)->get();
        foreach ($defaultData as $source) {
            $name = $source->name;
            $uniqueEntry = FuelType::where('name', $name)->where('tenant_id', $tenant->id)->first();
            if (!$uniqueEntry) {
                $fuel_type1 = \App\Models\FuelType::create([
                    'name' => $source->name,
                    'desc' => $source->desc,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        // Copy Fuel Unit Type
        $defaultData = FuelUnitType::where('tenant_id', $defaultSourceTenantId)->get();
        foreach ($defaultData as $source) {
            $name = $source->name;
            $uniqueEntry = FuelUnitType::where('name', $name)->where('tenant_id', $tenant->id)->first();
            if (!$uniqueEntry) {
                $fuel_unit_type1 = \App\Models\FuelUnitType::create([
                    'name' => $source->name,
                    'desc' => $source->desc,
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        // Optionally create first client, branch, and fleet if none exists yet
        $name = $tenant->name;
        $client = Client::where('tenant_id', $tenant->id)->first();
        if (!$client) {
            //There are no clients created in this tenant yet.  Create a default client
            $client = \App\Models\Client::create([
                'name' => $name,
                'abbrv' => $tenant->abbrv,
                'tenant_id' => $tenant->id,
            ]);
        }
        $name = $client->name . ' Branch 1';
        $branch = Branch::where('tenant_id', $tenant->id)->first();
        if (!$branch) {
            //There are no branches created in this tenant yet.  Create a default branch
            $branch = \App\Models\Branch::create([
                'name' => $name,
                'client_id' => $client->id,
                'tenant_id' => $tenant->id,
            ]);
        }
        $name = $branch->name . ' Fleet 1';
        $fleet = Fleet::where('tenant_id', $tenant->id)->first();
        if (!$fleet) {
            //There are no fleets created in this tenant yet.  Create a default fleet
            $fleet = \App\Models\Fleet::create([
                'name' => $name,
                'fleet_number' => 'F01',
                'branch_id' => $branch->id,
                'tenant_id' => $tenant->id,
            ]);
        }
    }

}
