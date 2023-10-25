<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use DateTime;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isEmpty;

class FuelTaxController extends Controller
{
    private const PERMISSION_ENTITY = 'fuel_tax';

    // GET /reports/fuel_tax
    public function index(Request $request)
    {
        $data = [];

        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $startDate = $request->has('startDate') ? $request->get('startDate') : null;
        $endDate = $request->has('endDate') ? ($request->get('endDate')) : null;
        $clientId = $request->has('filterByClient') ? ($request->get('filterByClient')) : null;
        $branchId = $request->has('filterByBranch') ? ($request->get('filterByBranch')) : null;
        $fleetId = $request->has('filterByFleet') ? ($request->get('filterByFleet')) : null;
        $reportType = $request->has('reportType') ? ($request->get('reportType')) : "summary";

        $tStartDate = new DateTime($startDate);
        $tEndDate = new DateTime($endDate);
        //$tStartDate = (new DateTime($startDate))->modify('first day of this month');
        //$tEndDate = (new DateTime($endDate))->modify('last day of this month');

        //Get all the vehicles that had meter readings in the date range
        $meterReadingsByVehicleStateMeter = [];
        $meterReadingIndex = [];
        //$sources = ['Enter State Line','Exit State Line']; //only select list of distinct vehicles rather than the full list of meter readings
        $sources = null; //get all meter readings
        $meterReadings = MeterReading::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId, false, $sources);
        foreach ($meterReadings as $key => $meterReading) {
            $meterReadingIndex[$meterReading->id] = $meterReading;
            $vehicleId = $meterReading->vehicle_id;
            // $locationKey = $meterReading->location_country . " | " . $meterReading->location_state; //Country + State needed?
            $locationKey = $meterReading->location_state;
            if ($meterReading->vehicle === null) {
                //TODO: Tmp solution because the cascade delete on deleting a vehicle apparently didn't work, need to skip if vehicle was deleted.
                continue;
            }
            if (!array_key_exists($vehicleId, $meterReadingsByVehicleStateMeter)) {
                // Vehicle
                $meterReadingsByVehicleStateMeter[$vehicleId] = [];
            }
            if (!array_key_exists($locationKey, $meterReadingsByVehicleStateMeter[$vehicleId])) {
                // Location
                $meterReadingsByVehicleStateMeter[$vehicleId][$locationKey] = [];
            }
            if (!array_key_exists($meterReading->id, $meterReadingsByVehicleStateMeter[$vehicleId][$locationKey])) {
                //meter reading by state
                $meterReadingsByVehicleStateMeter[$vehicleId][$locationKey][$meterReading->id] = $meterReading->meter_reading;
            }
        }

        //Now sort the meterReadings within the vehicle state arrays
        $report = [];
        foreach ($meterReadingsByVehicleStateMeter as $vehicleIdKey => $locations) {
            $vehicleReport = [];
            foreach ($locations as $locationKey => $meterReadingIds) {
                $enterMeter = null;
                $exitMeter = null;
                $log = [];
                asort($meterReadingIds); // will sort by array value which is meter_reading (odometer value)
                $lastExitReadingIndex = null;
                $index = 0;
                $matchOnLastEntry = false;
                foreach ($meterReadingIds as $meterReadingId => $odometer) {
                    $meterReading = $meterReadingIndex[$meterReadingId];
                    $log[] = "Meter Reading: veh#: '$meterReading->vehicle_number', source: '$meterReading->source', meter: '$meterReading->meter_reading', loc: '$meterReading->location_state', date: '$meterReading->meter_reading_date'";
                    // If source contains enter, and enterMeter is null, we found the first enter reading so set it
                    if ( (str_contains(strtolower($meterReading->source), 'enter')) && ($enterMeter == null)) {
                        $enterMeter = $meterReading;
                        $log[] = "Found Enter";
                    }
                    // If the source contains exit, and the exitMeter is null and found enter is true, we found the matching exit reading so set it
                    if ((str_contains(strtolower($meterReading->source), 'exit')) && $enterMeter != null) {
                        $exitMeter = $meterReading;
                        $log[] = "Found Exit";
                        $lastExitReadingIndex = $index;
                    }
                    // If the enterMeter and exitMeter are both not null, and in order, add an entry to the report
                    if ($enterMeter != null && $exitMeter != null) {
                        //ONLY IF Prev Exit Meter doesn't equal the current enter meter (fix for Geotab data enter, exits even though in the same state)
                        $log[] = "Adding enter and exit to a report entry";
                        FuelTaxController::addReportEntry($vehicleReport, $enterMeter, $exitMeter);
                        // and clear the enter and exit meters, for next match
                        $enterMeter = null;
                        $exitMeter = null;
                        if ($index == count($meterReadingIds)-1) {
                            $matchOnLastEntry = true;
                        }
                    }
                    $index++;
                }
                if ($matchOnLastEntry) {
                    //Don't need to check for enter/exit matches, we are done. go to next location.
                    continue;
                }
                else {
                    if ($enterMeter == null) {
                        //never found an enter for this location.  go ahead and set it to the first entry after the last exit
                        $keys = array_keys($meterReadingIds);
                        if ($lastExitReadingIndex == null) {
                            $firstMeterReading = $keys[0]; //Set enter meter to the first entry
                        } else if ($lastExitReadingIndex + 1 >= count($meterReadingIds)) {
                            $firstMeterReading = array_key_last($keys); //Set enter meter to the last entry
                        } else {
                            $firstMeterReading = $keys[$lastExitReadingIndex + 1]; //Set entry to the first entry after the last exit
                        }
                        $enterMeter = $meterReadingIndex[$firstMeterReading];
                        $log[] = "Enter not found, setting enter to first reading: $enterMeter->meter_reading after last exit";
                    }
                    if ($enterMeter && !$exitMeter) {
                        // Then we never found the last exit to match the entry, so set it to the last entry in sorted $meterReadingIds array
                        $lastMeterReading = array_key_last($meterReadingIds);
                        $exitMeter = $meterReadingIndex[$lastMeterReading];
                        $log[] = "Exit not found, setting enter to last reading: $exitMeter->meter_reading";
                    }
                    if ($enterMeter != null && $exitMeter != null) {
                        //and add an entry to the report
                        $log[] = "Adding enter and exit to a report entry";
                        FuelTaxController::addReportEntry($vehicleReport, $enterMeter, $exitMeter);
                        $enterMeter = null;
                        $exitMeter = null;
                    }
                }
            }

            //Now loop through the vehicle's fuel tax report entries and add them to the full report
            //Check if it's an entry that didn't actually leave the state and merge them together
            $prevEntry = null;
            $reportEntry = [];
            $index = 0;
            if ($reportType === 'detailed') {
                // Detailed Report
                foreach ($vehicleReport as $key => $reportEntry) {
                    $report[] = $reportEntry; //add the entry to the report
                }
            }
            else {
                // Summary report
                foreach ($vehicleReport as $key => $currentEntry) {
                    if ($reportEntry == null) {
                        $reportEntry = $currentEntry;
                    } else if (($currentEntry['enter_meter'] === $prevEntry['exit_meter']) && ($currentEntry['vehicle_number'] === $prevEntry['vehicle_number']) && ($currentEntry['location_state'] === $prevEntry['location_state'])) {
                        $reportEntry['exit_meter'] = $currentEntry['exit_meter'];
                        $reportEntry['exit_meter_reading_date'] = $currentEntry['exit_meter_reading_date'];
                        $reportEntry['distance'] = $reportEntry['distance'] + $currentEntry['distance'];
                        if ($index === count($vehicleReport) - 1) {
                            //It's the last entry so add it
                            $report[] = $reportEntry; //add the entry to the report
                            $reportEntry = null;
                        }
                    } else {
                        $report[] = $reportEntry; //add the entry to the report
                        $reportEntry = null;
                    }
                    $prevEntry = $currentEntry;
                    $index++;
                }
            }
        }
        //Remove zero distance entries
        $tReport = [];
        foreach ($report as $key => $currentEntry) {
            if ($currentEntry['distance'] !== 0) {
                $tReport[] = $currentEntry;
            }
        }
        $report = $tReport;
        $response = [];
        $response['data'] = $report;
        return $response;
    }

    //Add an entry to the report
    private static function addReportEntry(array &$report, $enterMeterReading, $exitMeterReading)
    {
        $reportEntry = [];
        $reportEntry['vehicle_id'] = $enterMeterReading->vehicle_id;
        $reportEntry['vehicle_number'] = $enterMeterReading->vehicle_number;
        $reportEntry['client_name'] = $enterMeterReading->client_name;
        $reportEntry['client_id'] = $enterMeterReading->client_id;
        $reportEntry['branch_name'] = $enterMeterReading->branch_name;
        $reportEntry['branch_id'] = $enterMeterReading->branch_id;
        $reportEntry['fleet_name'] = $enterMeterReading->fleet_name;
        $reportEntry['fleet_id'] = $enterMeterReading->fleet_id;
        $reportEntry['enter_meter'] = $enterMeterReading->meter_reading;
        $reportEntry['exit_meter'] = $exitMeterReading->meter_reading;
        $reportEntry['distance'] = $exitMeterReading->meter_reading - $enterMeterReading->meter_reading;
        $reportEntry['location_state'] = ucwords($enterMeterReading->location_state);
        $reportEntry['location_country'] = $enterMeterReading->location_country;
        $reportEntry['enter_meter_reading_date'] = $enterMeterReading->meter_reading_date;
        $reportEntry['exit_meter_reading_date'] = $exitMeterReading->meter_reading_date;
        //$reportEntry['meter_reading_type'] = null;
        if ($reportEntry['distance'] < 0) {
            $reportEntry['distance'] = 0;
        }
        if ($enterMeterReading->id == $exitMeterReading->id) {
            $reportEntry['exit_meter'] = null;
            $reportEntry['exit_meter_reading_date'] = null;
            $reportEntry['distance'] = null;
        }
        $report[] = $reportEntry;
    }

}
