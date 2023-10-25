<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReading extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['meter_reading','meter_reading_date','notes','source_id','source',
        'external_source','external_id','location_country','location_state'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public static function findByName($tenantId, $source, $vehicleId = null, $sort = null, $sortDir = null, $itemsPerPage = 100, $fleetPermissionsFilter = null)
    {
        $query = MeterReading::query()
            ->select('meter_readings.*', 'vehicles.vehicle_number as vehicle_number')
            ->distinct()
            ->join('vehicles', 'meter_readings.vehicle_id', '=', 'vehicles.id')
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id');
        $query->with(['vehicle']);
        $query->where('meter_readings.tenant_id', $tenantId);

        if ($source) {
            $query->where(function ($q) use ($source) {
                $q->whereRaw("LOWER(meter_readings.source) LIKE ?", '%' . $source . '%');
                if ((int) $source !== 0) {
                    $q->orWhereRaw("meter_readings.id = ?", $source);
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
                $query->orderBy('meter_readings.' . $sort, $sortDir);
            }
        }

        return $query->paginate($itemsPerPage);
    }

    public static function byDateRange($tenantId, $startDate, $endDate, $clientId = null, $branchId = null, $fleetId = null, $distinctVehicles = false, $sources = null) {
        if (!isset($tenantId)) {
            throw new Exception("Missing tenant in MeterReading.byDateRange()");
        }
        $query = MeterReading::query();
        if ($distinctVehicles) {
            $query
                ->select('vehicles.vehicle_number as vehicle_number', 'vehicles.id as vehicle_id')
                ->distinct('vehicle_id');
        }
        else {
            $query
                ->select('meter_readings.*','vehicles.vehicle_number as vehicle_number', 'vehicles.id as vehicle_id', 'vehicles.vin as vehicle_vin', 'system_vehicle_types.name as system_vehicle_type_name',
                    'fleets.name as fleet_name','branches.name as branch_name','clients.name as client_name',
                    'fleets.id as fleet_id','branches.id as branch_id','clients.id as client_id')
                ->distinct();
        }
        $query
            ->join('vehicles', 'meter_readings.vehicle_id', '=', 'vehicles.id')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->join('system_vehicle_types', 'vehicle_types.system_vehicle_type_id', '=', 'system_vehicle_types.id')
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
            ->join('branches', 'fleets.branch_id', '=', 'branches.id')
            ->join('clients', 'branches.client_id', '=', 'clients.id');
        $query->with('vehicle');
        $query->where('meter_readings.tenant_id', $tenantId);
        $query->whereBetween('meter_readings.meter_reading_date', [$startDate, $endDate]);
        if ($clientId) {
            $query->where('clients.id', $clientId);
        }
        if ($branchId) {
            $query->where('branches.id', $branchId);
        }
        if ($fleetId) {
            $query->where('fleets.id', $fleetId);
        }
        if ($sources) {
            $query->whereIn('meter_readings.source', $sources);
        }
        $meterReadings = $query->get();
        return $meterReadings;
    }

    public static function latestByVehicle($vehicleId) {
        $query = MeterReading::query()
            ->select('meter_readings.*');
        $query->where('meter_readings.vehicle_id', $vehicleId);
        $query->orderBy('meter_reading', 'desc');
        $query->take(1);
        return $query->get();
    }

    public static function byVehicleLessThanMeter($vehicleId, $meter) {
        $query = MeterReading::query()
            ->select('meter_readings.*')
            ->where('meter_readings.vehicle_id', $vehicleId)
            ->where('meter_reading', '<', $meter)
            ->orderBy('meter_reading', 'desc');
        return $query->first();
    }

    public static function byVehicleLessThanDate($vehicleId, $date, $limit = 1) {
        $query = MeterReading::query()
            ->select('meter_readings.*')
            ->where('meter_readings.vehicle_id', $vehicleId)
            ->where('meter_reading_date', "<=", $date)
            ->orderBy('meter_reading_date', 'desc')
            ->orderBy('id', 'desc')
            ->take($limit);
        return $query->get();
    }

    public function isValidMeterReading() {
        //TODO:  this (meter reading) is valid if:
        // It is in sequential order with the reading immediately before and after it
    }

    public static function findByIntegrationVehicleDay($tenantId, $integrationId, $vehicleId, $datetime = null, $limit = 1)
    {
        $fromDate = $datetime ? new DateTime($datetime) : new DateTime();
        $toDate = $datetime ? new DateTime($datetime) : new DateTime();
        $fromDate->setTime(0, 0, 0); //beginning of day
        $toDate->setTime(23, 59, 59); //end of day

        $query = MeterReading::query()
            ->select('meter_readings.*')
            ->where('meter_readings.tenant_id', $tenantId)
            ->where('meter_readings.source_id', $integrationId)
            ->where('meter_readings.vehicle_id', $vehicleId)
            ->where('meter_reading_date', ">=", $fromDate)
            ->where('meter_reading_date', "<=", $toDate)
            ->orderBy('meter_reading_date', 'desc')
            ->take($limit);
        return $query->get();
    }

    public static function findByExternalId($tenantId, $integrationId, $externalId, $externalSource = null, $source = null, $vehicleId = null, $limit = 1)
    {
        $query = MeterReading::query()
            ->select('meter_readings.*')
            ->where('meter_readings.tenant_id', $tenantId)
            ->where('meter_readings.source_id', $integrationId)
            ->where('meter_readings.external_id', $externalId)
            ->orderBy('meter_reading', 'asc')
            ->take($limit);
        if ($externalSource) {
            $query->where('meter_readings.external_source', $externalSource);
        }
        if ($source) {
            $query->where('meter_readings.source', $source);
        }
        if ($vehicleId) {
            $query->where('meter_readings.vehicle_id', $vehicleId);
        }
        return $query->get();
    }

}
