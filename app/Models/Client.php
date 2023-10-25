<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','abbrv','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100, $modelPermissionsFilter = null)
    {
        $query = Client::query()
            ->select('clients.*');
        $query->where('tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%')
                    ->orWhereRaw("LOWER(abbrv) LIKE ?", '%' . $name . '%')
                ;
                if ((int) $name !== 0) {
                    $q->orWhereRaw("clients.id = ?", $name);
                }
            });
        }
        if ($modelPermissionsFilter) {
            $query->whereIn('id', $modelPermissionsFilter);
        }

        if ($sort) {
            $query->orderBy('clients.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }

    public function movetotenant($newtenantid = null)
    {
        try {
            $newtenantid = -1;  #HARD CODED FOR NOW, SET TO -1, change to desired new tenant id.  Just for developer to run and step through carefully.
            $targetTenant = Tenant::findOrFail($newtenantid);
            $prevTenant = Tenant::findOrFail($this->tenant->id);
            $updateDB = false; #Hard coded used for testing before running real DB updates.

            Log::alert("Moving client $this->name to new tenant: $targetTenant->name");
            $updateDBStr = $updateDB ? 'true' : 'false';
            Log::alert("UpdateDB set to: $updateDBStr");

            return;  # failsafe to stop execution unless you really want to

            // Update client's users tenant id
            // TODO: Need to implement this.  Users, an UserModelHasPermissions

            // Update client's integrations tenant id
            // TODO: Need to implement this

            // Loop through each of the client's RO vendors, and move them, update tenant id
            $query = RepairOrder::query()
                ->select('repair_orders.vendor_id', 'vendors.name as vendor_name')
                ->where('repair_orders.tenant_id', $prevTenant->id)
                ->join('vehicles', 'repair_orders.vehicle_id', '=', 'vehicles.id')
                ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
                ->join('branches', 'fleets.branch_id', '=', 'branches.id')
                ->join('clients', 'branches.client_id', '=', 'clients.id')
                ->join('vendors', 'repair_orders.vendor_id', '=', 'vendors.id')
                ->where('clients.id', $this->id)
                ->with('vendor')
                ->distinct('repair_orders.vendor_id');
            $vendorsUniqueROs = $query->get();
            $count = 1;
            $total = count($vendorsUniqueROs);
            Log::alert("***********  Found " . $total . " Vendors to transfer **********");
            foreach ($vendorsUniqueROs as $key => $ro) {
                $vendorName = $ro->vendor->name;
                Log::alert("********** Processing $count of $total: $vendorName");
                $newVendor = Vendor::query()->where('tenant_id', $newtenantid)->where('name', $vendorName)->first();
                if (!$newVendor) {
                    $existingVendor = Vendor::query()->where('tenant_id', $prevTenant->id)->where('name', $vendorName)->firstOrFail();
                    $existingVendor->tenant_id = $newtenantid; //move it
                    if ($updateDB) {
                        $existingVendor->save();
                    }
                }
                $count += 1;
            }

            // Loop through each of the client's PM templates and copy them to new tenant id
            $query = PreventiveMaintenance::query()
                ->select('preventive_maintenances.*', 'preventive_maintenance_templates.name as pmt_name')
                ->where('preventive_maintenances.tenant_id', $prevTenant->id)
                ->join('vehicles', 'preventive_maintenances.vehicle_id', '=', 'vehicles.id')
                ->join('fleets', 'vehicles.fleet_id', '=', 'fleets.id')
                ->join('branches', 'fleets.branch_id', '=', 'branches.id')
                ->join('clients', 'branches.client_id', '=', 'clients.id')
                ->join('preventive_maintenance_templates', 'preventive_maintenances.preventive_maintenance_template_id', '=', 'preventive_maintenance_templates.id')
                ->where('clients.id', $this->id)
                ->with(['preventiveMaintenanceTemplate', 'repairOrderType'])
                ->distinct('preventive_maintenances.preventive_maintenance_template_id');
            $pms = $query->get();
            $count = 1;
            $total = count($pms);
            Log::alert("***********  Found " . $total . " PM Templates to transfer **********");
            foreach ($pms as $key => $pm) {
                $pmt = $pm->preventiveMaintenanceTemplate;
                $pmtName = $pmt->name;
                Log::alert("********** Processing $count of $total: $pmtName");
                $query = PreventiveMaintenanceTemplate::query()->where('tenant_id', $newtenantid)->where('name', $pmtName);
                $newPMT = $query->first();
                if (!$newPMT) {
                    $newPMT = new PreventiveMaintenanceTemplate();
                    $newPMT->tenant_id = $newtenantid;
                    $newPMT->name = $pmt->name;
                    $newPMT->recurring = $pmt->recurring;
                    $newPMT->system_meter_type_id = $pmt->system_meter_type_id;
                    $newPMT->system_p_m_due_type_id = $pmt->system_p_m_due_type_id;
                    $newPMT->length_meters = $pmt->length_meters;
                    $newPMT->length_days = $pmt->length_days;
                    $newPMT->desc = $pmt->desc;
                    $repairOrderTypeName = $pmt->repairOrderType->name;
                    $repairOrderType = RepairOrderType::query()->where('tenant_id', $newtenantid)->where('name', $repairOrderTypeName)->firstOrFail();
                    $newPMT->repair_order_type_id = $repairOrderType->id;
                    if ($updateDB) {
                        $newPMT->save();
                    }
                }
                $count += 1;
            }


            // Loop through each of the client's branches, update tenant id
            $count = 1;
            $total = count($this->branches);
            Log::alert("***********  Found " . $total . " Branches to transfer **********");
            foreach ($this->branches as $key => $branch) {
                Log::alert("********** Processing $count of $total: $branch->name");
                $branch->tenant_id = $newtenantid;
                if ($updateDB) {
                    $branch->save();
                }

                // Loop through each branch's fleets, update tenant id
                $countFleet = 1;
                $totalFleet = count($branch->fleets);
                Log::alert("***********  Found " . $totalFleet . " Fleets to transfer **********");
                foreach ($branch->fleets as $key => $fleet) {
                    Log::alert("********** Processing $countFleet of $totalFleet: $fleet->name");
                    $fleet->tenant_id = $newtenantid;
                    if ($updateDB) {
                        $fleet->save();
                    }

                    // Loop through each fleet's vehicles, update tenant id
                    $countVehicle = 1;
                    $totalVehicle = count($fleet->vehicles);
                    Log::alert("***********  Found " . $totalVehicle . " Vehicles to transfer **********");
                    foreach ($fleet->vehicles as $key => $vehicle) {
                        Log::alert("********** Processing $countVehicle of $totalVehicle: $vehicle->vehicle_number");
                        if ($vehicle->engineManufacturer) {
                            $engineMfgName = $vehicle->engineManufacturer->name;
                            $engineMfg = EngineManufacturer::query()->where('tenant_id', $newtenantid)->where('name', $engineMfgName)->firstOrFail();
                        }
                        $vehicleTypeName = $vehicle->vehicleType->name;
                        $vehicleType = VehicleType::query()->where('tenant_id', $newtenantid)->where('name', $vehicleTypeName)->firstOrFail();
                        $vehicle->tenant_id = $newtenantid;
                        $vehicle->vehicle_type_id = $vehicleType->id;
                        if ($vehicle->engineManufacturer) {
                            $vehicle->engine_manufacturer_id = $engineMfg->id;
                        }
                        if ($updateDB) {
                            $vehicle->save();
                        }

                        // Loop through each vehicle's meter reading, change tenant_id
                        $countMeterReading = 1;
                        $totalMeterReading = count($vehicle->meterReadings);
                        Log::alert("***********  Found " . $totalMeterReading . " Meter Readings to transfer **********");
                        foreach ($vehicle->meterReadings as $key => $meterReading) {
                            Log::alert("********** Processing $countMeterReading of $totalMeterReading: $meterReading->vehilce_id:$meterReading->meter_reading_date");
                            $meterReading->tenant_id = $newtenantid;
                            if ($updateDB) {
                                $meterReading->save();
                            }
                            $countMeterReading += 1;
                        }

                        // Loop through each vehicle's fuel events, change tenant_id
                        $countFueling = 1;
                        $totalFueling = count($vehicle->fuelings);
                        Log::alert("***********  Found " . $totalFueling . " Fueling Events to transfer **********");
                        foreach ($vehicle->fuelings as $key => $fueling) {
                            Log::alert("********** Processing $countFueling of $totalFueling: $fueling->fueling_date");
                            if ($fueling->vendor) {
                                $vendorName = $fueling->vendor->name;
                                $vendor = Vendor::query()->where('tenant_id', $newtenantid)->where('name', $vendorName)->firstOrFail();
                            }
                            if ($fueling->fuelType) {
                                $fuelTypeName = $fueling->fuelType->name;
                                $fuelType = FuelType::query()->where('tenant_id', $newtenantid)->where('name', $fuelTypeName)->firstOrFail();
                            }
                            $fuelUnitTypeName = $fueling->fuelUnitType->name;
                            $fuelUnitType = FuelUnitType::query()->where('tenant_id', $newtenantid)->where('name', $fuelUnitTypeName)->firstOrFail();
                            $fueling->tenant_id = $newtenantid;
                            $fueling->fuel_unit_type_id = $fuelUnitType->id;
                            if ($fueling->vendor) {
                                $fueling->vendor_id = $vendor->id;
                            }
                            if ($fueling->fuelType) {
                                $fueling->fuel_type_id = $fuelType->id;
                            }
                            if ($updateDB) {
                                $fueling->save();
                            }
                            $countFueling += 1;
                        }

                        // Loop through each vehicle's PMs, change tenant_id
                        $countPM = 1;
                        $totalPM = count($vehicle->preventiveMaintenances);
                        Log::alert("***********  Found " . $totalPM . " PMs to transfer **********");
                        foreach ($vehicle->preventiveMaintenances as $key => $pm) {
                            $preventiveMaintenanceTemplateName = $pm->preventiveMaintenanceTemplate->name;
                            Log::alert("********** Processing $countPM of $totalPM: $pm->name");
                            $repairOrderTypeName = $pm->repairOrderType->name;
                            $preventiveMaintenanceTemplate = PreventiveMaintenanceTemplate::query()->where('tenant_id', $newtenantid)->where('name', $preventiveMaintenanceTemplateName)->firstOrFail();
                            $repairOrderType = RepairOrderType::query()->where('tenant_id', $newtenantid)->where('name', $repairOrderTypeName)->firstOrFail();
                            $pm->tenant_id = $newtenantid;
                            $pm->preventive_maintenance_template_id = $preventiveMaintenanceTemplate->id;
                            $pm->repair_order_type_id = $repairOrderType->id;
                            if ($updateDB) {
                                $pm->save();
                            }
                        }

                        // Loop through each vehicle's ROs, change tenant_id
                        $countRO = 1;
                        $totalRO = count($vehicle->repairOrders);
                        Log::alert("***********  Found " . $totalRO . " Repair Orders to transfer **********");
                        foreach ($vehicle->repairOrders as $key => $repairOrder) {
                            Log::alert("********** Processing $countRO of $totalRO: $repairOrder->vehilce_id:$repairOrder->desc");
                            if ($repairOrder->vendor) {
                                $vendorName = $repairOrder->vendor->name;
                                $vendor = Vendor::query()->where('tenant_id', $newtenantid)->where('name', $vendorName)->firstOrFail();
                            }
                            $repairOrderStatusName = $repairOrder->repairOrderStatus->name;
                            $repairOrderStatus = RepairOrderStatus::query()->where('tenant_id', $newtenantid)->where('name', $repairOrderStatusName)->firstOrFail();
                            $repairOrder->tenant_id = $newtenantid;
                            $repairOrder->repair_order_status_id = $repairOrderStatus->id;
                            if ($repairOrder->vendor) {
                                $repairOrder->vendor_id = $vendor->id;
                            }
                            if ($updateDB) {
                                $repairOrder->save();
                            }

                            // Loop through each RO's line items, change tenant_id
                            foreach ($repairOrder->lineItems as $key => $lineItem) {
                                $lineItemCategoryName = $lineItem->lineItemCategory->name;
                                $lineItemCategory = LineItemCategory::query()->where('tenant_id', $newtenantid)->where('name', $lineItemCategoryName)->firstOrFail();
                                $lineItem->tenant_id = $newtenantid;
                                $lineItem->line_item_category_id = $lineItemCategory->id;
                                if ($updateDB) {
                                    $lineItem->save();
                                }
                            }
                            $countRO += 1;
                        }

                        // Loop through each vehicle's warranties, change tenant_id
                        $countW = 1;
                        $totalW = count($vehicle->warranties);
                        Log::alert("***********  Found " . $totalW . " Warranties to transfer **********");
                        foreach ($vehicle->warranties as $key => $warranty) {
                            Log::alert("********** Processing $countW of $totalW: $warranty->name");
                            $warranty->tenant_id = $newtenantid;
                            if ($updateDB) {
                                $warranty->save();
                            }
                        }
                        $countVehicle += 1;
                    }
                    $totalFleet += 1;
                }
                $count += 1;
            }

            // Update client's tenant id to new tenant
            $this->tenant_id = $newtenantid;
            if ($updateDB) {
                $this->save();
            }
            Log::alert("Migration $this->name completed");
        }
        catch(Exception $e) {
            $message = $e->getMessage();
            Log::error($message);
            throw new Exception($e);
        }

    }

    public function log($message)
    {
        Log::alert($message);
    }


}
