<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'vehicle_number', 'fleet_id',
        'vehicle_type_id', 'engine_manufacturer_id',
        'year', 'make', 'model', 'vin', 'tire_size',
        'license_plate_number', 'license_state', 'engine_serial_number',
        'purchase_price', 'in_service_date'
    ];

    protected $casts = [
        'in_service_date' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function engineManufacturer()
    {
        return $this->belongsTo(EngineManufacturer::class);
    }

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    public function latestMeterReading()
    {
        return $this->hasMany(MeterReading::class)->latest('meter_reading')->first();
    }

    public function fuelings()
    {
        return $this->hasMany(Fueling::class);
    }

    public function preventiveMaintenances()
    {
        return $this->hasMany(PreventiveMaintenance::class);
    }

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public static function findByName($tenantId, $number, $clientId = null, $branchId = null, $fleetId = null, $sort = null, $sortDir = null, $itemsPerPage = 100, $fleetPermissionsFilter = null)
    {
        $query = Vehicle::query()
            ->select('vehicles.*',
                'fleets.name as fleet_name','branches.name as branch_name','clients.name as client_name',
                'fleets.id as fleet_id','branches.id as branch_id','clients.id as client_id',
                'vehicle_types.name as vehicle_type_name', 'engine_manufacturers.name as engine_manufacturer_name'
            )
            ->distinct()
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
            ->join('branches', 'fleets.branch_id', '=', 'branches.id')
            ->join('clients', 'branches.client_id', '=', 'clients.id')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id', 'left outer')
            ->join('engine_manufacturers', 'vehicles.engine_manufacturer_id', '=', 'engine_manufacturers.id', 'left outer');
        $query->with(['fleet']);
        $query->where('vehicles.tenant_id', $tenantId);

        if ($number) {
            $query->where(function ($q) use ($number) {
                $q->whereRaw("LOWER(vehicles.vehicle_number) LIKE ?", '%' . $number . '%');
                if ((int)$number !== 0) {
                    $q->orWhereRaw("vehicles.id = ?", $number);
                }
            });
        }

        if ($clientId) {
            $query->where('clients.id', $clientId);
        }
        if ($branchId) {
            $query->where('branches.id', $branchId);
        }
        if ($fleetId) {
            $query->where('fleets.id', $fleetId);
        }
        if ($fleetPermissionsFilter) {
            $query->whereIn('fleets.id', $fleetPermissionsFilter);
        }

        if ($sort) {
            if ($sort == 'fleet.name') {
                $query->orderBy('fleet_name', $sortDir);
            } else {
                $query->orderBy('vehicles.' . $sort, $sortDir);
            }
        }

        return $query->paginate($itemsPerPage);
    }

    public static function findByVin($tenantId, $vin)
    {
        $vin = strtoupper(trim($vin));
        $query = Vehicle::query()
            ->select('vehicles.*');
        $query->where('vehicles.tenant_id', $tenantId);
        $query->where('vehicles.vin', $vin);
        return $query->first();
    }

}
