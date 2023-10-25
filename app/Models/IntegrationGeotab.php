<?php

namespace App\Models;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Geotab\API as GeotabApi;  // Documentation and API examples: https://github.com/Geotab/mygeotab-php/blob/5e6eccc82bb24816a7eee844d884031be368ca7c/sample.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class IntegrationGeotab extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['integration_id',
    ];


    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public static function findByIntegration($integrationId)
    {
        return IntegrationGeotab::where('integration_id', $integrationId)->firstOrFail();
    }

    public function runTask($task, $integrationRun, $params)
    {
        $username = $this->integration->username;
        $pwd = $this->integration->password;
        $results = null;
        // API documentation:  https://github.com/Geotab/mygeotab-php
        $integrationRun->logMessage("connecting to geotab...");
        $api = new GeotabApi($username, $pwd, $this->geotab_database, "my.geotab.com");
        $api->authenticate();
        $integrationRun->logMessage("connection successful");
        switch ($task) {
            case 'TestConnection':
                $results = $this->testConnection($api, $integrationRun, $params);
                break;
            case 'SimpleTest':
                $results = $this->runSimpleTest($api, $integrationRun, $params);
                break;
            case 'MeterReadings':
                $results = $this->getMeterReadings($api, $integrationRun, $params);
                break;
            case 'DriverViolations':
                $results = $this->getDriverViolations($api, $integrationRun, $params);
                break;
            case 'FuelTax':
                $results = $this->getFuelTax($api, $integrationRun, $params);
                break;
            default:
                throw new Exception("Missing integration task" . $task);
                break;
        }
        return $results;
    }

    private function testConnection($api, $integrationRun, $params)
    {
        // Connection already worked if it made it this far
        $integrationRun->logMessage("connection to Geotab is confirmed");
        $integrationRun->setTotal(1);
        $integrationRun->successPlus();
        $results = [];
        return $results;
    }

    private function runSimpleTest($api, $integrationRun, $params)
    {
        $integrationRun->logMessage("retrieving data for one vehicle's device");
        $integrationRun->setTotal(1);
        $results = $api->get("Device", ["resultsLimit" => 1]);
        $integrationRun->successPlus();
        $integrationRun->logMessage("data for device retrieved successfully");
        return $results;
    }

    private function getDriverViolations($api, $integrationRun, $params)
    {
        $integrationRun->setTotal(1);
        $toDate = new DateTime();
        $fromDate = new DateTime();
        $fromDate->modify("-1 month");
        $violations = $api->get("DutyStatusViolation", [
            "search" => [
                "userSearch" => ["id" => "b1"],
                "toDate" => $toDate->format("c"),   // ISO8601, or could use "2018-11-03 00:53:29.370134"
                "fromDate" => $fromDate->format("c")
            ],
            "resultsLimit" => 10
        ]);
        $integrationRun->setTotal(count($violations));
        $integrationRun->successPlus(count($violations));
        return $violations;
    }

    private function getMeterReadings($api, $integrationRun, $params)
    {
        // https://community.geotab.com/s/article/How-To-View-Odometer-Engine-Hours-Adjustment-Using-An-API-Call?language=en_US
        $dateTimeZone = isset($params['dateTimeZone']) ? new DateTimeZone($params['dateTimeZone']) : null;
        $fromDate = isset($params['startDate']) ? new DateTime($params['startDate'], $dateTimeZone) : new DateTime(null, $dateTimeZone);
        $toDate = isset($params['endDate']) ? new DateTime($params['endDate'], $dateTimeZone) : new DateTime(null, $dateTimeZone);
        $toDate->setTime(23, 59, 59); //end of day
        $tenantId = $this->tenant_id;
        $integrationRun->logMessage("Running geotab odometer readings import from " . $fromDate->format("c") . " and " . $toDate->format("c"), false, true);
        $unitOfMeasure = isset($params['$unitOfMeasure']) ? $params['$unitOfMeasure'] : 'miles';
        $unitOfMeasureConversionDivider = 1;
        //Geotab Meter readings are always in meters
        switch ($unitOfMeasure) {
            case 'miles':
                $unitOfMeasureConversionDivider = 1609.344; //1 mile = 1609.344 meters
                break;
            default:
                $unitOfMeasureConversionDivider = 1; //default to 1 (no conversions)
                break;
        }
        // Possible optimization is to get all the devices from TZ first, index by vin#
        $deviceIndexByIdVin_Geotab = [];
        $meterReadingsToImport = [];
        // Get all the devices from Geotab that were active after fromDate and before toDate datetime
        // make one call for each day.  took to long to loop through all events so get the last event
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($fromDate, $interval, $toDate);
        $multicalls = [];
        foreach ($period as $dt) {
            $integrationRun->logMessage($dt->format("Y-m-d"), false, true);
            $tFromDate = clone $dt;
            $tToDate = clone $dt;
            $tToDate->setTime(23, 59, 59); //end of day
            $apiParams = [
                //"resultsLimit" => 3,
                "search" => [
                    "fromDate" => $tFromDate->format("c"),
                    "toDate" => $tToDate->format("c")
                ],
                "propertySelector" => [
                    "fields" => ['id', 'name', 'vehicleIdentificationNumber', 'engineVehicleIdentificationNumber']
                ],
            ];
            $integrationRun->logMessage("retrieving active geotab devices between " . $tFromDate->format("c") . " and " . $tToDate->format("c") . "...");
            $devices = $api->get("Device", $apiParams);
            //$integrationRun->logMessage("found " . count($devices) . " active devices");

            // Now using a multicall to get all the odometer readings for active devices in the date range
            $multicalls = [];
            foreach ($devices as $key => $device) {
                $deviceIndexByIdVin_Geotab[$device['id']] = $device['vehicleIdentificationNumber'];
                $apiParams = [
                    "typeName" => 'StatusData',
                    //"resultsLimit" => 3,
                    "search" => [
                        "diagnosticSearch" => ["id" => "DiagnosticOdometerId"],
                        "deviceSearch" => ["id" => $device['id']],
                        "fromDate" => $tFromDate->format("c"),
                        "toDate" => $tToDate->format("c")
                    ],
                ];
                $apiCall = ["Get", $apiParams];
                array_push($multicalls, $apiCall);
            }

            $integrationRun->logMessage("retrieving odometer readings for active devices " . $tFromDate->format("c") . " and " . $tToDate->format("c") . "...");
            $deviceStatusData = $api->multiCall($multicalls);
            $integrationRun->logMessage("found " . count($deviceStatusData). " active devices to process");

            // Get last meter reading from each day in the range
            $logStr = "";
            foreach ($deviceStatusData as $deviceDataEvents) {
                $lastMeterReadingByDay = [];
                /*
                The code immediatly following this block prev was in the loop running on each $deviceEvent
                This takes way too long, some have 26,000+ deviceEvents in one week.
                Instead changed the api multicall above to run one per day, and can shortcut by pulling the last entry as the last reading of the day
                foreach ($deviceDataEvents as $deviceEvent) {
                }
                */
                // Loop through device events in reverse order, first one with odometer is the ending day odometer reading
                $deviceEvent = null;
                for (end($deviceDataEvents); ($currentKey = key($deviceDataEvents)) !== null; prev($deviceDataEvents)) {
                    $tDeviceEvent = current($deviceDataEvents);
                    if ($tDeviceEvent['data']) {
                        $deviceEvent = $tDeviceEvent;
                        break;  //found it, drop out of loop
                    }
                }
                if ($deviceEvent != null) {
                    $meter = $deviceEvent['data'];
                    $meterReadingDateTimeStr = $deviceEvent['dateTime'];
                    $meterId = $deviceEvent['diagnostic']['id'] . '|' . $deviceEvent['id'];
                    $deviceId = $deviceEvent['device']['id'];
                    $vin = $deviceIndexByIdVin_Geotab[$deviceId];
                    // DateTime::createFromFormat('c', $meterReadingDateTimeStr) Doesn't work!, have to use DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $meterReadingDateTimeStr)
                    $meterReadingDateOnly = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $meterReadingDateTimeStr);
                    $key = "Device Id: " . $deviceId . ", Vin#:" . $vin . ", Date&Time: " . $meterReadingDateTimeStr . ", Meter Reading: " . $meter . ", Geotab event id: " . $meterId;
                    $logStr = $logStr . " | processing geotab data: " . $key . " | ";
                    // $integrationRun->logMessage("processing: " . $key); //causes to many log saves
                    if ($meterReadingDateOnly === false) {
                        $results['failed'][$key] = "Invalid date format encountered from Geotab, skipping.";
                        continue;
                    }
                    $meterReadingDateOnlyStr = $meterReadingDateOnly->format('Y-m-d');
                    if (!isset($lastMeterReadingByDay[$deviceId])) {
                        $lastMeterReadingByDay[$deviceId] = [];
                    }
                    $lastMeterReadingByDay[$deviceId][$meterReadingDateOnlyStr] = $deviceEvent;

                    // $integrationRun->logMessage("geotab events: " . $logStr); //string too long
                    foreach ($lastMeterReadingByDay as $lmrbd) {
                        foreach ($lmrbd as $lastMeterReading) {
                            array_push($meterReadingsToImport, $lastMeterReading);
                            // $integrationRun->logMessage("last meter reading per day is: meters: " . $lastMeterReading['data'] . ", dateTime: " . $lastMeterReading['dateTime'] . ", geotab id: " . $lastMeterReading['id']);
                        }
                    }
                }
            }
        }

        // Now we have only the latest reading per day to import into TZ, upsert TZ one per day
        $integrationRun->setTotal(count($meterReadingsToImport));
        $results = [];
        $results['count_total'] = count($meterReadingsToImport);
        $results['count_successful'] = 0;
        $results['count_failed'] = 0;
        $results['failed'] = [];
        $integrationRun->logMessage("Found " . count($meterReadingsToImport) . " daily meter readings to import into TZ", false, true);
        foreach ($meterReadingsToImport as $meterReadingToImport) {
            $key = "";
            $meter = $meterReadingToImport['data'];
            $meterReadingDateTimeStr = $meterReadingToImport['dateTime'];
            $meterId = $meterReadingToImport['diagnostic']['id'];
            $deviceId = $meterReadingToImport['device']['id'];
            if ($meterReadingToImport['id']) {
                $meterId = $meterId . '|' . $meterReadingToImport['id'];
            }
            else {
                $meterId = $meterId . '|' . $deviceId;
            }
            $vin = $deviceIndexByIdVin_Geotab[$deviceId];
            $meterConverted = floor($meter / $unitOfMeasureConversionDivider);
            $externalSource = 'Geotab Odometer Event';
            $key = "Device Id: ". $deviceId . ", Vin#:" . $vin . ", Date&Time: " . $meterReadingDateTimeStr . ", Meter Reading: " . $meter . ", Geotab event id: " . $meterId;
            $integrationRun->logMessage("Importing: " . $key);
            try {
                $meterReadingDatetime = new DateTime($meterReadingDateTimeStr);
                if (!$vin) {
                    throw new Exception("Encountered Geotab meter reading on a device without a vin# in Geotab. Geotab device id: ". $deviceId);
                }
                $vehicle = Vehicle::findByVin($tenantId, $vin);
                if (!$vehicle) {
                    throw new Exception("TranzIT vehicle match by vin# not found. vin#: " . $vin);
                }
                $meterReadingQuery = MeterReading::findByIntegrationVehicleDay($tenantId, $this->integration->id, $vehicle->id, $meterReadingDateTimeStr, 1);
                if ($meterReadingQuery->count() > 0) {
                    $meterReading = $meterReadingQuery[0];
                    if ($meterReading->meter_reading == ((int) round(($meterConverted), 0))) {
                        $integrationRun->logMessage("This reading has already been imported, skipping update.");
                    }
                    else {
                        $meterReading->meter_reading = $meterConverted;
                        // $meterReading->meter_reading_date = $meterReadingDatetime->format('Y-m-d H:i:s');
                        $meterReading->meter_reading_date = $meterReadingDatetime;
                        $meterReading->external_id = $meterId;
                        $integrationRun->logMessage("Updating meter reading in TZ.");
                        $meterReading->save();
                    }
                }
                else {
                    $meterReading = new MeterReading();
                    $meterReading->tenant_id = $tenantId;
                    $meterReading->vehicle_id = $vehicle->id;
                    $meterReading->meter_reading = $meterConverted;
                    // $meterReading->meter_reading_date = $meterReadingDatetime->format('Y-m-d H:i:s');;
                    $meterReading->meter_reading_date = $meterReadingDatetime;
                    $meterReading->source_id = $this->integration->id;
                    $meterReading->source = 'Integration';
                    $meterReading->external_source = $externalSource;
                    $meterReading->external_id = $meterId;
                    $integrationRun->logMessage("Inserting meter reading into TZ.");
                    $meterReading->save();
                }
                $results['count_successful']++;
                $integrationRun->successPlus();
                $integrationRun->logMessage("meter reading imported successfully: " . $key, false, true);
            }
            catch (Exception $e) {
                $results['count_failed']++;
                $results['failed'][$key] = $e->getMessage();
                $integrationRun->failedPlus();
                $integrationRun->logMessage("error: " . $e->getMessage(), true, true);
            }
        }

        return $results;
    }



    private function getFuelTax($api, $integrationRun, $params)
    {
        $dateTimeZone = isset($params['dateTimeZone']) ? new DateTimeZone($params['dateTimeZone']) : null;
        $fromDate = isset($params['startDate']) ? new DateTime($params['startDate'], $dateTimeZone) : new DateTime(null, $dateTimeZone);
        $toDate = isset($params['endDate']) ? new DateTime($params['endDate'], $dateTimeZone) : new DateTime(null, $dateTimeZone);
        $toDate->setTime(23, 59, 59); //end of day
        $tenantId = $this->tenant_id;

        $unitOfMeasure = isset($params['$unitOfMeasure']) ? $params['$unitOfMeasure'] : 'miles';
        $unitOfMeasureConversionDivider = 1;
        //Geotab Meter readings are always in meters
        switch ($unitOfMeasure) {
            case 'miles':
                $unitOfMeasureConversionDivider = 1.609344; //1 mile = 1.609344 kilometers (geotab fuel tax number) = 1609.344 meters
                break;
            default:
                $unitOfMeasureConversionDivider = 1; //default to 1 (no conversions)
                break;
        }

        // Possible optimization is to get all the devices from TZ first, index by vin#
        $deviceIndexByIdVin_Geotab = [];

        // Get all the devices from Geotab that were active after fromDate and before toDate datetime
        //$fromDate = new DateTime();
        //$fromDate->modify("-1 day");
        $apiParams = [
            //"resultsLimit" => 3,
            "search" => [
                "fromDate" => $fromDate->format("c"),
                "toDate" => $toDate->format("c")
            ],
            "propertySelector" => [
                "fields" => ['id', 'name', 'vehicleIdentificationNumber', 'engineVehicleIdentificationNumber']
            ],
        ];
        $integrationRun->logMessage("retrieving geotab devices that were active between " . $fromDate->format("c") . " and " . $toDate->format("c"), false, true);
        $devices = $api->get("Device", $apiParams);
        $integrationRun->logMessage("found " . count($devices) . " active devices", false, true);
        foreach ($devices as $key => $device) {
            $deviceIndexByIdVin_Geotab[$device['id']] = $device['vehicleIdentificationNumber'];
        }

        // Now get fuel tax report
        $apiParams = [
            "typeName" => 'FuelTaxDetail',
            //"resultsLimit" => 3,
            "search" => [
                "fromDate" => $fromDate->format("c"),
                "toDate" => $toDate->format("c"),
                "includeBoundaries" => false,
                "includeHourlyData" => false
            ],
        ];
        $integrationRun->logMessage("retrieving geotab fuel tax entries between " . $fromDate->format("c") . " and " . $toDate->format("c"), false, true);
        $fuelTaxDetails = $api->get("FuelTaxDetail", $apiParams);
        $integrationRun->logMessage("found " . count($fuelTaxDetails) . " fuel tax entries", false, true);

        // Loop through tax details and import TZ meter readings
        $integrationRun->setTotal(count($fuelTaxDetails));
        $results = [];
        $results['count_total'] = count($fuelTaxDetails);
        $results['count_successful'] = 0;
        $results['count_failed'] = 0;
        $results['failed'] = [];
        foreach ($fuelTaxDetails as $fuelTaxDetail) {
            try {
                $fuelTaxDetailId = $fuelTaxDetail['id'];
                $deviceId = $fuelTaxDetail['device']['id'];
                $vin = $deviceIndexByIdVin_Geotab[$deviceId];
                $jurisdiction = $fuelTaxDetail['jurisdiction']; // in state abvrev form
                $enterOdometer = $fuelTaxDetail['enterOdometer'];
                $exitOdometer = $fuelTaxDetail['exitOdometer'];
                $distance = $exitOdometer - $enterOdometer;
                $enterTimeStr = $fuelTaxDetail['enterTime'];
                $exitTimeStr = $fuelTaxDetail['exitTime'];
                $isNegligible = $fuelTaxDetail['isNegligible'];
                $externalSource = 'Geotab Fuel Tax Detail';
                $key = "Device Id: ". $deviceId . ", Vin#:" . $vin . ", Jurisdiction: " . $jurisdiction . ", Enter meter: " . $enterOdometer . ", Exit meter: " . $exitOdometer . ", Enter time: " . $enterTimeStr . ", Exit time: " . $exitTimeStr . ", Geotab fuel tax detail id: " . $fuelTaxDetailId;
                $integrationRun->logMessage("Importing: " . $key, false, true);
                if (!$vin) {
                    throw new Exception("Encountered Geotab meter reading on a device without a vin# in Geotab. Geotab device id: ". $deviceId);
                }
                $enterTime = new DateTime($enterTimeStr);
                $exitTime = new DateTime($exitTimeStr);
                $enterOdometerConverted = floor($enterOdometer / $unitOfMeasureConversionDivider);
                $exitOdometerConverted = floor($exitOdometer / $unitOfMeasureConversionDivider);
                if ($isNegligible && $distance >= 1) {
                    throw new Exception("Encountered Geotab entry with invalid odomter readings. isNegligible is true but distance is greater than 1. Enter Odometer: " . $enterOdometerConverted . ", Exit Odometer: " . $exitOdometerConverted);
                }
                if (!$isNegligible && $distance < 1) {
                    throw new Exception("Encountered Geotab entry with invalid odomter readings. isNegligible is false but distance is less than 1. Enter Odometer: " . $enterOdometerConverted . ", Exit Odometer: " . $exitOdometerConverted);
                }
                if (!$vin) {
                    throw new Exception("Encountered Geotab fuel tax meter reading on a device without a vin# in Geotab. Geotab device id: ". $deviceId);
                }
                if ($isNegligible) {
                    $integrationRun->logMessage("Geotab flagged this fuel tax detail as Negligible, skipping import", false, true);
                    $results['count_successful']++;
                    $integrationRun->successPlus();
                    continue;
                }
                $vehicle = Vehicle::findByVin($tenantId, $vin);
                if (!$vehicle) {
                    throw new Exception("TranzIT vehicle match by vin# not found. vin#: " . $vin);
                }
                if (State::STATES[$jurisdiction]) {
                    $locationState = State::STATES[$jurisdiction];
                }
                else {
                    throw new Exception("Could not find matching location abbreviation for ".$jurisdiction);
                }
                // Now create/update enter meter reading in TranzIT
                $source = 'Enter State Line';
                $meterReadingQuery = MeterReading::findByExternalId($tenantId, $this->integration->id, $fuelTaxDetailId, $externalSource, $source, $vehicle->id, 1);
                if ($meterReadingQuery->count() > 0) {
                    $meterReading = $meterReadingQuery[0];
                    if ($meterReading->meter_reading == ((int) round(($enterOdometerConverted), 0))) {
                        $integrationRun->logMessage("This fuel tax meter reading has already been imported, skipping update.", false, true);
                    }
                    else {
                        $meterReading->meter_reading = $enterOdometerConverted;
                        $meterReading->meter_reading_date = $enterTime;
                        $meterReading->location_state = $locationState;
                        $integrationRun->logMessage("Updating fuel tax enter meter reading in TZ.", false, true);
                        $meterReading->save();
                    }
                }
                else {
                    // Enter meter reading
                    $meterReading = new MeterReading();
                    $meterReading->tenant_id = $tenantId;
                    $meterReading->vehicle_id = $vehicle->id;
                    $meterReading->meter_reading = $enterOdometerConverted;
                    // $meterReading->meter_reading_date = $enterTime->format('Y-m-d H:i:s');;
                    $meterReading->meter_reading_date = $enterTime;
                    $meterReading->location_state = $locationState;
                    $meterReading->source_id = $this->integration->id;
                    $meterReading->source = $source;
                    $meterReading->external_source = $externalSource;
                    $meterReading->external_id = $fuelTaxDetailId;
                    $integrationRun->logMessage("Inserting enter state line meter reading into TZ.", false, true);
                    $meterReading->save();
                }
                // Now create/update enter meter reading in TranzIT
                $source = 'Exit State Line';
                $meterReadingQuery = MeterReading::findByExternalId($tenantId, $this->integration->id, $fuelTaxDetailId, $externalSource, $source, $vehicle->id, 1);
                if ($meterReadingQuery->count() > 0) {
                    $meterReading = $meterReadingQuery[0];
                    if ($meterReading->meter_reading == ((int) round(($exitOdometerConverted), 0))) {
                        $integrationRun->logMessage("This fuel tax meter reading has already been imported, skipping update.", false, true);
                    }
                    else {
                        $meterReading->meter_reading = $exitOdometerConverted;
                        $meterReading->meter_reading_date = $exitTime;
                        $meterReading->location_state = $locationState;
                        $integrationRun->logMessage("Updating fuel tax exit meter reading in TZ.", false, true);
                        $meterReading->save();
                    }
                }
                else {
                    // Exit meter reading
                    $meterReading = new MeterReading();
                    $meterReading->tenant_id = $tenantId;
                    $meterReading->vehicle_id = $vehicle->id;
                    $meterReading->meter_reading = $exitOdometerConverted;
                    // $meterReading->meter_reading_date = $enterTime->format('Y-m-d H:i:s');;
                    $meterReading->meter_reading_date = $exitTime;
                    $meterReading->location_state = $locationState;
                    $meterReading->source_id = $this->integration->id;
                    $meterReading->source = $source;
                    $meterReading->external_source = $externalSource;
                    $meterReading->external_id = $fuelTaxDetailId;
                    $integrationRun->logMessage("Inserting exit state line meter reading into TZ.", false, true);
                    $meterReading->save();
                }

                $results['count_successful']++;
                $integrationRun->successPlus();
                $integrationRun->logMessage("Fuel tax detail meter readings created or updated in TZ successfully", false, true);
            }
            catch (Exception $e) {
                $results['count_failed']++;
                $results['failed'][$key] = $e->getMessage();
                $integrationRun->failedPlus();
                $integrationRun->logMessage("error: " . $e->getMessage(), true, true);
            }
        }

        return $results;
    }

}
