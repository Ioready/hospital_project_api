
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\SuperAdminController;
use App\Http\Controllers\API\PlanController;
use App\Http\Controllers\API\HospitalController;



Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::post('logout', [RegisterController::class, 'logout']);
    Route::get('/users', [RegisterController::class, 'index'])->name('index');
    Route::get('/superadmin_show', [SuperAdminController::class, 'superAdminProfileShow']);
    Route::get('/superadmin/edit_profile', [SuperAdminController::class, 'superAdminEditProfile']);
    Route::put('/superadmin/update-profile/{id}', [SuperAdminController::class, 'profileUpdate']);
    Route::get('/superadmin/dashboard', [SuperAdminController::class, 'dashboard']);

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

    Route::get('/superadmin/active_hospitals', [HospitalController::class, 'activeHospital']);
    Route::get('/superadmin/inactive_hospitals', [HospitalController::class, 'inactiveHospital']);
    Route::get('/superadmin/license_expired_hospital', [HospitalController::class, 'licenseExpiredHospital']);

    
    
});
