<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use function PHPUnit\Framework\throwException;

class RepairOrder extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = ['vehicle_id', 'vendor_id',
        'repair_order_status_id', 'start_date', 'completed_date',
        'meter_reading','meter_reading_id','invoice_number','copy_of_purchase_order',
        'total_price','desc','notes',
        'needs_approval','approval_received_date','ro_number',
        ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    public function repairOrderStatus()
    {
        return $this->belongsTo(RepairOrderStatus::class);
    }

    public function preventiveMaintenances()
    {
        return $this->hasMany(PreventiveMaintenance::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
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
        $meterReading->meter_reading_date = $this->start_date;
        $meterReading->source = 'Repair Order';
        $meterReading->source_id = $this->id;
        $meterReading->save();
        return $meterReading;
    }

    public static function index($tenantId, $roNumber = null, $invoiceNumber = null, $clientId = null, $branchId = null, $fleetId = null, $vehicleId = null, $desc = null, $repairOrderStatusId = null, $paginateOptions = null, $fleetPermissionsFilter = null)
    {
        $query = RepairOrder::query()
            ->select('repair_orders.*', 'vehicles.vehicle_number as vehicle_number',
                'fleets.name as fleet_name','branches.name as branch_name','clients.name as client_name',
                'vendors.name as vendor_name', 'repair_order_statuses.name as ros_name',
                'fleets.id as fleet_id','branches.id as branch_id','clients.id as client_id', 'vendors.id as vendor_id')
            ->distinct()
            ->join('vehicles', 'repair_orders.vehicle_id', '=', 'vehicles.id')
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
            ->join('branches', 'fleets.branch_id', '=', 'branches.id')
            ->join('clients', 'branches.client_id', '=', 'clients.id')
            ->join('vendors', 'repair_orders.vendor_id', '=', 'vendors.id')
            ->join('repair_order_statuses', 'repair_orders.repair_order_status_id', '=', 'repair_order_statuses.id');
        $query->with(['vehicle', 'vendor', 'repairOrderStatus']);
        $query->where('repair_orders.tenant_id', $tenantId);
        if ($desc) {
            $query->where(function ($q) use ($desc) {
                $q->whereRaw("LOWER(repair_orders.desc) LIKE ?", '%' . $desc . '%');
                if ((int) $desc !== 0) {
                    $q->orWhereRaw("repair_orders.id = ?", $desc);
                }
            });
        }
        if ($roNumber) {
            $query->where('repair_orders.ro_number', $roNumber);
        }
        if ($invoiceNumber) {
            $query->where('repair_orders.invoice_number', $invoiceNumber);
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
            $query->where('repair_orders.vehicle_id', $vehicleId);
        }
        if ($repairOrderStatusId) {
            $query->where('repair_orders.repair_order_status_id', $repairOrderStatusId);
        }
        if ($fleetPermissionsFilter) {
            $query->whereIn('fleets.id', $fleetPermissionsFilter);
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
            }
            else if ($sort == 'vendor.name') {
                $query->orderBy('vendor_name', $sortDir);
            }
            else if ($sort == 'repair_order_status.name') {
                $query->orderBy('ros_name', $sortDir);
            }
            else {
                $query->orderBy('repair_orders.' . $sort, $sortDir);
            }
        }
        else {
            $query->orderBy('updated_at','desc');
        }
        return $paginate ? $query->paginate($itemsPerPage) : $query->get();
    }

    public static function byDateRange($tenantId, $startDate, $endDate, $clientId = null, $branchId = null, $fleetId = null) {
        if (!isset($tenantId)) {
            throw new Exception("Missing tenant in RepairOrder.byDateRange()");
        }
        $query = RepairOrder::query()
            ->select('repair_orders.*','vehicles.vehicle_number as vehicle_number', 'vehicles.vin as vehicle_vin',
                'vendors.name as vendor_name', 'repair_order_statuses.name as ros_name', 'system_vehicle_types.name as system_vehicle_type_name',
                'fleets.name as fleet_name','branches.name as branch_name','clients.name as client_name',
                'fleets.id as fleet_id','branches.id as branch_id','clients.id as client_id', 'vendors.id as vendor_id')
            ->distinct()
            ->join('vendors', 'repair_orders.vendor_id', '=', 'vendors.id')
            ->join('vehicles', 'repair_orders.vehicle_id', '=', 'vehicles.id')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->join('system_vehicle_types', 'vehicle_types.system_vehicle_type_id', '=', 'system_vehicle_types.id')
            ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
            ->join('branches', 'fleets.branch_id', '=', 'branches.id')
            ->join('clients', 'branches.client_id', '=', 'clients.id')
            ->join('repair_order_statuses', 'repair_orders.repair_order_status_id', '=', 'repair_order_statuses.id');
        $query->with('lineItems');
        $query->with('lineItems.lineItemCategory');
        $query->with('vehicle');
        $query->where('repair_orders.tenant_id', $tenantId);
        $query->whereBetween('repair_orders.completed_date', [$startDate, $endDate]);
        if ($clientId) {
            $query->where('clients.id', $clientId);
        }
        if ($branchId) {
            $query->where('branches.id', $branchId);
        }
        if ($fleetId) {
            $query->where('fleets.id', $fleetId);
        }
        $repairOrders = $query->get();
        return $repairOrders;
    }

    public function calcTotalPrice()
    {
        $newTotalPrice = 0;
        foreach ($this->lineItems as $lineItem) {
            $newTotalPrice = $newTotalPrice + $lineItem->total_price;
        }
        $this->total_price = $newTotalPrice;
        $this->save();
    }

    public static function addLineItemTypeSums($repairOrder, $lineItemTypesIndex) {
        $lineItemSummaryByType = [];
        foreach ($repairOrder->lineItems as $key => $lineItem) {
            $lineItemTypeName = $lineItemTypesIndex[$lineItem->lineItemCategory->line_item_type_id];
            $lineItemSummaryByType[$lineItemTypeName] = array_key_exists($lineItemTypeName,$lineItemSummaryByType) ? ($lineItemSummaryByType[$lineItemTypeName] + (float) $lineItem->total_price) : (float) $lineItem->total_price;
        }
        foreach ($lineItemSummaryByType as $name => $sum) {
            $repairOrder[$name] = $sum;
        }
        return $repairOrder;
    }

    public function closeLinkedPMs()
    {
        foreach ($this->preventiveMaintenances as $key => $pm) {
            $tPM = PreventiveMaintenance::find($pm->id);
            if ($tPM) {
                $tPM->closePM($this->completed_date, $this->meter_reading);
            }
        }
    }

}
