<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $tenant = \App\Models\Tenant::create([
            'name' => 'Dev Testing Tenant',
            'abbrv' => 'DTT',
            'ro_number' => 10000,
        ]);
        $user = \App\Models\User::create([
            'name' => 'Scott Wallace Dev',
            'tenant_id' => $tenant->id,
            'email' => 'scott@tranzitfleet.com',
            'password' => bcrypt('new3password8'),
        ]);
        $user = \App\Models\User::create([
            'name' => 'TranzIT Admin',
            'tenant_id' => $tenant->id,
            'email' => 'admin@tranzitfleet.com',
            'password' => bcrypt('password8'),
        ]);
        $system_vehicle_type_h = \App\Models\SystemVehicleType::create([
            'name' => 'Engine Heavy Duty',
            'desc' => 'Heavy duty vehicles containing an engine',
        ]);
        $system_vehicle_type = \App\Models\SystemVehicleType::create([
            'name' => 'Engine Medium Duty',
            'desc' => 'Heavy duty vehicles containing an engine',
        ]);
        $system_vehicle_type = \App\Models\SystemVehicleType::create([
            'name' => 'Engine Light Duty',
            'desc' => 'Heavy duty vehicles containing an engine',
        ]);
        $system_vehicle_type_c = \App\Models\SystemVehicleType::create([
            'name' => 'Contributor',
            'desc' => 'Power Unit, no engine',
        ]);
        $system_meter_type_m = \App\Models\SystemMeterType::create([
            'name' => 'Miles',
            'desc' => 'Miles',
        ]);
        $system_meter_type = \App\Models\SystemMeterType::create([
            'name' => 'Meters',
            'desc' => 'Meters',
        ]);
        $system_meter_type = \App\Models\SystemMeterType::create([
            'name' => 'Hours',
            'desc' => 'Hours',
        ]);
        $pm_due_type = \App\Models\SystemPMDueType::create([
            'name' => 'Days',
            'desc' => 'Due on length of days only',
        ]);
        $pm_due_type = \App\Models\SystemPMDueType::create([
            'name' => 'Interval',
            'desc' => 'Due on length of interval only',
        ]);
        $pm_due_type = \App\Models\SystemPMDueType::create([
            'name' => 'Days or Interval',
            'desc' => 'Due on length of days or length interval whichever comes first',
        ]);
        $repair_order_status_n = \App\Models\RepairOrderStatus::create([
            'name' => 'New',
            'desc' => 'new',
            'tenant_id' => $tenant->id,
        ]);
        $repair_order_status = \App\Models\RepairOrderStatus::create([
            'name' => 'Approved',
            'desc' => 'approved',
            'tenant_id' => $tenant->id,
        ]);
        $repair_order_status_t = \App\Models\RepairOrderStatus::create([
            'name' => 'In Process',
            'desc' => 'currently in process of being worked on',
            'tenant_id' => $tenant->id,
        ]);
        $repair_order_status = \App\Models\RepairOrderStatus::create([
            'name' => 'Closed',
            'desc' => 'repair order is closed',
            'tenant_id' => $tenant->id,
        ]);
        $repair_order_type = \App\Models\RepairOrderType::create([
            'name' => 'Wet Full Service',
            'code' => 'Wet',
            'desc' => 'wet full service',
            'tenant_id' => $tenant->id,
        ]);
        $repair_order_type = \App\Models\RepairOrderType::create([
            'name' => 'Dry Partial Service',
            'code' => 'Dry',
            'desc' => 'dry partial service',
            'tenant_id' => $tenant->id,
        ]);
        $repair_order_type_t = \App\Models\RepairOrderType::create([
            'name' => 'Transmission Service',
            'code' => 'Trans Srv',
            'desc' => 'transmission service',
            'tenant_id' => $tenant->id,
        ]);
        $repair_order_type = \App\Models\RepairOrderType::create([
            'name' => 'DOT Inspection',
            'code' => 'DOT',
            'desc' => 'DOT Inspection',
            'tenant_id' => $tenant->id,
        ]);
        $line_item_type1 = \App\Models\LineItemType::create([
            'name' => 'Labor',
            'code' => 'Labor',
            'desc' => 'Labor',
            'tenant_id' => $tenant->id,
        ]);
        $line_item_type2 = \App\Models\LineItemType::create([
            'name' => 'Parts',
            'code' => 'Parts',
            'desc' => 'Part',
            'tenant_id' => $tenant->id,
        ]);
        $line_item_type3 = \App\Models\LineItemType::create([
            'name' => 'Tires',
            'code' => 'Tires',
            'desc' => 'Tires',
            'tenant_id' => $tenant->id,
        ]);
        $line_item_type4 = \App\Models\LineItemType::create([
            'name' => 'Tax',
            'code' => 'Tax',
            'desc' => 'Tax',
            'tenant_id' => $tenant->id,
        ]);
        $line_item_category1 = \App\Models\LineItemCategory::create([
            'name' => 'Tax',
            'code' => 'Tax',
            'desc' => 'Tax',
            'line_item_type_id' => $line_item_type4->id,
            'tenant_id' => $tenant->id,
        ]);
        $line_item_category2 = \App\Models\LineItemCategory::create([
            'name' => 'FET',
            'code' => 'FET',
            'desc' => 'FET',
            'line_item_type_id' => $line_item_type4->id,
            'tenant_id' => $tenant->id,
        ]);
        $line_item_category3 = \App\Models\LineItemCategory::create([
            'name' => 'Tire',
            'code' => 'Tire',
            'desc' => 'Tire',
            'line_item_type_id' => $line_item_type3->id,
            'tenant_id' => $tenant->id,
        ]);
        $line_item_category4 = \App\Models\LineItemCategory::create([
            'name' => 'Alternator',
            'code' => 'Alternator',
            'desc' => 'Alternator',
            'line_item_type_id' => $line_item_type2->id,
            'tenant_id' => $tenant->id,
        ]);
        $line_item_category4 = \App\Models\LineItemCategory::create([
            'name' => 'Axle',
            'code' => 'Axle',
            'desc' => 'Axle',
            'line_item_type_id' => $line_item_type2->id,
            'tenant_id' => $tenant->id,
        ]);
        $line_item_category5 = \App\Models\LineItemCategory::create([
            'name' => 'Tire Balance',
            'code' => 'Tire Balance',
            'desc' => 'Tire Balance',
            'line_item_type_id' => $line_item_type1->id,
            'tenant_id' => $tenant->id,
        ]);
        $line_item_category5 = \App\Models\LineItemCategory::create([
            'name' => 'Tow Service',
            'code' => 'Tow Service',
            'desc' => 'Tow Service',
            'line_item_type_id' => $line_item_type1->id,
            'tenant_id' => $tenant->id,
        ]);
        $fuel_type1 = \App\Models\FuelType::create([
            'name' => 'Diesel',
            'desc' => 'Diesel',
            'tenant_id' => $tenant->id,
        ]);
        $fuel_type = \App\Models\FuelType::create([
            'name' => 'Unleaded - Regular (87)',
            'desc' => 'Unleaded - Regular (87)',
            'tenant_id' => $tenant->id,
        ]);
        $fuel_type = \App\Models\FuelType::create([
            'name' => 'Unleaded - Medium (89)',
            'desc' => 'Unleaded - Medium (89)',
            'tenant_id' => $tenant->id,
        ]);
        $fuel_type2 = \App\Models\FuelType::create([
            'name' => 'Unleaded - Premium (91)',
            'desc' => 'Unleaded - Premium (91)',
            'tenant_id' => $tenant->id,
        ]);
        $fuel_type = \App\Models\FuelType::create([
            'name' => 'Unleaded - Premium (93)',
            'desc' => 'Unleaded - Premium (93)',
            'tenant_id' => $tenant->id,
        ]);
        $fuel_type = \App\Models\FuelType::create([
            'name' => 'Electric',
            'desc' => 'Electric',
            'tenant_id' => $tenant->id,
        ]);
        $fuel_unit_type1 = \App\Models\FuelUnitType::create([
            'name' => 'Gallons',
            'desc' => 'Gallons',
            'tenant_id' => $tenant->id,
        ]);
        $fuel_unit_type = \App\Models\FuelUnitType::create([
            'name' => 'Liters',
            'desc' => 'Liters',
            'tenant_id' => $tenant->id,
        ]);
        $fuel_unit_type = \App\Models\FuelUnitType::create([
            'name' => 'Hours',
            'desc' => 'Hours charged',
            'tenant_id' => $tenant->id,
        ]);
        $vehicle_type_1 = \App\Models\VehicleType::create([
            'name' => 'Semi Truck',
            'tenant_id' => $tenant->id,
            'desc' => 'Semi Truck description',
            'system_vehicle_type_id' => $system_vehicle_type_h->id,
        ]);
        $vehicle_type_2 = \App\Models\VehicleType::create([
            'name' => 'Trailer',
            'tenant_id' => $tenant->id,
            'desc' => 'Trailer description',
            'system_vehicle_type_id' => $system_vehicle_type_c->id,
        ]);
        $pm_templates = \App\Models\PreventiveMaintenanceTemplate::factory(5)->create([
            'tenant_id' => $tenant->id,
            'repair_order_type_id' => $repair_order_type_t->id,
            'system_meter_type_id' => $system_meter_type_m->id,
            'system_p_m_due_type_id' => $pm_due_type->id,
        ]);

        $engine_manufacturers = \App\Models\EngineManufacturer::factory(5)->create([
            'tenant_id' => $tenant->id
        ]);
        $engine_manufacturers1 = $engine_manufacturers[0];
        $engine_manufacturers2 = $engine_manufacturers[1];
        $vendor = \App\Models\Vendor::factory(5)->create([
            'tenant_id' => $tenant->id
        ]);
        $clients = \App\Models\Client::factory(5)->create([
            'tenant_id' => $tenant->id
        ]);
        $client = $clients[0];
        $branches = \App\Models\Branch::factory(5)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
        ]);
        $branch = $branches[0];
        $fleets = \App\Models\Fleet::factory(5)->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
        ]);
        $fleet1 = $fleets[0];
        $fleet2 = $fleets[1];
        $vehicles1 = \App\Models\Vehicle::factory(5)->create([
            'tenant_id' => $tenant->id,
            'fleet_id' => $fleet1->id,
            'vehicle_type_id' => $vehicle_type_1->id,
            'engine_manufacturer_id' => $engine_manufacturers1->id,
        ]);
        $vehicles2 = \App\Models\Vehicle::factory(5)->create([
            'tenant_id' => $tenant->id,
            'fleet_id' => $fleet2->id,
            'vehicle_type_id' => $vehicle_type_2->id,
            'engine_manufacturer_id' => $engine_manufacturers2->id,
        ]);
        $warranties1 = \App\Models\Warranty::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles1[0],
        ]);
        $warranties2 = \App\Models\Warranty::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles1[1],
        ]);
        $warranties1 = \App\Models\Warranty::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles2[0],
        ]);
        $warranties2 = \App\Models\Warranty::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles2[1],
        ]);
        $meterReading1 = \App\Models\MeterReading::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles1[0],
        ]);
        $meterReading2 = \App\Models\MeterReading::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles1[1],
        ]);
        $meterReading1 = \App\Models\MeterReading::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles2[0],
        ]);
        $meterReading2 = \App\Models\MeterReading::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles2[1],
        ]);
        $fueling1 = \App\Models\Fueling::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles1[0],
            'vendor_id' => $vendor[0]->id,
            'fuel_unit_type_id' => $fuel_unit_type1->id,
            'fuel_type_id' => $fuel_type1->id,
            'meter_reading_id' => $meterReading1[0]->id,
        ]);
        $fueling2 = \App\Models\Fueling::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles1[1],
            'vendor_id' => $vendor[0]->id,
            'fuel_unit_type_id' => $fuel_unit_type1->id,
            'fuel_type_id' => $fuel_type2->id,
            'meter_reading_id' => $meterReading2[0]->id,
        ]);
        $fueling1 = \App\Models\Fueling::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles2[0],
            'vendor_id' => $vendor[1]->id,
            'fuel_unit_type_id' => $fuel_unit_type1->id,
            'fuel_type_id' => $fuel_type1->id,
            'meter_reading_id' => $meterReading1[1]->id,
        ]);
        $fueling2 = \App\Models\Fueling::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles2[1],
            'vendor_id' => $vendor[1]->id,
            'fuel_unit_type_id' => $fuel_unit_type1->id,
            'fuel_type_id' => $fuel_type2->id,
            'meter_reading_id' => $meterReading2[1]->id,
        ]);
        $pms1 = \App\Models\PreventiveMaintenance::factory(5)->create([
            'tenant_id' => $tenant->id,
            'preventive_maintenance_template_id' => $pm_templates[0]->id,
            'repair_order_type_id' => $repair_order_type_t->id,
            'system_meter_type_id' => $system_meter_type_m->id,
            'system_p_m_due_type_id' => $pm_due_type->id,
            'vehicle_id' => $vehicles1[0]->id,
        ]);
        $pms2 = \App\Models\PreventiveMaintenance::factory(5)->create([
            'tenant_id' => $tenant->id,
            'preventive_maintenance_template_id' => $pm_templates[2]->id,
            'repair_order_type_id' => $repair_order_type_t->id,
            'system_meter_type_id' => $system_meter_type_m->id,
            'system_p_m_due_type_id' => $pm_due_type->id,
            'completed_date' => null,
            'vehicle_id' => $vehicles1[0]->id,
        ]);
        $ro1 = \App\Models\RepairOrder::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles1[0]->id,
            'vendor_id' => $vendor[0]->id,
            'repair_order_status_id' => $repair_order_status_t,
            // 'preventive_maintenance_id' => $repair_order_status_t,
        ]);
        $ro2 = \App\Models\RepairOrder::factory(5)->create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicles1[1]->id,
            'vendor_id' => $vendor[1]->id,
            'repair_order_status_id' => $repair_order_status_t,
            // 'preventive_maintenance_id' => $repair_order_status_t,
        ]);
        $lineItems1 = \App\Models\LineItem::factory(5)->create([
            'tenant_id' => $tenant->id,
            'repair_order_id' => $ro1[0]->id,
            'line_item_category_id' => $line_item_category3->id,
        ]);
        $lineItems2 = \App\Models\LineItem::factory(5)->create([
            'tenant_id' => $tenant->id,
            'repair_order_id' => $ro1[1]->id,
            'line_item_category_id' => $line_item_category2->id,
        ]);


        $pms2[3]->repair_order_id = $ro1[0]->id;
        // $ro1[0]->preventive_maintenance_id = $pms2[3]->id;
        $ro1[0]->save();
        $pms2[3]->save();
        $pms2[4]->repair_order_id = $ro1[1]->id;
        // $ro1[1]->preventive_maintenance_id = $pms2[4]->id;
        $ro1[1]->save();
        $pms2[4]->save();
        $pms3 = \App\Models\PreventiveMaintenance::factory(5)->create([
            'tenant_id' => $tenant->id,
            'preventive_maintenance_template_id' => $pm_templates[3]->id,
            'repair_order_type_id' => $repair_order_type_t->id,
            'system_meter_type_id' => $system_meter_type_m->id,
            'system_p_m_due_type_id' => $pm_due_type->id,
            'vehicle_id' => $vehicles2[0]->id,
        ]);


        //Call Other Seeders Here
//        $this->call([
//            RolesAndPermissionsSeeder::class,
//            XXXXSeeder::class,
//        ]);

    }
}
