<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Fleet;
use App\Models\FuelType;
use App\Models\FuelUnitType;
use App\Models\MeterReading;
use App\Models\State;
use App\Models\Tenant;
use App\Models\Vendor;
use App\Models\Vehicle;
use App\Models\Fueling;
use DateTime;
use Exception;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class FuelingController extends Controller
{
    private const PERMISSION_ENTITY = 'fueling';
    private const PERMISSION_FLEET = 'fleet';

    // GET /fuelings
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fleetPermissionsFilter = $user->getModelPermissionsFilter(self::PERMISSION_FLEET);
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return Fueling::findByVehicle($user->tenant->id, $request->query('filterByVehicle'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage, $fleetPermissionsFilter);
    }

    // GET /fuelings/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $model = Fueling::with('vehicle','vendor','fuelType','fuelUnitType')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }


    // POST /fuelings
    public function store(Request $request)
    {
        $user = $request->user();
        $fields = [
            'vehicle_id' => ['required','integer'],
            'vendor_id' => ['integer'],
            'fuel_unit_type_id' => ['required','integer'],
            'price_per_unit' => ['required','numeric'],
            'total_price' => ['required','numeric'],
            'total_units' => ['required','numeric'],
            'meter_reading' => ['required','integer'],
            'meter_reading_id' => ['integer','nullable'],
            'fueling_date' => ['required','date'],
            'fuel_type_id' => ['integer'],
            'location_country' => ['string'],
            'location_state' => ['string'],
            'notes' => ['string'],
        ];
        $validated = $request->validate($fields);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
            abort(403, 'Access denied');
        }

        $vendor = null;
        if (array_key_exists('vendor_id', $validated)) {
            $vendor = Vendor::findOrFail($validated['vendor_id']);
            if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
                abort(403, 'Access denied');
            }
        }
        $fuelType = null;
        if (array_key_exists('fuel_type_id', $validated)) {
            $fuelType = FuelType::findOrFail($validated['fuel_type_id']);
            if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
                abort(403, 'Access denied');
            }
        }
        $fuelUnitType = FuelUnitType::findOrFail($validated['fuel_unit_type_id']);
        if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
            abort(403, 'Access denied');
        }

        $model = new Fueling($validated);
        $model->tenant_id = $user->tenant->id;
        $model->vehicle_id = $vehicle->id;
        $model->fuel_unit_type_id = $fuelUnitType->id;
        if ($vendor != null) {
            $model->vendor_id = $vendor->id;
        }
        if ($fuelType != null) {
            $model->fuel_type_id = $fuelType->id;
        }

        $model->save();
        //Now create or update the related meter event
        $meterReading = $model->updateMeterReading();
        $model->meter_reading_id = $meterReading->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /fuelings/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $model = Fueling::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        $fields = [
            'vehicle_id' => ['integer'],
            'vendor_id' => ['integer','nullable'],
            'fuel_unit_type_id' => ['integer'],
            'price_per_unit' => ['numeric'],
            'total_price' => ['numeric'],
            'total_units' => ['numeric'],
            'meter_reading' => ['integer'],
            'meter_reading_id' => ['integer'],
            'fueling_date' => ['date'],
            'fuel_type_id' => ['integer','nullable'],
            'location_country' => ['string','nullable'],
            'location_state' => ['string','nullable'],
            'notes' => ['string','nullable'],
        ];
        if ($model->vehicle_id != $request->get('vehicle_id') && !is_null($request->get('vehicle_id'))) {
            $vehicle = Vehicle::findOrFail($request->get('vehicle_id'));
            if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
                abort(403, 'Access denied');
            }
            $fields['vehicle_id'] = ['required','integer'];
        }
        if ($model->vendor_id != $request->get('vendor_id') && !is_null($request->get('vendor_id'))) {
            $vendor = Vendor::findOrFail($request->get('vendor_id'));
            if (!Tenant::allowedAccess($user->tenant, $vendor->tenant)) {
                abort(403, 'Access denied');
            }
            $fields['vendor_id'] = ['required','integer'];
        }
        if ($model->fuel_unit_type_id != $request->get('fuel_unit_type_id') && !is_null($request->get('fuel_unit_type_id'))) {
            $fuelUnitType = FuelUnitType::findOrFail($request->get('fuel_unit_type_id'));
            $fields['fuel_unit_type_id'] = ['required','integer'];
        }
        if ($model->meter_reading_id != $request->get('meter_reading_id') && !is_null($request->get('meter_reading_id'))) {
            $meterReading = MeterReading::findOrFail($request->get('meter_reading_id'));
            $fields['meter_reading_id'] = ['required','integer'];
        }
        if ($model->fuel_type_id != $request->get('fuel_type_id') && !is_null($request->get('fuel_type_id'))) {
            $meterReading = FuelType::findOrFail($request->get('fuel_type_id'));
            $fields['fuel_type_id'] = ['required','integer'];
        }
        if ($model->price_per_unit != $request->get('price_per_unit')) {
            $fields['price_per_unit'] = ['required','numeric'];
        }
        if ($model->total_price != $request->get('total_price')) {
            $fields['total_price'] = ['required','numeric'];
        }
        if ($model->total_units != $request->get('total_units')) {
            $fields['total_units'] = ['required','numeric'];
        }
        if ($model->meter_reading != $request->get('meter_reading')) {
            $fields['meter_reading'] = ['required','numeric'];
        }
        if ($model->numeric != $request->get('numeric')) {
            $fields['numeric'] = ['required','e'];
        }
        if ($model->location_country != $request->get('location_country')) {
            $fields['location_country'] = ['required','string'];
        }
        if ($model->location_state != $request->get('location_state')) {
            $fields['location_state'] = ['required','string'];
        }


        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);

        //Now create or update the related meter event
        $meterReading = $model->updateMeterReading();
        $model->meter_reading_id = $meterReading->id;
        $model->save();

        return response()->json($model);
    }

    // DELETE /fuelings/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = Fueling::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }

    // POST /fuelings/import
    public function import(Request $request) {
        $results = [];
        $results['total'] = 0;
        $results['imported'] = 0;
        $results['failed'] = [];
        $user = $request->user();
        $tenant = $user->tenant;
        $fields = [
            'fuelings' => ['required', 'array'],
            'filterByClient' => ['required', 'integer'],
        ];
        $validated = $request->validate($fields);
        $client = Client::findOrFail($validated['filterByClient']);
        if (!Tenant::allowedAccess($user->tenant, $client->tenant)) {
            abort(403, 'Access denied');
        }

        $results['total'] = count($validated['fuelings']);
        foreach ($validated['fuelings'] as $key => $fuelEvent) {
            try {
                $fields = [
                    'Vehicle Number' => ['required','string'],
                    'Fueling Date' => ['required','date'],
                    'Meter' => ['required','integer'],
                    'Gallons' => ['required','numeric'],
                    'Price Per Gallon' => ['required','numeric'],
                    'Fuel Type' => ['string'],
                    'Vendor' => ['string'],
                    'State' => ['string'],
                    'Country' => ['string'],
                ];
                if (!$fuelEvent['Vehicle Number']) {
                    throw new Exception("Missing required value for vehicle number ".$fuelEvent['Vehicle Number']);
                }
                $fuelingDate = null;
                if (!$fuelEvent['Fueling Date']) {
                    throw new Exception("Missing required value for vehicle number ".$fuelEvent['Vehicle Number']);
                }
                else {
                    $date = $fuelEvent['Fueling Date'];
                    $format = 'm/d/Y';
                    $d = DateTime::createFromFormat($format, $fuelEvent['Fueling Date']);
                    if ($d && $d->format($format) === $date) {
                        $fuelingDate = $d->format('Y-m-d');
                    }
                    else {
                        throw new Exception("Invalid fueling date encountered ". $fuelEvent['Fueling Date']. ", must be in format mm/dd/yyyy");
                    }
                }
                if (!$fuelEvent['Meter'] || !is_numeric($fuelEvent['Meter'])) {
                    throw new Exception("Missing required numeric value for meter ".$fuelEvent['Meter']);
                }
                if (!$fuelEvent['Gallons'] || !is_numeric($fuelEvent['Gallons'])) {
                    throw new Exception("Missing required numeric value for Gallons ".$fuelEvent['Gallons']);
                }
                if (!$fuelEvent['Price Per Gallon'] || !is_numeric($fuelEvent['Price Per Gallon'])) {
                    throw new Exception("Missing required numeric value for Price Per Gallon ".$fuelEvent['Price Per Gallon']);
                }

                $vehicle = Vehicle::query()
                    ->select('vehicles.*')
                    ->where('vehicles.vehicle_number', $fuelEvent['Vehicle Number'])
                    ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
                    ->join('branches', 'fleets.branch_id', '=', 'branches.id')
                    ->join('clients', 'branches.client_id', '=', 'clients.id')
                    ->where('clients.id', $client->id)
                    ->first();
                if (empty($vehicle)) {
                    throw new Exception("Could not find matching vehicle for vehicle number ".$fuelEvent['Vehicle Number']);
                }

                $vendor = null;
                if ($fuelEvent['Vendor']) {
                    $vendor = Vendor::query()->where('tenant_id', $tenant->getKey())->whereRaw('LOWER(name) = ?', [strtolower($fuelEvent['Vendor'])])->first();
                }
                $fuelType = null;
                if($fuelEvent['Fuel Type']) {
                    $fuelType = FuelType::query()->where('tenant_id', $tenant->getKey())->whereRaw('LOWER(name) = ?', [strtolower($fuelEvent['Fuel Type'])])->first();
                }
                $fuelUnitType = FuelUnitType::query()->where('tenant_id', $tenant->getKey())->whereRaw('LOWER(name) = ?', [strtolower('Gallons')])->first();
                $locationState = null;
                if($fuelEvent['State']) {
                    if (State::STATES[$fuelEvent['State']]) {
                        $locationState = State::STATES[$fuelEvent['State']];
                    }
                    else {
                        throw new Exception("Could not find matching state abbreviation for ".$fuelEvent['State']);
                    }
                }
                $locationCountry = null;
                if($fuelEvent['Country']) {
                    if (Country::COUNTRIES[$fuelEvent['Country']]) {
                        $locationCountry = Country::COUNTRIES[$fuelEvent['Country']];
                    }
                    else {
                        throw new Exception("Could not find matching country abbreviation for ".$fuelEvent['Country']);
                    }
                }

                $fueling = Fueling::query()
                    ->where('tenant_id', $tenant->getKey())
                    ->where('vehicle_id', $vehicle->getKey())
                    ->where('total_units', $fuelEvent['Gallons'])
                    ->where('fueling_date', $fuelingDate)
                    ->first();
                if (empty($fueling)) {
                    $fueling = new Fueling();
                }
                $fueling->fill([
                    'fueling_date' => $fuelingDate,
                    'meter_reading' => $fuelEvent['Meter'],
                    'price_per_unit' => $fuelEvent['Price Per Gallon'],
                    'total_units' => $fuelEvent['Gallons'],
                ]);

                $fueling->tenant_id = $tenant->id;
                $fueling->vehicle_id = $vehicle->id;
                $fueling->fuel_unit_type_id = $fuelUnitType->id;
                $fueling->total_price = $fueling->price_per_unit * $fueling->total_units;

                if ($vendor) $fueling->vendor_id = $vendor->id;
                if ($fuelType) $fueling->fuel_type_id = $fuelType->id;
                if ($locationState) $fueling->location_state = $locationState;
                if ($locationCountry) $fueling->location_country = $locationCountry;

                $fueling->save();

                //Now create or update the related meter event
                $meterReading = $fueling->updateMeterReading();
                $fueling->meter_reading_id = $meterReading->id;
                $fueling->save();

                $results['imported'] = $results['imported'] + 1;

            }
            catch(Exception $e) {
                $row = $key+1 ;
                $results['failed'][$row] = 'Error: item '.$row.' failed to import: ' .$e->getMessage();
            }
        }
        return response()->json($results);
    }

    // POST /fuelings/v1/import
    // API to receive fuel events from external api calls, such as intelgic for automated data entry
    public function importv1(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, 'store')) {
            abort(401, 'Unauthorized');
        }

        $results = [
            "success" => true
        ];

        Log::info("Fueling event importv1() request body: " . json_encode($request->all(), JSON_PRETTY_PRINT));
        return response()->json($results);
    }

}
