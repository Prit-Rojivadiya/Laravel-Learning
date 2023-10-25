<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','system_vehicle_type_id','desc','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function systemVehicleType()
    {
        return $this->belongsTo(SystemVehicleType::class);
    }


    public static function findByName($tenantId, $name, $systemVehicleTypeId, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = VehicleType::query()
            ->select('vehicle_types.*', 'system_vehicle_types.name as system_vehicle_type_name')
            ->distinct()
            ->join('system_vehicle_types', 'vehicle_types.system_vehicle_type_id', '=', 'system_vehicle_types.id');
        $query->with(['systemVehicleType']);
        $query->where('tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(vehicle_types.name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("vehicle_types.id = ?", $name);
                }
            });
        }
        if ($systemVehicleTypeId) {
            $query->where('system_vehicle_type_id', $systemVehicleTypeId);
        }

        if ($sort) {
            if ($sort == 'system_vehicle_type_name') {
                $query->orderBy('system_vehicle_types.name', $sortDir);
            } else {
                $query->orderBy('vehicle_types.' . $sort, $sortDir);
            }
        }


        return $query->paginate($itemsPerPage);
    }
}
