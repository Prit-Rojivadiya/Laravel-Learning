<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fleet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','fleet_number','branch_id','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public static function findByName($tenantId, $name, $branchId = null, $sort = null, $sortDir = null, $itemsPerPage = 100, $modelPermissionsFilter = null)
    {
        $query = Fleet::query()
            ->select('fleets.*', 'branches.name as branch_name')
            ->distinct()
            ->join('branches', 'fleets.branch_id', '=', 'branches.id');
        $query->with(['branch']);
        $query->where('fleets.tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(fleets.name) LIKE ?", '%' . $name . '%')
                    ->orWhereRaw("LOWER(fleet_number) LIKE ?", '%' . $name . '%')
                ;
                if ((int) $name !== 0) {
                    $q->orWhereRaw("fleets.id = ?", $name);
                }
            });
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($modelPermissionsFilter) {
            $query->whereIn('fleets.id', $modelPermissionsFilter);
        }

        if ($sort) {
            if ($sort == 'branch.name') {
                $query->orderBy('branch_name', $sortDir);
            } else {
                $query->orderBy('fleets.' . $sort, $sortDir);
            }
        }

        return $query->paginate($itemsPerPage);
    }
}
