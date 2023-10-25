<?php

namespace App\Imports;

use App\Models\EngineManufacturer;
use App\Models\Fleet;
use App\Models\State;
use App\Models\Tenant;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Carbon\Carbon;
use DateTime;
use Exception;
use Ramsey\Uuid\Uuid;

class VehicleImport
{
    private $filepath;

    //TODO implement with https://www.php.net/manual/en/function.geoip-region-name-by-code.php
    private $states = State::STATES;

    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    public function import()
    {
        $importUuid = Uuid::uuid4();
        $fileIsValid = true;

        $handle = fopen($this->filepath, 'r');

        // get tenant header row
        //$headers = fgetcsv($handle, 0, ",");
        if (($rowData = fgetcsv($handle, 0, ",")) !== FALSE) {
            $tenantName = $rowData[0];
        }
        try {
            #$tenant = Tenant::query()->whereRaw('LOWER(name) = ?', ['ntc'])->firstOrFail();
            #$tenant = Tenant::query()->whereRaw('LOWER(name) = ?', [strtolower($tenantName)])->firstOrFail();
            $tenant = Tenant::query()->whereRaw('abbrv = ?', [$tenantName])->firstOrFail();
            #$tenant = Tenant::where('name', "Helaman Holding Company")->firstOrFail();
            echo "Importing vehicles for tenant: ".$tenantName."\n";
        }
        catch (Exception $e) {
            $fileIsValid = false;
            echo "Failed to find tenant for '" . $tenantName . "'"."\n";
        }

        //check column headers
        //$headers = fgetcsv($handle, 0, ",");
        if (  $fileIsValid && (($rowData = fgetcsv($handle, 0, ",")) !== FALSE) ) {
            $clientName = $rowData[0];
            $branchName = $rowData[1];
            $fleetName = $rowData[2];
            $vehicleNumber = $rowData[3];
            $year = $rowData[4];
            $make = $rowData[5];
            $model = $rowData[6];
            $vin = $rowData[7];
            $license = $rowData[8];
            $licenseState = $rowData[9];
            $purchasePrice = $rowData[10];
            $inServiceDate = $rowData[11];
            $type = $rowData[12];
            $engineMfgName = $rowData[13];
            $engineSerial = $rowData[14];
            $tireSize = $rowData[15];

            if ($clientName != 'Client') {
                echo "Missing column 'Client', encountered: ".$clientName."\n";
                $fileIsValid = false;
            }
            if ($branchName != 'Branch') {
                echo "Missing column 'Branch', encountered: ".$branchName."\n";
                $fileIsValid = false;
            }
            if ($fleetName != 'Fleet') {
                echo "Missing column 'Fleet', encountered: ".$fleetName."\n";
                $fileIsValid = false;
            }
            if ($vehicleNumber != 'Vehicle Unit Number') {
                echo "Missing column 'Client', encountered: ".$vehicleNumber."\n";
                $fileIsValid = false;
            }
            if ($year != 'Year') {
                echo "Missing column 'Year', encountered: ".$year."\n";
                $fileIsValid = false;
            }
            if ($make != 'Make') {
                echo "Missing column 'Make', encountered: ".$make."\n";
                $fileIsValid = false;
            }
            if ($model != 'Model') {
                echo "Missing column 'Model', encountered: ".$model."\n";
                $fileIsValid = false;
            }
            if ($vin != 'Vin#') {
                echo "Missing column 'Vin#', encountered: ".$vin."\n";
                $fileIsValid = false;
            }
            if ($license != 'License Plate #') {
                echo "Missing column 'License Plate #', encountered: ".$license."\n";
                $fileIsValid = false;
            }
            if ($licenseState != 'License State') {
                echo "Missing column 'License State', encountered: ".$licenseState."\n";
                $fileIsValid = false;
            }
            if ($purchasePrice != 'Purchase Price') {
                echo "Missing column 'Purchase Price', encountered: ".$purchasePrice."\n";
                $fileIsValid = false;
            }
            if ($inServiceDate != 'In Service Date') {
                echo "Missing column 'In Service Date', encountered: ".$inServiceDate."\n";
                $fileIsValid = false;
            }
            if ($type != 'Vehicle Type') {
                echo "Missing column 'Vehicle Type', encountered: ".$type."\n";
                $fileIsValid = false;
            }
            if ($engineMfgName != 'Engine Manufacturer') {
                echo "Missing column 'Engine Manufacturer', encountered: ".$engineMfgName."\n";
                $fileIsValid = false;
            }
            if ($engineSerial != 'Engine Serial #') {
                echo "Missing column 'Engine Serial #', encountered: ".$engineSerial."\n";
                $fileIsValid = false;
            }
            if ($tireSize != 'Tire Size') {
                echo "Missing column 'Tire Size', encountered: ".$tireSize."\n";
                $fileIsValid = false;
            }
        }
        else {
            $fileIsValid = false;
        }

        if ($fileIsValid) {
            // loop over data
            while (($rowData = fgetcsv($handle, 0, ",")) !== FALSE) {
                $clientName = $rowData[0];
                $branchName = $rowData[1];
                $fleetName = $rowData[2];
                $vehicleNumber = $rowData[3];
                $year = $rowData[4];
                $make = $rowData[5];
                $model = $rowData[6];
                $vin = $rowData[7];
                $license = $rowData[8];
                $licenseState = $rowData[9];
                $purchasePrice = $rowData[10];
                $inServiceDate = $rowData[11];
                $type = $rowData[12];
                $engineMfgName = $rowData[13];
                $engineSerial = $rowData[14];
                $tireSize = $rowData[15];

                $fleetName = str_replace("’","'",$fleetName);
                $fleet = Fleet::query()->where('tenant_id', $tenant->getKey())->whereRaw('LOWER(name) = ?', [strtolower($fleetName)])->first();
                $vehicleType = VehicleType::query()->where('tenant_id', $tenant->getKey())->whereRaw('LOWER(name) = ?', [strtolower($type)])->first();
                $engineMfg = EngineManufacturer::query()->where('tenant_id', $tenant->getKey())->whereRaw('LOWER(name) = ?', [strtolower($engineMfgName)])->first();

                $licenseStateName = null;
                if (!empty($licenseState)) {
                    $licenseStateName = strtolower($this->states[$licenseState]);
                    //$licenseStateName = geoip_region_name_by_code('US', $licenseState);
                    //if (empty($licenseStateName)) {
                    //    echo 'Vehicle Number ' . $vehicleNumber. " failed to import. Invalid license state abbreviation: " . $licenseState . "\n";
                    //}
                    //else {
                    //    $licenseStateName = strtolower($licenseStateName);
                    //}
                }

                $inServiceDateFormated = null;
                if (!empty($inServiceDate)) {
                    $inServiceDateFormated = DateTime::createFromFormat('m/d/y',$inServiceDate);
                }

                if (empty($fleet)) {
                    echo 'Vehicle Number ' . $vehicleNumber. " failed to import. Missing fleet: " . $fleetName . "\n";
                    continue;
                }

                if (empty($vehicleType)) {
                    echo 'Vehicle Number ' . $vehicleNumber. " failed to import. Missing vehicle type: " . $type . "\n";;
                    continue;
                }

                if (!empty($engineMfgName) && empty($engineMfg)) {
                    echo 'Vehicle Number ' . $vehicleNumber. " failed to import. Missing engine mfg.: " . $engineMfgName . "\n";
                    continue;
                }

                $vehicle = Vehicle::where('vehicle_number', $vehicleNumber)->where('fleet_id', $fleet->id)->first();
                if (empty($vehicle)) {
                    $vehicle = new Vehicle();
                }

                $vehicle->forceFill([
                    'vehicle_number' => $vehicleNumber,
                    'purchase_price' => ($purchasePrice) ? (float)$purchasePrice : 0,
                    'in_service_date' => ($inServiceDateFormated) ? Carbon::createFromTimestamp($inServiceDateFormated->getTimestamp()) : null,
                    'year' => ($year) ? (int)$year : null,
                    'make' => $make,
                    'model' => $model,
                    'vin' => $vin,
                    'tire_size' => $tireSize,
                    'license_plate_number' => $license,
                    'license_state' => $licenseStateName,
                    'engine_serial_number' => $engineSerial,
                    'tenant_id' => $tenant->getKey(),
                    'fleet_id' => $fleet->getKey(),
                    'vehicle_type_id' => $vehicleType->getKey(),
                    'engine_manufacturer_id' => ($engineMfg) ? $engineMfg->getKey() : null,
                ]);

                $vehicle->save();
            }
        }

        fclose($handle);
    }
}
