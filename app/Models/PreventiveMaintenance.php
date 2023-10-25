<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreventiveMaintenance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'preventive_maintenance_template_id', 'repair_order_type_id',
        'recurring', 'system_meter_type_id', 'system_p_m_due_type_id',
        'length_meters', 'length_days',
        'start_date', 'due_date', 'completed_date',
        'start_at_meter', 'due_at_meter', 'completed_at_meter',
        'vehicle_id', 'desc', 'repair_order_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function preventiveMaintenanceTemplate()
    {
        return $this->belongsTo(PreventiveMaintenanceTemplate::class);
    }

    public function repairOrderType()
    {
        return $this->belongsTo(RepairOrderType::class);
    }

    public function systemMeterType()
    {
        return $this->belongsTo(SystemMeterType::class);
    }

    public function systemPMDueType()
    {
        return $this->belongsTo(SystemPMDueType::class, 'system_p_m_due_type_id');
    }

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class, 'repair_order_id');
    }

    public function closePM($completedDate = null, $completedMeter = null, $pmJustClosed = false)
    {
        $pmAlreadyClosed = false;
        if ($this->completed_date && !$pmJustClosed) {
            $pmAlreadyClosed = true;
        }
        $changed = false;
        if ($completedDate) {
            $tCompletedDateOld = new \DateTime($this->completed_date);
            $tCompletedDateNew = new \DateTime($completedDate);
            if ( (!$this->completed_date) || ($tCompletedDateOld->diff($tCompletedDateNew)->format("%a") != 0) ) {
                $changed = true;
                $this->completed_date = $completedDate;
            }
        }
        if ($completedMeter) {
            if ($this->completed_at_meter != $completedMeter) {
                $changed = true;
                $this->completed_at_meter = $completedMeter;
            }
        }
        if ($changed) {
            $this->save();
        }

        //now auto open a new pm if this PM was just closed and was recurring
        if ($this->completed_date && !$pmAlreadyClosed) {
            // If this PM is recurring, then auto create a new PM as this one just closed
            if ($this->recurring) {
                $newPM = new PreventiveMaintenance();
                $newPM->tenant_id = $this->tenant_id;
                $newPM->name = $this->name;
                $newPM->preventive_maintenance_template_id = $this->preventive_maintenance_template_id;
                $newPM->repair_order_type_id = $this->repair_order_type_id;
                $newPM->recurring = $this->recurring;
                $newPM->system_meter_type_id = $this->system_meter_type_id;
                $newPM->system_p_m_due_type_id = $this->system_p_m_due_type_id;
                $newPM->length_meters = $this->length_meters;
                $newPM->length_days = $this->length_days;
                $newPM->start_date = $this->completed_date;
                $newPM->start_at_meter = $this->completed_at_meter;
                $newPM->vehicle_id = $this->vehicle_id;
                $newPM->desc = $this->desc;
                if ($newPM->start_date && $this->length_days) {
                    $dueDate = (new \DateTime($newPM->start_date))->modify("+$this->length_days day");
                    $newPM->due_date = $dueDate;
                }
                if ($newPM->start_at_meter && $this->length_meters) {
                    $newPM->due_at_meter = $newPM->start_at_meter + $this->length_meters;
                }
                $newPM->save();
            }
        }
    }

    #return PreventiveMaintenance::findByName($user->tenant->id, $request->query('filterByClient'), $request->query('filterByBranch'), $request->query('filterByFleet'), $request->query('filterByName'), $request->query('filterByVehicle'), $request->query('filterByStatus'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage);

    public static function index($tenantId, $clientId = null, $branchId = null, $fleetId = null, $vehicleId = null, $name = null, $status = null, $includeMeterReading = false, $pmDueTypeId = null, $paginateOptions = null, $fleetPermissionsFilter = null)
    {
        $query = PreventiveMaintenance::query()
            ->select('preventive_maintenances.*', 'vehicles.vehicle_number as vehicle_number',
                'fleets.name as fleet_name','branches.name as branch_name','clients.name as client_name',
                'fleets.id as fleet_id','branches.id as branch_id','clients.id as client_id')
            ->distinct()
            ->join('vehicles', 'preventive_maintenances.vehicle_id', '=', 'vehicles.id')
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
            ->join('branches', 'fleets.branch_id', '=', 'branches.id')
            ->join('clients', 'branches.client_id', '=', 'clients.id');
        $query->with(['vehicle','preventiveMaintenanceTemplate','repairOrder','repairOrderType','systemMeterType','systemPMDueType']);
        $query->where('preventive_maintenances.tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(preventive_maintenances.name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("preventive_maintenances.id = ?", $name);
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
        if ($vehicleId) {
            $query->where('preventive_maintenances.vehicle_id', $vehicleId);
        }
        if ($fleetPermissionsFilter) {
            $query->whereIn('fleets.id', $fleetPermissionsFilter);
        }
        if ($pmDueTypeId && $pmDueTypeId != 'all') {
            $query->where('preventive_maintenances.system_p_m_due_type_id', $pmDueTypeId);
        }

        $now = (new \DateTime())->setTime(0,0,0,0);
        $nowStr =  $now->format('Y-m-d H:i:s');
        $nowPlus30 = (new \DateTime())->add(new \DateInterval('P30D'));
        $nowPlus30Str = $nowPlus30->format('Y-m-d H:i:s');

        switch ($status) {
            case 'open':
                $query->whereNull('preventive_maintenances.completed_date');
                break;
            case 'completed':
                $query->whereNotNull('preventive_maintenances.completed_date');
                break;
            case 'due-soon':
                $query->whereNull('preventive_maintenances.completed_date');
                //Note: Additional filtering will be done below after getting latest meter reading
                break;
            case 'overdue':
                $query->whereNull('preventive_maintenances.completed_date');
                //->whereNull('repair_order_id');
                //Note: Additional filtering will be done below after getting latest meter reading
                break;
        }

        $paginate = false;
        $sort = null;
        $sortDir = 'desc';
        $page = 1;
        $itemsPerPage = 100;

        if ($paginateOptions) {
            $paginate = true;
            if (isset($paginateOptions['sort'])) {
                $sort = $paginateOptions['sort'];
            }
            if (isset($paginateOptions['sortDir'])) {
                $sortDir = $paginateOptions['sortDir'];
            }
            if (isset($paginateOptions['page'])) {
                $page = $paginateOptions['page'];
            }
            if (isset($paginateOptions['itemsPerPage'])) {
                $itemsPerPage = $paginateOptions['itemsPerPage'];
            }
            if ($sort == 'vehicle.vehicle_number') {
                $query->orderBy('vehicle_number', $sortDir);
            } else {
                $query->orderBy('preventive_maintenances.' . $sort, $sortDir);
            }
        }
        else {
            $query->orderBy('updated_at','desc');
        }

        if (!$paginate && ($includeMeterReading || $pmDueTypeId)) {
            $results = $query->get();
            $overDue = [];
            $dueSoonByDate = [];
            $dueSoonByInterval = [];
            $dueSoonByDateOrInterval = [];
            // Loop through each PM, add calculated fields, and tag by due type
            foreach ($results as $item) {
                //Calculate fields
                if ($item->repair_order_id) {
                    $item->hasRepairOrder = true;
                }
                else {
                    $item->hasRepairOrder = false;
                }
                $item->overdue = false;
                if (!$item->completed_date) {
                    $latestMeterReading = MeterReading::latestByVehicle($item->vehicle_id);
                    if ($latestMeterReading->count() == 1) {
                        $item->latestMeterReading = $latestMeterReading[0]->meter_reading;
                        if ($item->due_at_meter) {
                            $item->remainingMeters = $item->due_at_meter - $item->latestMeterReading;
                        }
                        if ($item->due_date) {
                            $dueDate = new \DateTime($item->due_date);
                            $item->remainingDays = $now->diff($dueDate)->days;
                            if ($dueDate < $now) {
                                $item->remainingDays = $item->remainingDays * -1;
                            }
                        }
                    } else {
                        $item->latestMeterReading = null;
                    }

                    //Calc remaining days and intervace, along with Due Soon and Overdue arrays
                    $daysBoundary = 30;
                    $intervalBoundary = 1000;
                    $pmDueType = SystemPMDueType::find($item->system_p_m_due_type_id);
                    if ($pmDueType) {
                        switch ($pmDueType->name) {
                            case 'Days':
                                if ($item->remainingDays <= $daysBoundary) {
                                    array_push($dueSoonByDate, $item);
                                }
                                if ($item->remainingDays < 0) {
                                    $item->overdue = true;
                                    array_push($overDue, $item);
                                }
                                break;
                            case 'Interval':
                                if ($item->remainingMeters <= $intervalBoundary) {
                                    array_push($dueSoonByInterval, $item);
                                }
                                if ($item->remainingMeters < 0) {
                                    $item->overdue = true;
                                    array_push($overDue, $item);
                                }
                                break;
                            case 'Days or Interval':
                                if ($item->remainingDays <= $daysBoundary || $item->remainingMeters <= $intervalBoundary) {
                                    array_push($dueSoonByDateOrInterval, $item);
                                }
                                if ($item->remainingDays < 0 || $item->remainingMeters < 0) {
                                    $item->overdue = true;
                                    array_push($overDue, $item);
                                }
                                break;
                            default:
                                // Count as Days or Interval
                                if ($item->remainingDays <= $daysBoundary || $item->remainingMeters <= $intervalBoundary) {
                                    array_push($dueSoonByDateOrInterval, $item);
                                }
                                if ($item->remainingDays < 0 || $item->remainingMeters < 0) {
                                    $item->overdue = true;
                                    array_push($overDue, $item);
                                }
                                break;
                        }
                    } else {
                        // Shouldn't happen, but count is as Days or Interval
                        if ($item->remainingDays <= $daysBoundary || $item->remainingMeters <= $intervalBoundary) {
                            array_push($dueSoonByDateOrInterval, $item);
                        }
                        if ($item->remainingDays < 0 || $item->remainingMeters < 0) {
                            $item->overdue = true;
                            array_push($overDue, $item);
                        }
                    }
                }
            }

            if ($status == 'due-soon') {
                $pmDueTypeFilter = SystemPMDueType::find($pmDueTypeId);
                $pmDueTypeFilterName = $pmDueTypeFilter ? $pmDueTypeFilter->name : 'All';
                switch ($pmDueTypeFilterName) {
                    case 'Days':
                        $results = $dueSoonByDate;
                        break;
                    case 'Interval':
                        $results = $dueSoonByInterval;
                        break;
                    case 'Days or Interval':
                        $results = $dueSoonByDateOrInterval;
                        break;
                    default:
                        // PMDueTypeFilter was all or null
                        $results = array_merge($dueSoonByDateOrInterval, $dueSoonByDate, $dueSoonByInterval);
                        break;
                }
            }
            elseif ($status == 'overdue') {
                $results = $overDue;
            }
            return $results;
        }
        else {
            return $paginate ? $query->paginate($itemsPerPage) : $query->get();
        }
    }





}
