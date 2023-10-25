<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Fueling;
use App\Models\LineItemType;
use App\Models\MeterReading;
use App\Models\RepairOrder;
use App\Models\Tenant;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;

class CostPerMileDetailedController extends Controller
{
    private const PERMISSION_ENTITY = 'cost_per_mile_detailed';

    // GET /reports/cost_per_mile_detailed
    public function index(Request $request) {
        $data = [];
        $skipContributors = true; //Don't include contributors in this report. Future TODO: allow to toggle the report

        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $reportType = $request->has('reportType') ? $request->get('reportType') : 'month';
        $unitType = $request->has('unitType') ? $request->get('unitType') : 'vehicle';
        $startDate = $request->has('startDate') ? $request->get('startDate') : null;
        $endDate = $request->has('endDate') ? ($request->get('endDate')) : null;
        $clientId = $request->has('filterByClient') ? ($request->get('filterByClient')) : null;
        $branchId = $request->has('filterByBranch') ? ($request->get('filterByBranch')) : null;
        $fleetId = $request->has('filterByFleet') ? ($request->get('filterByFleet')) : null;

        $tStartDate = new DateTime($startDate);
        $tEndDate = new DateTime($endDate);
        //$tStartDate = (new DateTime($startDate))->modify('first day of this month');
        //$tEndDate = (new DateTime($endDate))->modify('first day of this month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($tStartDate, $interval, $tEndDate);

        $monthKeyFormat = "M_y";

        //Calculate distance traveled, start and end meters on each vehicle individually.
        //  Then add them by $unitKey
        //  End Meter = the last meter reading in the date range per vehicle
        //  Start Meter = last meter reading PRIOR to the date range per vehicle

        //Get all the vehicles that had meter readings in the date range
        $vehicles = [];
        $distinctVehicles = false; //only select list of distinct vehicles rather than the full list of meter readings
        $meterReadings = MeterReading::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId, $distinctVehicles);
        foreach ($meterReadings as $key => $meterReading) {
            if ($meterReading->vehicle === null) {
                //TODO: Tmp solution because the cascade delete on deleting a vehicle apparently didn't work, need to skip if vehicle was deleted.
                continue;
            }
            $vehicleNumber = $meterReading->vehicle_number;
            $unitKey = $this->getUnitKey($unitType, $meterReading);
            if (!isset($vehicles[$vehicleNumber]["id"])) {
                $vehicles[$vehicleNumber]["id"] = $meterReading->vehicle->id;
            }
            if (!isset($vehicles[$vehicleNumber]["unit_key"])) {
                $vehicles[$vehicleNumber]["unit_key"] = $unitKey;
            }
            if (!isset($vehicles[$vehicleNumber]["vehicle_number"])) {
                $vehicles[$vehicleNumber]["vehicle_number"] = $meterReading->vehicle_number;
            }
            $this->addUnitDetails($data, $unitKey, $unitType, $meterReading);
        }
        foreach ($vehicles as $vehicleNumber => $vehicle) {
            $vehicleStartMeterForPeriod = 0;
            $vehicleEndMeterForPeriod = 0;
            $distance = 0;
            $vehicleId = $vehicle["id"];
            $unitKey = $vehicle["unit_key"];
            $startMeterReading = MeterReading::byVehicleLessThanDate($vehicleId, $tStartDate, 1);
            $endMeterReading = MeterReading::byVehicleLessThanDate($vehicleId, $tEndDate, 1);
            if ($startMeterReading->count() > 0) {
                $vehicleStartMeterForPeriod = (int) $startMeterReading[0]->meter_reading;
            }
            if ($endMeterReading->count() > 0) {
                $vehicleEndMeterForPeriod = (int) $endMeterReading[0]->meter_reading;
            }
            $distance = $vehicleEndMeterForPeriod - $vehicleStartMeterForPeriod;
            if (!isset($data[$unitKey]["distance"])) {
                $data[$unitKey]["distance"] = 0;
            }
            if ($distance > 0) {
                $data[$unitKey]["distance"] += $distance;
            }

            if ($unitType == 'vehicle') {
                if (!isset($data[$unitKey]["start_meter"])) {
                    $data[$unitKey]["start_meter"] = $vehicleStartMeterForPeriod;
                }
                if (!isset($data[$unitKey]["end_meter"])) {
                    $data[$unitKey]["end_meter"] = $vehicleEndMeterForPeriod;
                }
            }
            else {
                if (!isset($data[$unitKey]["start_meter"])) {
                    $data[$unitKey]["start_meter"] = null;
                }
                if (!isset($data[$unitKey]["end_meter"])) {
                    $data[$unitKey]["end_meter"] = null;
                }
            }
        }



//        //  Because of potential bad meter reading data for distance calculation:
//        //  Check the Start and End Meter reading against the last x readings, take the greatest
//        $vehicles = [];
//        $meterReadings = MeterReading::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId);
//        foreach ($meterReadings as $key => $meterReading) {
//            if ($skipContributors && $meterReading['system_vehicle_type_name'] == 'Contributor') {
//                continue;
//            }
//
//            $unitKey = $this->getUnitKey($unitType, $meterReading);
//            $this->addUnitDetails($data, $unitKey, $unitType, $meterReading);
//
//            $vehicleNumber = $meterReading->vehicle_number;
//            //now initialize the distance, start_meter, end_meter if they haven't been set yet
//            if (!isset($vehicles[$vehicleNumber]["id"])) {
//                $vehicles[$vehicleNumber]["id"] = (int) $meterReading->vehicle_id;
//            }
//            if (!isset($vehicles[$vehicleNumber]["distance"])) {
//                $vehicles[$vehicleNumber]["distance"] = 0;
//            }
//            if (!isset($vehicles[$vehicleNumber]["unit_key"])) {
//                $vehicles[$vehicleNumber]["unit_key"] = $unitKey;
//            }
//
//            if (!isset($vehicles[$vehicleNumber]["start_meter"])) {
//                $vehicles[$vehicleNumber]["start_meter"] = (int) $meterReading->meter_reading;
//            }
//            if (!isset($vehicles[$vehicleNumber]["end_meter"])) {
//                $vehicles[$vehicleNumber]["end_meter"] = (int) $meterReading->meter_reading;
//            }
//
//            //now calculate the highest and lowest meter reading in the date range (regardless of date recorded).
//            if ((int) $meterReading->meter_reading < $vehicles[$vehicleNumber]["start_meter"]) {
//                $vehicles[$vehicleNumber]["start_meter"] = (int) $meterReading->meter_reading;
//            }
//            if ((int) $meterReading->meter_reading > $vehicles[$vehicleNumber]["end_meter"]) {
//                $vehicles[$vehicleNumber]["end_meter"] = (int) $meterReading->meter_reading;
//            }
//        }
//
//        //Get end and start meters to use for distance calculations
//        $limit = 3;
//        foreach ($vehicles as $key => $vehicle) {
//            if (!isset($vehicle['start_meter'])) {
//                $vehicle['start_meter'] = 0;
//            }
//            if (!isset($vehicle['end_meter'])) {
//                $vehicle['end_meter'] = 0;
//            }
//
//            $endMeterReadings = MeterReading::byVehicleLessThanDate($vehicle['id'], $endDate, $limit);
//            $endMeter = 0;
//            foreach ($endMeterReadings as $key => $meterReading) {
//                $currentMeterReading = (int) $meterReading->meter_reading;
//                if ($currentMeterReading > $endMeter) {
//                    $endMeter = $currentMeterReading;
//                }
//            }
//            //Always overwrite the vehicle end meter with the greater of the last x reading in the date range to account for bad data entry issues
//            $vehicle["end_meter"] = $endMeter;
//
//            $startMeterReadings = MeterReading::byVehicleLessThanDate($vehicle['id'], $startDate, $limit);
//            $startMeter = 0;
//            foreach ($startMeterReadings as $key => $meterReading) {
//                $currentMeterReading = (int) $meterReading->meter_reading;
//                if ($currentMeterReading > $startMeter) {
//                    $startMeter = $currentMeterReading;
//                }
//            }
//            //only replace the vehicle start meter if we found an earlier reading
//            if ($startMeter > 0) {
//                $vehicle["start_meter"] = $startMeter;
//            }
//
//            $distance = $vehicle['end_meter'] - $vehicle['start_meter'];
//            if ($distance < 1) {
//                $distance = 0;
//            }
//            $vehicle["distance"] = $distance;
//            $vehicles[$key] = $vehicle;
//        }
//
//        // Now loop through each vehicle and sum by $unitType
//        foreach ($vehicles as $key => $vehicle) {
//            //now initialize the distance, start_meter, end_meter if they haven't been set yet
//            $unitKey = $vehicle['unit_key'];
//            if (!isset($data[$unitKey]["distance"])) {
//                $data[$unitKey]["distance"] = 0;
//            }
//            if ($unitType == 'vehicle') {
//                if (!isset($data[$unitKey]["start_meter"])) {
//                    $data[$unitKey]["start_meter"] = $vehicle["start_meter"];
//                }
//                if (!isset($data[$unitKey]["end_meter"])) {
//                    $data[$unitKey]["end_meter"] = $vehicle["end_meter"];
//                }
//            }
//            else {
//                if (!isset($data[$unitKey]["start_meter"])) {
//                    $data[$unitKey]["start_meter"] = null;
//                }
//                if (!isset($data[$unitKey]["end_meter"])) {
//                    $data[$unitKey]["end_meter"] = null;
//                }
//            }
//            //now add distance by unitType
//            $data[$unitKey]["distance"] += $vehicle["distance"];
//        }

        //Sum Repair Order expenses per month
        $repairOrders = RepairOrder::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId);
        foreach ($repairOrders as $key => $repairOrder) {
            if ($repairOrder->vehicle === null) {
                //TODO: Tmp solution because the cascade delete on deleting a vehicle apparently didn't work, need to skip if vehicle was deleted.
                continue;
            }
            if ($skipContributors && $repairOrder['system_vehicle_type_name'] == 'Contributor') {
                continue;
            }
            $unitKey = $this->getUnitKey($unitType, $repairOrder);
            $this->addUnitDetails($data, $unitKey, $unitType, $repairOrder);

            //initialize the total and month columns if they haven't been set yet
            if (!isset($data[$unitKey]["total_cost"])) {
                $data[$unitKey]["total_cost"] = 0;
                foreach ($period as $dt) {
                    $monthKey = $dt->format($monthKeyFormat);
                    $data[$unitKey][$monthKey] = 0;
                }
            }

            //now calculate month totals, and grand total per unit
            $data[$unitKey]["total_cost"] += (float) $repairOrder->total_price;
            $tROCompletedDate = (new DateTime($repairOrder->completed_date));
            $monthKey = $tROCompletedDate->format($monthKeyFormat);
            $data[$unitKey][$monthKey] += (float) $repairOrder->total_price;
        }

        //Sum fuel per month
        $fuelings = Fueling::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId);
        foreach ($fuelings as $key => $fuelEvent) {
            if ($fuelEvent->vehicle === null) {
                //TODO: Tmp solution because the cascade delete on deleting a vehicle apparently didn't work, need to skip if vehicle was deleted.
                continue;
            }
            if ($skipContributors && $fuelEvent['system_vehicle_type_name'] == 'Contributor') {
                continue;
            }
            $unitKey = $this->getUnitKey($unitType, $fuelEvent);
            $this->addUnitDetails($data, $unitKey, $unitType, $fuelEvent);
            //initialize the total and month columns if they haven't been set yet
            if (!isset($data[$unitKey]["total_cost"])) {
                $data[$unitKey]["total_cost"] = 0;
                foreach ($period as $dt) {
                    $monthKey = $dt->format($monthKeyFormat);
                    $data[$unitKey][$monthKey] = 0;
                }
            }
            //add in fueling expenses here
            $tFuelDate = (new DateTime($fuelEvent->fueling_date));
            $monthKey = $tFuelDate->format($monthKeyFormat);
            $data[$unitKey]["total_cost"] += (float)$fuelEvent->total_price;
            $data[$unitKey][$monthKey] += (float)$fuelEvent->total_price;
        }

        //Setup results
        $results = [];
        $results['data'] = [];
        $results['monthColumns'] = [];

        //Now add distance and calculate cost per mile
        foreach ($data as $key => $row) {
            if (!isset($row['total_cost'])) {
                $row['total_cost'] = 0;
            }
            if (!isset($row['distance'])) {
                $row['distance'] = 0;
            }

            $row['cpm_fuel'] = $row['total_cost'] / ($row['distance'] > 0 ? $row['distance'] : 1);
            array_push($results['data'], $row);
        }

        //Add in dyamical columns
        foreach ($period as $dt) {
            $monthKey = $dt->format($monthKeyFormat);
            array_push($results['monthColumns'], $monthKey);
        }

        return $results;
    }

    //identify report unit key (there will be one row per unique key): branch, fleet, or vehicle
    private static function getUnitKey(string $unitType, $unitObject) {
        $unitKey = null;
        if ($unitType == 'branch') {
            $unitKey = $unitObject->branch_name;
        }
        elseif ($unitType == 'fleet') {
            $unitKey = $unitObject->fleet_name;
        }
        else {
            $unitKey = $unitObject->vehicle_number;
        }
        return $unitKey;
    }

    //add unit information to a fow
    private static function addUnitDetails(&$data, $unitKey, $unitType, $unitObject) {
        if (!isset($data[$unitKey]["unit"])) {
            $data[$unitKey]["unit"] = $unitKey;
        }
        if ($unitType == 'branch') {
            if (!isset($data[$unitKey]["client_name"])) {
                $data[$unitKey]["client_name"] = $unitObject->client_name;
            }
            if (!isset($data[$unitKey]["branch_name"])) {
                $data[$unitKey]["branch_name"] = $unitObject->branch_name;
            }
            if (!isset($data[$unitKey]["client_id"])) {
                $data[$unitKey]["client_id"] = $unitObject->client_id;
            }
            if (!isset($data[$unitKey]["branch_id"])) {
                $data[$unitKey]["branch_id"] = $unitObject->branch_id;
            }
        }
        elseif($unitType == 'fleet') {
            if (!isset($data[$unitKey]["client_name"])) {
                $data[$unitKey]["client_name"] = $unitObject->client_name;
            }
            if (!isset($data[$unitKey]["branch_name"])) {
                $data[$unitKey]["branch_name"] = $unitObject->branch_name;
            }
            if (!isset($data[$unitKey]["fleet_name"])) {
                $data[$unitKey]["fleet_name"] = $unitObject->fleet_name;
            }
            if (!isset($data[$unitKey]["client_id"])) {
                $data[$unitKey]["client_id"] = $unitObject->client_id;
            }
            if (!isset($data[$unitKey]["branch_id"])) {
                $data[$unitKey]["branch_id"] = $unitObject->branch_id;
            }
            if (!isset($data[$unitKey]["fleet_id"])) {
                $data[$unitKey]["fleet_id"] = $unitObject->fleet_id;
            }
        }
        else {
            if (!isset($data[$unitKey]["client_name"])) {
                $data[$unitKey]["client_name"] = $unitObject->client_name;
            }
            if (!isset($data[$unitKey]["branch_name"])) {
                $data[$unitKey]["branch_name"] = $unitObject->branch_name;
            }
            if (!isset($data[$unitKey]["fleet_name"])) {
                $data[$unitKey]["fleet_name"] = $unitObject->fleet_name;
            }
            if (!isset($data[$unitKey]["vehicle_number"])) {
                $data[$unitKey]["vehicle_number"] = $unitObject->vehicle_number;
            }
            if (!isset($data[$unitKey]["client_id"])) {
                $data[$unitKey]["client_id"] = $unitObject->client_id;
            }
            if (!isset($data[$unitKey]["branch_id"])) {
                $data[$unitKey]["branch_id"] = $unitObject->branch_id;
            }
            if (!isset($data[$unitKey]["fleet_id"])) {
                $data[$unitKey]["fleet_id"] = $unitObject->fleet_id;
            }
            if (!isset($data[$unitKey]["vehicle_id"])) {
                $data[$unitKey]["vehicle_id"] = $unitObject->vehicle_id;
            }
        }
        //return $data;  $data to be passed by reference to optimize performance
    }


}
