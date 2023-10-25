<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => ['api', 'auth:sanctum'],
    'prefix' => 'auth'
], function ($router) {
    Route::post('logout', AuthController::class.'@logout');
    Route::get('user', AuthController::class.'@me');
});

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', AuthController::class.'@login');
    Route::post('refresh', AuthController::class.'@refresh');
    Route::post('token', AuthController::class.'@token');

    Route::post('/password/email', '\App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->middleware([\Spatie\HttpLogger\Middlewares\HttpLogger::class]);
    Route::post('/password/reset', '\App\Http\Controllers\Auth\ResetPasswordController@reset');
});

Route::group([
    'middleware' => ['api', 'auth:sanctum', \Spatie\HttpLogger\Middlewares\HttpLogger::class],
], function ($router) {

    Route::post('/impersonate/{id}', '\App\Http\Controllers\Api\ImpersonateController@store');
    Route::post('/exit-impersonation', '\App\Http\Controllers\Api\ExitImpersonateController@store');

    Route::get('/vendors', '\App\Http\Controllers\Api\VendorController@index');
    Route::post('/vendors', '\App\Http\Controllers\Api\VendorController@store');
    Route::put('/vendors/{id}', '\App\Http\Controllers\Api\VendorController@update');
    Route::get('/vendors/{id}', '\App\Http\Controllers\Api\VendorController@show');
    Route::delete('/vendors/{id}', '\App\Http\Controllers\Api\VendorController@delete');

    Route::get('/clients', '\App\Http\Controllers\Api\ClientController@index');
    Route::post('/clients', '\App\Http\Controllers\Api\ClientController@store');
    Route::put('/clients/{id}', '\App\Http\Controllers\Api\ClientController@update');
    Route::get('/clients/{id}', '\App\Http\Controllers\Api\ClientController@show');
    Route::delete('/clients/{id}', '\App\Http\Controllers\Api\ClientController@delete');
    Route::put('/clients/movetotenant/{id}/{newtenantid}', '\App\Http\Controllers\Api\ClientController@movetotenant');

    Route::get('/branches', '\App\Http\Controllers\Api\BranchController@index');
    Route::post('/branches', '\App\Http\Controllers\Api\BranchController@store');
    Route::put('/branches/{id}', '\App\Http\Controllers\Api\BranchController@update');
    Route::get('/branches/{id}', '\App\Http\Controllers\Api\BranchController@show');
    Route::delete('/branches/{id}', '\App\Http\Controllers\Api\BranchController@delete');

    Route::get('/fleets', '\App\Http\Controllers\Api\FleetController@index');
    Route::post('/fleets', '\App\Http\Controllers\Api\FleetController@store');
    Route::put('/fleets/{id}', '\App\Http\Controllers\Api\FleetController@update');
    Route::get('/fleets/{id}', '\App\Http\Controllers\Api\FleetController@show');
    Route::delete('/fleets/{id}', '\App\Http\Controllers\Api\FleetController@delete');

    Route::get('/vehicles', '\App\Http\Controllers\Api\VehicleController@index');
    Route::post('/vehicles', '\App\Http\Controllers\Api\VehicleController@store');
    Route::put('/vehicles/{id}', '\App\Http\Controllers\Api\VehicleController@update');
    Route::get('/vehicles/{id}', '\App\Http\Controllers\Api\VehicleController@show');
    Route::delete('/vehicles/{id}', '\App\Http\Controllers\Api\VehicleController@delete');

    Route::get('/vehicle_types', '\App\Http\Controllers\Api\VehicleTypeController@index');
    Route::post('/vehicle_types', '\App\Http\Controllers\Api\VehicleTypeController@store');
    Route::put('/vehicle_types/{id}', '\App\Http\Controllers\Api\VehicleTypeController@update');
    Route::get('/vehicle_types/{id}', '\App\Http\Controllers\Api\VehicleTypeController@show');
    Route::delete('/vehicle_types/{id}', '\App\Http\Controllers\Api\VehicleTypeController@delete');

    Route::get('/vehicle_meter_reading/{id}', '\App\Http\Controllers\Api\VehicleMeterReadingController@show');

    Route::post('/file_imports', '\App\Http\Controllers\Api\FileImportController@store');

    Route::get('/engine_manufacturers', '\App\Http\Controllers\Api\EngineManufacturerController@index');
    Route::post('/engine_manufacturers', '\App\Http\Controllers\Api\EngineManufacturerController@store');
    Route::put('/engine_manufacturers/{id}', '\App\Http\Controllers\Api\EngineManufacturerController@update');
    Route::get('/engine_manufacturers/{id}', '\App\Http\Controllers\Api\EngineManufacturerController@show');
    Route::delete('/engine_manufacturers/{id}', '\App\Http\Controllers\Api\EngineManufacturerController@delete');

    Route::get('/warranties', '\App\Http\Controllers\Api\WarrantyController@index');
    Route::post('/warranties', '\App\Http\Controllers\Api\WarrantyController@store');
    Route::put('/warranties/{id}', '\App\Http\Controllers\Api\WarrantyController@update');
    Route::get('/warranties/{id}', '\App\Http\Controllers\Api\WarrantyController@show');
    Route::delete('/warranties/{id}', '\App\Http\Controllers\Api\WarrantyController@delete');

    Route::get('/meter_readings', '\App\Http\Controllers\Api\MeterReadingController@index');
    Route::post('/meter_readings', '\App\Http\Controllers\Api\MeterReadingController@store');
    Route::put('/meter_readings/{id}', '\App\Http\Controllers\Api\MeterReadingController@update');
    Route::get('/meter_readings/{id}', '\App\Http\Controllers\Api\MeterReadingController@show');
    Route::delete('/meter_readings/{id}', '\App\Http\Controllers\Api\MeterReadingController@delete');

    Route::get('/fuelings', '\App\Http\Controllers\Api\FuelingController@index');
    Route::post('/fuelings', '\App\Http\Controllers\Api\FuelingController@store');
    Route::put('/fuelings/{id}', '\App\Http\Controllers\Api\FuelingController@update');
    Route::get('/fuelings/{id}', '\App\Http\Controllers\Api\FuelingController@show');
    Route::delete('/fuelings/{id}', '\App\Http\Controllers\Api\FuelingController@delete');
    Route::post('/fuelings/import', '\App\Http\Controllers\Api\FuelingController@import');
    Route::post('/fuelings/v1/import', '\App\Http\Controllers\Api\FuelingController@importv1');


    Route::get('/system_vehicle_types', '\App\Http\Controllers\Api\SystemVehicleTypeController@index');
    Route::get('/system_meter_types', '\App\Http\Controllers\Api\SystemMeterTypeController@index');
    Route::get('/system_p_m_due_types', '\App\Http\Controllers\Api\SystemPMDueTypeController@index');
    Route::get('/fuel_types', '\App\Http\Controllers\Api\FuelTypeController@index');
    Route::get('/fuel_unit_types', '\App\Http\Controllers\Api\FuelUnitTypeController@index');
    Route::get('/repair_order_statuses', '\App\Http\Controllers\Api\RepairOrderStatusController@index');

    Route::get('/repair_order_types', '\App\Http\Controllers\Api\RepairOrderTypeController@index');
    Route::post('/repair_order_types', '\App\Http\Controllers\Api\RepairOrderTypeController@store');
    Route::put('/repair_order_types/{id}', '\App\Http\Controllers\Api\RepairOrderTypeController@update');
    Route::get('/repair_order_types/{id}', '\App\Http\Controllers\Api\RepairOrderTypeController@show');
    Route::delete('/repair_order_types/{id}', '\App\Http\Controllers\Api\RepairOrderTypeController@delete');

    Route::get('/line_item_types', '\App\Http\Controllers\Api\LineItemTypeController@index');
    Route::post('/line_item_types', '\App\Http\Controllers\Api\LineItemTypeController@store');
    Route::put('/line_item_types/{id}', '\App\Http\Controllers\Api\LineItemTypeController@update');
    Route::get('/line_item_types/{id}', '\App\Http\Controllers\Api\LineItemTypeController@show');
    Route::delete('/line_item_types/{id}', '\App\Http\Controllers\Api\LineItemTypeController@delete');

    Route::get('/line_item_categories', '\App\Http\Controllers\Api\LineItemCategoryController@index');
    Route::post('/line_item_categories', '\App\Http\Controllers\Api\LineItemCategoryController@store');
    Route::put('/line_item_categories/{id}', '\App\Http\Controllers\Api\LineItemCategoryController@update');
    Route::get('/line_item_categories/{id}', '\App\Http\Controllers\Api\LineItemCategoryController@show');
    Route::delete('/line_item_categories/{id}', '\App\Http\Controllers\Api\LineItemCategoryController@delete');

    Route::get('/preventive_maintenance_templates', '\App\Http\Controllers\Api\PreventiveMaintenanceTemplateController@index');
    Route::post('/preventive_maintenance_templates', '\App\Http\Controllers\Api\PreventiveMaintenanceTemplateController@store');
    Route::put('/preventive_maintenance_templates/{id}', '\App\Http\Controllers\Api\PreventiveMaintenanceTemplateController@update');
    Route::get('/preventive_maintenance_templates/{id}', '\App\Http\Controllers\Api\PreventiveMaintenanceTemplateController@show');
    Route::delete('/preventive_maintenance_templates/{id}', '\App\Http\Controllers\Api\PreventiveMaintenanceTemplateController@delete');

    Route::get('/preventive_maintenances', '\App\Http\Controllers\Api\PreventiveMaintenanceController@index');
    Route::post('/preventive_maintenances', '\App\Http\Controllers\Api\PreventiveMaintenanceController@store');
    Route::put('/preventive_maintenances/{id}', '\App\Http\Controllers\Api\PreventiveMaintenanceController@update');
    Route::get('/preventive_maintenances/{id}', '\App\Http\Controllers\Api\PreventiveMaintenanceController@show');
    Route::delete('/preventive_maintenances/{id}', '\App\Http\Controllers\Api\PreventiveMaintenanceController@delete');

    Route::get('/repair_orders', '\App\Http\Controllers\Api\RepairOrderController@index');
    Route::post('/repair_orders', '\App\Http\Controllers\Api\RepairOrderController@store');
    Route::put('/repair_orders/{id}', '\App\Http\Controllers\Api\RepairOrderController@update');
    Route::get('/repair_orders/{id}', '\App\Http\Controllers\Api\RepairOrderController@show');
    Route::delete('/repair_orders/{id}', '\App\Http\Controllers\Api\RepairOrderController@delete');
    Route::post('/repair_orders/v1/import', '\App\Http\Controllers\Api\RepairOrderController@importv1');


    Route::get('/line_items', '\App\Http\Controllers\Api\LineItemController@index');
    Route::post('/line_items', '\App\Http\Controllers\Api\LineItemController@store');
    Route::put('/line_items/{id}', '\App\Http\Controllers\Api\LineItemController@update');
    Route::get('/line_items/{id}', '\App\Http\Controllers\Api\LineItemController@show');
    Route::delete('/line_items/{id}', '\App\Http\Controllers\Api\LineItemController@delete');

    Route::get('/reports/invoice_summary', '\App\Http\Controllers\Api\Reports\InvoiceSummaryController@index');
    Route::get('/reports/cost_per_mile_summary', '\App\Http\Controllers\Api\Reports\CostPerMileSummaryController@index');
    Route::get('/reports/cost_per_mile_detailed', '\App\Http\Controllers\Api\Reports\CostPerMileDetailedController@index');
    Route::get('/reports/fuel_tax', '\App\Http\Controllers\Api\Reports\FuelTaxController@index');

    Route::post('/update-password', '\App\Http\Controllers\Auth\UpdatePasswordController@update');
    Route::put('/profile/{id}', '\App\Http\Controllers\Api\ProfileController@update');

    Route::get('/tenants', '\App\Http\Controllers\Api\TenantController@index');
    Route::post('/tenants', '\App\Http\Controllers\Api\TenantController@store');
    Route::put('/tenants/{id}', '\App\Http\Controllers\Api\TenantController@update');
    Route::get('/tenants/{id}', '\App\Http\Controllers\Api\TenantController@show');
    Route::delete('/tenants/{id}', '\App\Http\Controllers\Api\TenantController@delete');
    Route::get('/tenants/name/{id}', '\App\Http\Controllers\Api\TenantController@name');
    Route::put('/tenants/defaults/{id}', '\App\Http\Controllers\Api\TenantController@defaults');

    Route::get('/users', '\App\Http\Controllers\Api\UserController@index');
    Route::get('/users/{id}', '\App\Http\Controllers\Api\UserController@show');
    Route::post('/users', '\App\Http\Controllers\Api\UserController@store');
    Route::put('/users/{id}', '\App\Http\Controllers\Api\UserController@update');
    Route::delete('/users/{id}', '\App\Http\Controllers\Api\UserController@delete');
    Route::get('/users/role/{id}', '\App\Http\Controllers\Api\UserController@role');

    Route::get('/user_model_has_permissions', '\App\Http\Controllers\Api\UserModelHasPermissionsController@index');
    Route::post('/user_model_has_permissions', '\App\Http\Controllers\Api\UserModelHasPermissionsController@store');

    Route::get('/integrations', '\App\Http\Controllers\Api\IntegrationController@index');
    Route::post('/integrations', '\App\Http\Controllers\Api\IntegrationController@store');
    Route::put('/integrations/{id}', '\App\Http\Controllers\Api\IntegrationController@update');
    Route::get('/integrations/{id}', '\App\Http\Controllers\Api\IntegrationController@show');
    Route::delete('/integrations/{id}', '\App\Http\Controllers\Api\IntegrationController@delete');
    Route::post('/integrations/run/{id}', '\App\Http\Controllers\Api\IntegrationController@run');

    Route::get('/integration_runs', '\App\Http\Controllers\Api\IntegrationRunController@index');
    Route::post('/integration_runs', '\App\Http\Controllers\Api\IntegrationRunController@store');
    Route::put('/integration_runs/{id}', '\App\Http\Controllers\Api\IntegrationRunController@update');
    Route::get('/integration_runs/{id}', '\App\Http\Controllers\Api\IntegrationRunController@show');
    Route::delete('/integration_runs/{id}', '\App\Http\Controllers\Api\IntegrationRunController@delete');

    Route::get('/integration_logs', '\App\Http\Controllers\Api\IntegrationLogController@index');
    Route::post('/integration_logs', '\App\Http\Controllers\Api\IntegrationLogController@store');
    Route::put('/integration_logs/{id}', '\App\Http\Controllers\Api\IntegrationLogController@update');
    Route::get('/integration_logs/{id}', '\App\Http\Controllers\Api\IntegrationLogController@show');
    Route::delete('/integration_logs/{id}', '\App\Http\Controllers\Api\IntegrationLogController@delete');

    Route::delete('/uploads/{id}', '\App\Http\Controllers\Api\UploadsController@delete');
    Route::post('/uploads', '\App\Http\Controllers\Api\UploadsController@store');
});

