<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\SuperAdminController;
use App\Http\Controllers\API\PlanController;
use App\Http\Controllers\API\HospitalController;
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
/** ---------Register and Login ----------- */
Route::controller(RegisterController::class)->group(function()
{
    
    // Route::post('login', 'login');
    Route::post('users', 'login')->name('index');

    

});
Route::post('register', [RegisterController::class,'register']);
    Route::post('/login', [RegisterController::class, 'login']);

/** -----------Users --------------------- */
Route::middleware('auth')->group(function() {
    
    Route::post('/logout',[RegisterController::class,'logout']);
    Route::get('/users',[RegisterController::class,'index'])->name('index');
    Route::get('/superadmin_show', [App\Http\Controllers\API\SuperAdminController::class, 'superAdminProfileShow']);
    Route::get('/superadmin/edit_profile', [App\Http\Controllers\API\SuperAdminController::class, 'superAdminEditProfile']);
    Route::put('/superadmin/update-profile/{id}', [App\Http\Controllers\API\SuperAdminController::class, 'profileUpdate']);
    Route::get('/superadmin/dashboard', [App\Http\Controllers\API\SuperAdminController::class, 'dashboard']);
    
    
    Route::post('/superadmin/add_plans', [PlanController::class, 'addPlans']);
    Route::get('/superadmin/all_Plans', [PlanController::class, 'allPlans']);
    Route::get('/superadmin/edit_Plans/{id}', [PlanController::class, 'editPlans']);
    Route::put('/superadmin/update_Plans/{id}', [PlanController::class, 'updatePlans']);
    Route::delete('/superadmin/delete_Plans/{id}', [PlanController::class, 'deletePlans']);


    Route::post('/superadmin/add_hospitals', [HospitalController::class, 'addHospitals']);
    Route::get('/superadmin/all_hospitals', [HospitalController::class, 'allHospitals']);
    Route::get('/superadmin/edit_hospitals/{id}', [HospitalController::class, 'editHospitals']);
    Route::put('/superadmin/update_hospitals/{id}', [HospitalController::class, 'updateHospitals']);
    Route::delete('/superadmin/delete_hospitals/{id}', [HospitalController::class, 'deleteHospitals']);
    Route::put('/superadmin/status_update_hospital/{id}', [HospitalController::class, 'statusUpdateHospitals']);

    

    

    
    
});

// Route::middleware('auth:sanctum')->controller(RegisterController::class)->group(function() {
//     Route::get('/users','index')->name('index');
// });


// Route::middleware('auth:sanctum')->group(function () {

//     Route::get('/profile', [SuperAdminController::class, 'superAdminProfileShow']);
//     Route::put('/profile', [SuperAdminController::class, 'update']);
// });

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
