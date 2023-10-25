<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Fueling extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['vehicle_id','vendor_id','fuel_unit_type_id',
        'price_per_unit','total_units','total_price','meter_reading','meter_reading_id',
        'fueling_date','fuel_type_id','location_country','location_state','notes'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function fuelUnitType()
    {
        return $this->belongsTo(FuelUnitType::class);
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class);
    }

    public function updateMeterReading()
    {
        $meterReading = null;
        if (isset($this->meter_reading_id)) {
            $meterReading = MeterReading::findOrFail($this->meter_reading_id);
        }
        else {
            $meterReading = new MeterReading();
        }
        $meterReading->tenant_id = $this->tenant_id;
        $meterReading->vehicle_id = $this->vehicle_id;
        $meterReading->meter_reading = $this->meter_reading;
        $meterReading->meter_reading_date = $this->fueling_date;
        $meterReading->source = 'Fueling';
        $meterReading->source_id = $this->id;
        $meterReading->save();
        return $meterReading;
    }

    public static function findByVehicle($tenantId, $vehicleId = null, $sort = null, $sortDir = null, $itemsPerPage = 100, $fleetPermissionsFilter = null)
    {
        $query = Fueling::query()
            ->select('fuelings.*', 'vehicles.vehicle_number as vehicle_number', 'vendors.name as vendor_name', 'fuel_unit_types.name as fuel_unit_type_name', 'fuel_types.name as fuel_type_name')
            ->distinct()
            ->join('vehicles', 'fuelings.vehicle_id', '=', 'vehicles.id')
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
            ->join('fuel_unit_types', 'fuelings.fuel_unit_type_id', '=', 'fuel_unit_types.id')
            ->leftJoin('vendors', 'fuelings.vendor_id', '=', 'vendors.id')
            ->leftJoin('fuel_types', 'fuelings.fuel_type_id', '=', 'fuel_types.id');
        $query->with(['vehicle','vendor','fuelType','fuelUnitType']);
        $query->where('fuelings.tenant_id', $tenantId);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }
        if ($fleetPermissionsFilter) {
            $query->whereIn('fleets.id', $fleetPermissionsFilter);
        }

        if ($sort) {
            if ($sort == 'vehicle.vehicle_number') {
                $query->orderBy('vehicle_number', $sortDir);
            }
            else if ($sort == 'vendor.name') {
                $query->orderBy('vendor_name', $sortDir);
            }
            else if ($sort == 'fuel_unit_type.name') {
                $query->orderBy('fuel_unit_type_name', $sortDir);
            }
            else if ($sort == 'fuel_type.name') {
                $query->orderBy('fuel_type_name', $sortDir);
            }
            else {
                $query->orderBy('fuelings.' . $sort, $sortDir);
            }
        }

        return $query->paginate($itemsPerPage);
    }

    public static function byDateRange($tenantId, $startDate, $endDate, $clientId = null, $branchId = null, $fleetId = null) {
        if (!isset($tenantId)) {
            throw new Exception("Missing tenant in Fueling.byDateRange()");
        }
        $query = Fueling::query()
            ->select('fuelings.*','vehicles.vehicle_number as vehicle_number', 'vehicles.vin as vehicle_vin', 'system_vehicle_types.name as system_vehicle_type_name',
                'fleets.name as fleet_name','branches.name as branch_name','clients.name as client_name',
                'fleets.id as fleet_id','branches.id as branch_id','clients.id as client_id')
            ->distinct()
            ->join('vehicles', 'fuelings.vehicle_id', '=', 'vehicles.id')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->join('system_vehicle_types', 'vehicle_types.system_vehicle_type_id', '=', 'system_vehicle_types.id')
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
            ->join('branches', 'fleets.branch_id', '=', 'branches.id')
            ->join('clients', 'branches.client_id', '=', 'clients.id');
        $query->with('vehicle');
        $query->where('fuelings.tenant_id', $tenantId);
        $query->whereBetween('fuelings.fueling_date', [$startDate, $endDate]);
        if ($clientId) {
            $query->where('clients.id', $clientId);
        }
        if ($branchId) {
            $query->where('branches.id', $branchId);
        }
        if ($fleetId) {
            $query->where('fleets.id', $fleetId);
        }
        $fuelings = $query->get();
        return $fuelings;
    }



}
