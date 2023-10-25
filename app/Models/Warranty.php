<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warranty extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','desc','ending_date','ending_mileage','mileage_total'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public static function findByName($tenantId, $name, $vehicleId = null, $sort = null, $sortDir = null, $itemsPerPage = 100, $fleetPermissionsFilter = null)
    {
        $query = Warranty::query()
            ->select('warranties.*', 'vehicles.vehicle_number as vehicle_number')
            ->distinct()
            ->join('vehicles', 'warranties.vehicle_id', '=', 'vehicles.id')
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id');
        $query->with(['vehicle']);
        $query->where('warranties.tenant_id', $tenantId);

        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(warranties.name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("warranties.id = ?", $name);
                }
            });
        }

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }
        if ($fleetPermissionsFilter) {
            $query->whereIn('fleets.id', $fleetPermissionsFilter);
        }

        if ($sort) {
            if ($sort == 'vehicle.vehicle_number') {
                $query->orderBy('vehicle_number', $sortDir);
            } else {
                $query->orderBy('warranties.' . $sort, $sortDir);
            }
        }

        return $query->paginate($itemsPerPage);
    }
}
