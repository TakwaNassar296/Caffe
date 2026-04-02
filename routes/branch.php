<?php

use App\Http\Controllers\Branch\SettingController;


   use App\Http\Controllers\Branch\AuthController;
   use App\Http\Controllers\Branch\BranchMaterialController;
use App\Http\Controllers\Branch\EmployeeAttendanceController;
use App\Http\Controllers\Branch\ExternalsMaterailController;
use App\Http\Controllers\Branch\LoyaltyController;
   use App\Http\Controllers\Branch\OperationsReportController;
   use App\Http\Controllers\Branch\OrderController;
   use App\Http\Controllers\Branch\ProductController;
   use App\Http\Controllers\Branch\ProductsReportController;
   use App\Http\Controllers\Branch\ReportController;
   use Illuminate\Support\Facades\Route;





      /***************************** Authentication  ***************************/
      Route::controller(AuthController::class)->group(function () {
         Route::post('login','login');
      });
      

      /***************************** Branch User ***************************/
      
      Route::middleware('auth:sanctum')->controller(OrderController::class)->group(function () {
         Route::get('orders','orders');
         Route::get('orders/{id}','show');
      });


         Route::middleware('auth:sanctum')->controller(ProductController::class)->group(function () {
         Route::get('products','products');
         Route::get('products/{id}','show');
         Route::post('/products/{product}/toggle-active', 'toggleActive');
         Route::get('total-categories','totalCategories');
      });

      Route::middleware('auth:sanctum')->controller(\App\Http\Controllers\Branch\CategoryController::class)->group(function () {
         Route::get('categories','index');
         Route::get('categories/{id}','show');
      });


      Route::middleware('auth:sanctum')->controller(BranchMaterialController::class)->group(function(){
         Route::get('materials','materials');
         Route::post('material-requests','createMaterialRequest');
         Route::get('material-requests','getMaterialRequests');
         Route::get('material-requests/history','getApprovalHistory');
         Route::post('material-requests/{id}/confirm-delivery','confirmDelivery');
      });

        Route::middleware('auth:sanctum')->controller(ExternalsMaterailController::class)->group(function(){
         Route::get('material-externals','materials');
         Route::post('material-externals-requests','createMaterialRequest');
         Route::get('material-externals-requests','getMaterialRequests');
         Route::get('material-externals-requests/history','getApprovalHistory');
         Route::post('material-externals-requests/{id}/confirm-delivery','confirmDelivery');
      });

      Route::middleware('auth:sanctum')->controller(OrderController::class)->group(function(){
         Route::post('change-status','changeStatus');
      });

      /***************************** Loyalty ***************************/
      Route::middleware('auth:sanctum')->controller(LoyaltyController::class)->group(function(){
         Route::get('employees','employees');
         Route::post('award-points','awardPoints');
      });

      Route::middleware('auth:sanctum')->controller(ReportController::class)->group(function(){
      Route::get('financial-reports','financialReports');
      });

      Route::middleware('auth:sanctum')->controller(OperationsReportController::class)->group(function(){
      Route::get('operations-reports','operationsReports');
      });

      Route::middleware('auth:sanctum')->controller(ProductsReportController::class)->group(function(){
      Route::get('products-reports','productsReports');
      });


      Route::middleware('auth:sanctum')->controller(SettingController::class)->group(function(){
         Route::get('settings','Setting');
         Route::post('settings','updateSettings');
      });

      /***************************** Employee Attendance ***************************/
      Route::middleware('auth:sanctum')->controller(EmployeeAttendanceController::class)->group(function(){
         Route::get('attendance/employees','employees'); // Get all employees in branch
         Route::post('attendance/status','updateStatus'); // Update attendance/departure status (requires employee_id, status, notes)
         Route::post('attendance','attendance'); // Record attendance (requires employee_id) - Legacy
         Route::post('departure','departure'); // Record departure (requires employee_id) - Legacy
         Route::post('attendance/toggle','toggle'); // Toggle attendance/departure (requires employee_id) - Legacy
         Route::get('attendance','index'); // Get all attendance records (can filter by employee_id)
         Route::get('attendance/today','today'); // Get today's attendance (requires employee_id)
      });
