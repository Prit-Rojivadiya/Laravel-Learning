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

class CostPerMileSummaryController extends Controller
{
    private const PERMISSION_ENTITY = 'cost_per_mile_summary';

    // GET /reports/cost_per_mile_summary
    public function index(Request $request) {
        $data = [];
        $costTotals = [];

        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $startDate = $request->has('startDate') ? $request->get('startDate') : null;
        $endDate = $request->has('endDate') ? ($request->get('endDate')) : null;
        $clientId = $request->has('filterByClient') ? ($request->get('filterByClient')) : null;
        $branchId = $request->has('filterByBranch') ? ($request->get('filterByBranch')) : null;
        $fleetId = $request->has('filterByFleet') ? ($request->get('filterByFleet')) : null;

        $tStartDate = new DateTime($startDate);
        $tEndDate = new DateTime($endDate);
        //$tStartDate = (new DateTime($startDate))->modify('first day of this month');
        //$tEndDate = (new DateTime($endDate))->modify('last day of this month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($tStartDate, $interval, $tEndDate);

        $monthKeyFormat = "M_y";

        //Get Line Items Types for the report
        $lineItemTypesIndex = [];
        $query = LineItemType::query()
            ->select('line_item_types.*');
        $query->where('tenant_id', $user->tenant->id);
        $lItemTypes = $query->get();
        foreach ($lItemTypes as $key => $lineItemType) {
            $lineItemTypesIndex[$lineItemType->id] = strtolower($lineItemType->name);
        }

        //create report data as rows by month, and initial columns
        foreach ($period as $dt) {
            $monthKey = $dt->format($monthKeyFormat);
            $data[$monthKey]["month"] = $dt->format($monthKeyFormat);
            $data[$monthKey]["distance"] = 0;
            foreach ($lineItemTypesIndex as $lineItemType) {
                $data[$monthKey][$lineItemType] = 0;
            }
            $data[$monthKey]["contributors"] = 0;
            $data[$monthKey]["fuel"] = 0;
            $data[$monthKey]["total_cost"] = 0;
            $data[$monthKey]["cpm_fuel"] = 0;
            $data[$monthKey]["cpm_wo_fuel"] = 0;
            $data[$monthKey]["fuel_total_units"] = 0;
            $data[$monthKey]["distance_per_unit"] = 0;
        }

        //create percent expenses
        foreach ($lineItemTypesIndex as $lineItemType) {
            $costTotals[$lineItemType] = 0;
        }
        $costTotals["contributors"] = 0;
        $costTotals["fuel"] = 0;
        $grandTotal = 0;

        //Get all the vehicles that had meter readings in the date range
        $vehicles = [];
        $distinctVehicles = true; //only select list of distinct vehicles rather than the full list of meter readings
        $meterReadings = MeterReading::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId, $distinctVehicles);
        foreach ($meterReadings as $key => $meterReading) {
            if ($meterReading->vehicle === null) {
                //TODO: Tmp solution because the cascade delete on deleting a vehicle apparently didn't work, need to skip if vehicle was deleted.
                continue;
            }
            $vehicleNumber = $meterReading->vehicle_number;
            $vehicles[$vehicleNumber] = $meterReading->vehicle->id;
        }
        foreach ($period as $dt) {
            $monthKey = $dt->format($monthKeyFormat);
            $pStart = $dt;
            $pEnd = clone $dt;
            $pEnd->add($interval);
            foreach ($vehicles as $key => $vehicleId) {
                $vehicleStartMeterForPeriod = 0;
                $vehicleEndMeterForPeriod = 0;
                $distance = 0;
                $startMeterReading = MeterReading::byVehicleLessThanDate($vehicleId, $pStart, 1);
                $endMeterReading = MeterReading::byVehicleLessThanDate($vehicleId, $pEnd, 1);
                if ($startMeterReading->count() > 0) {
                    $vehicleStartMeterForPeriod = (int) $startMeterReading[0]->meter_reading;
                }
                if ($endMeterReading->count() > 0) {
                    $vehicleEndMeterForPeriod = (int) $endMeterReading[0]->meter_reading;
                }
                $distance = $vehicleEndMeterForPeriod - $vehicleStartMeterForPeriod;
                if ($distance > 0) {
                    $data[$monthKey]["distance"] += $distance;
                }
            }
        }

//        //Calc end meter (and start meter) in order to calculate the summed distance traveled per month
//        $vehicles = [];
//        $meterReadings = MeterReading::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId);
//        foreach ($meterReadings as $key => $meterReading) {
//            $tMeterReadingCompletedDate = (new DateTime($meterReading->meter_reading_date));
//            $monthKey = $tMeterReadingCompletedDate->format($monthKeyFormat);
//            $vehicleNumber = $meterReading->vehicle_number;
//
//            //initialize vehicle by month key, and data to track (start, end, distance)
//            if (!isset($vehicles[$vehicleNumber][$monthKey]["id"])) {
//                $vehicles[$vehicleNumber][$monthKey]["id"] = (int) $meterReading->vehicle_id;
//            }
//            if (!isset($vehicles[$vehicleNumber][$monthKey]["monthKey"])) {
//                $vehicles[$vehicleNumber][$monthKey]["monthKey"] = $monthKey;
//            }
//            if (!isset($vehicles[$vehicleNumber][$monthKey]["distance"])) {
//                $vehicles[$vehicleNumber][$monthKey]["distance"] = 0;
//            }
//            if (!isset($vehicles[$vehicleNumber][$monthKey]["start_meter"])) {
//                $vehicles[$vehicleNumber][$monthKey]["start_meter"] = (int) $meterReading->meter_reading;
//            }
//            if (!isset($vehicles[$vehicleNumber][$monthKey]["end_meter"])) {
//                $vehicles[$vehicleNumber][$monthKey]["end_meter"] = (int) $meterReading->meter_reading;
//            }
//            if (!isset($vehicles[$vehicleNumber][$monthKey]["start_meter_date"])) {
//                $vehicles[$vehicleNumber][$monthKey]["start_meter_date"] = $tMeterReadingCompletedDate;
//            }
//
//            //now calculate the highest and lowest meter reading in the date range (regardless of date recorded).
//            if ((int) $meterReading->meter_reading < $vehicles[$vehicleNumber][$monthKey]["start_meter"]) {
//                $vehicles[$vehicleNumber][$monthKey]["start_meter"] = (int) $meterReading->meter_reading;
//                $vehicles[$vehicleNumber][$monthKey]["start_meter_date"] = $tMeterReadingCompletedDate;
//            }
//            if ((int) $meterReading->meter_reading > $vehicles[$vehicleNumber][$monthKey]["end_meter"]) {
//                $vehicles[$vehicleNumber][$monthKey]["end_meter"] = (int) $meterReading->meter_reading;
//            }
//        }
//        //Overwrite the vehicle start meter with the greater of the last x readings prior to the last start meter reading in the date range.
//        $limit = 3;
//        foreach ($vehicles as $key => $vehicle) {
//            $vehicleMonthKeys = array_keys($vehicle);
//            foreach ($vehicleMonthKeys as $key2 => $vehicleByMonthKey) {
//                $vehicleByMonth = $vehicle[$vehicleByMonthKey];
//                $vehicleMonthKey = $vehicleByMonth['monthKey'];
//                $startMeterReadings = MeterReading::byVehicleLessThanDate($vehicleByMonth['id'], $vehicleByMonth['start_meter_date'], $limit);
//                $startMeter = 0;
//                $startMeterDate = null;
//                foreach ($startMeterReadings as $key3 => $meterReading) {
//                    $currentMeterReadingDate = (new DateTime($meterReading->meter_reading_date));
//                    $currentMeterReading = (int)$meterReading->meter_reading;
//                    if ($currentMeterReading > $startMeter) {
//                        $startMeterDate = $currentMeterReadingDate;
//                        $startMeter = $currentMeterReading;
//                    }
//                }
//                //only replace the vehicle start meter if we found an earlier reading
//                if ($startMeter > 0) {
//                    $vehicleByMonth["start_meter_date"] = $startMeterDate;
//                    $vehicleByMonth["start_meter"] = $startMeter;
//                }
//
//                //Now sum the distance in the summary report
//                $vehicleByMonth["distance"] = $vehicleByMonth["end_meter"] - $vehicleByMonth["start_meter"];
//                if ($vehicleByMonth["distance"] > 0) {
//                    $data[$vehicleMonthKey]["distance"] += $vehicleByMonth["distance"];
//                }
//            }
//        }

        //Sum Repair Order expenses per month
        $repairOrders = RepairOrder::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId);
        foreach ($repairOrders as $key => $repairOrder) {
            if ($repairOrder->vehicle === null) {
                //TODO: Tmp solution because the cascade delete on deleting a vehicle apparently didn't work, need to skip if vehicle was deleted.
                continue;
            }
            $repairOrder->total_price = (float)$repairOrder->total_price;
            $tROCompletedDate = (new DateTime($repairOrder->completed_date));
            $monthKey = $tROCompletedDate->format($monthKeyFormat);
            $data[$monthKey]["total_cost"] = $data[$monthKey]["total_cost"] + $repairOrder->total_price;
            $grandTotal = $grandTotal + $repairOrder->total_price;

            if ($repairOrder['system_vehicle_type_name'] == 'Contributor') {
                $data[$monthKey]["contributors"] = $data[$monthKey]["contributors"] + $repairOrder->total_price;
                $costTotals["contributors"] = $costTotals["contributors"] + $repairOrder->total_price;
            }
            else {
                $repairOrder = RepairOrder::addLineItemTypeSums($repairOrder, $lineItemTypesIndex);
                foreach ($lineItemTypesIndex as $lineItemType) {
                    $data[$monthKey][$lineItemType] = $data[$monthKey][$lineItemType] + $repairOrder[$lineItemType];
                    $costTotals[$lineItemType] = $costTotals[$lineItemType] + $repairOrder[$lineItemType];
                }
            }
        }

        //Sum fuel per month
        $fuelings = Fueling::byDateRange($user->tenant->id, $startDate, $endDate, $clientId, $branchId, $fleetId);
        foreach ($fuelings as $key => $fuelEvent) {
            if ($fuelEvent->vehicle === null) {
                //TODO: Tmp solution because the cascade delete on deleting a vehicle apparently didn't work, need to skip if vehicle was deleted.
                continue;
            }
            $tFuelDate = (new DateTime($fuelEvent->fueling_date));
            $monthKey = $tFuelDate->format($monthKeyFormat);
            $data[$monthKey]["fuel_total_units"] = $data[$monthKey]["fuel_total_units"] + (float)$fuelEvent->total_units;
            $data[$monthKey]["fuel"] = $data[$monthKey]["fuel"] + (float)$fuelEvent->total_price;
            $data[$monthKey]["total_cost"] = $data[$monthKey]["total_cost"] + (float)$fuelEvent->total_price;
            $grandTotal = $grandTotal + (float)$fuelEvent->total_price;
            $costTotals["fuel"] = $costTotals["fuel"] + (float)$fuelEvent->total_price;
        }

        $results = [];
        $results['data'] = [];
        $results['percentageCosts'] = [];
        $results['summaryTypes'] = $lineItemTypesIndex;

        foreach ($data as $key => $row) {
            $row['cpm_fuel'] = $row['total_cost'] / ($row['distance'] > 0 ? $row['distance'] : 1);
            $row['cpm_wo_fuel'] = ($row['total_cost'] - $row['fuel']) / ($row['distance'] > 0 ? $row['distance'] : 1);
            $row['distance_per_unit'] = $row['distance'] / ($row['fuel_total_units'] > 0 ? $row['fuel_total_units'] : 1);
            array_push($results['data'], $row);
        }

        foreach ($lineItemTypesIndex as $lineItemType) {
            $row = [];
            $row['category'] = $lineItemType;
            $row['value'] = $costTotals[$lineItemType] / ($grandTotal > 0 ? $grandTotal : 1);
            array_push($results['percentageCosts'], $row);
        }
        $row = [];
        $row['category'] = 'contributors';
        $row['value'] = ($costTotals['contributors'] / ($grandTotal > 0 ? $grandTotal : 1)) * 100;
        array_push($results['percentageCosts'], $row);
        $row = [];
        $row['category'] = 'fuel';
        $row['value'] = ($costTotals['fuel'] / ($grandTotal > 0 ? $grandTotal : 1)) * 100;
        array_push($results['percentageCosts'], $row);

        return $results;
    }
}
