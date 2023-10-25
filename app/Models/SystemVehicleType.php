<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemVehicleType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','desc'];

    public function vehicleTypes()
    {
        return $this->hasMany(VehicleType::class);
    }

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        // todo: limit by tenant ID
        $query = SystemVehicleType::query()
            ->select('system_vehicle_types.*');

        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("system_vehicle_types.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('system_vehicle_types.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
