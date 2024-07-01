
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\SuperAdminController;
use App\Http\Controllers\API\PlanController;
use App\Http\Controllers\API\HospitalController;
use App\Http\Controllers\API\EmailController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\StripePaymentController;

use App\Http\Controllers\API\BankTransferPaymentController;
use App\Http\Controllers\API\SystemController;
use App\Http\Controllers\API\ReferralProgramController;



Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::post('logout', [RegisterController::class, 'logout']);
    Route::get('/users', [RegisterController::class, 'index'])->name('index');
    Route::get('/superadmin_show', [SuperAdminController::class, 'superAdminProfileShow']);
    Route::get('/superadmin/edit_profile', [SuperAdminController::class, 'superAdminEditProfile']);
    Route::post('/superadmin/update-profile/{id}', [SuperAdminController::class, 'profileUpdate']);
    Route::post('change-password', [RegisterController::class, 'updatePassword'])->name('update.password');

    Route::get('/superadmin/dashboard', [SuperAdminController::class, 'dashboard']);

    Route::post('/superadmin/add_plans', [PlanController::class, 'addPlans']);
    Route::get('/superadmin/all_Plans', [PlanController::class, 'allPlans']);
    Route::get('/superadmin/edit_Plans/{id}', [PlanController::class, 'editPlans']);
    Route::put('/superadmin/update_Plans/{id}', [PlanController::class, 'updatePlans']);
    Route::put('/superadmin/status_update_Plans/{id}', [PlanController::class, 'statusUpdatePlans']);
    Route::delete('/superadmin/delete_Plans/{id}', [PlanController::class, 'deletePlans']);
    Route::get('/superadmin/active_Plans', [PlanController::class, 'activePlans']);
    Route::get('/superadmin/inactive_Plans', [PlanController::class, 'inactivePlans']);
    


    Route::post('/superadmin/add_hospitals', [HospitalController::class, 'addHospitals']);
    Route::get('/superadmin/all_hospitals', [HospitalController::class, 'allHospitals']);
    Route::get('/superadmin/edit_hospitals/{id}', [HospitalController::class, 'editHospitals']);
    Route::post('/superadmin/update_hospitals/{id}', [HospitalController::class, 'updateHospitals']);
    Route::delete('/superadmin/delete_hospitals/{id}', [HospitalController::class, 'deleteHospitals']);
    Route::put('/superadmin/status_update_hospital/{id}', [HospitalController::class, 'statusUpdateHospitals']);

    Route::get('/superadmin/active_hospitals', [HospitalController::class, 'activeHospital']);
    Route::get('/superadmin/inactive_hospitals', [HospitalController::class, 'inactiveHospital']);
    Route::get('/superadmin/license_expired_hospital', [HospitalController::class, 'licenseExpiredHospital']);

    Route::get('/superadmin/email_list', [EmailController::class, 'index']);
    Route::post('/superadmin/new_email', [EmailController::class, 'newEmail']);
    Route::post('/superadmin/send_email', [EmailController::class, 'sendEmail']);

    Route::get('/superadmin/edit_email/{id}', [EmailController::class, 'editEmail']);
    Route::get('/superadmin/show_email/{id}', [EmailController::class, 'showEmail']);
    Route::delete('/superadmin/delete_email/{id}', [EmailController::class, 'deleteEmail']);

    
    


    Route::post('/superadmin/add-coupons', [CouponController::class, 'create']);
    Route::put('/superadmin/update-coupons/{id}', [CouponController::class, 'update']);
    Route::get('/superadmin/coupons/list/', [CouponController::class, 'index']);

    Route::get('/superadmin/coupons/validate/{code}', [CouponController::class, 'validateCoupon']);
    Route::delete('/superadmin/delete-coupons/{id}', [CouponController::class, 'delete']);

    Route::get('/superadmin/edit-coupons/{id}', [CouponController::class, 'editCoupon']);
    Route::post('/superadmin/apply-coupons', [CouponController::class, 'applyCoupon']);

    
    //plan-order
    Route::post('order/{id}/changeaction', [BankTransferPaymentController::class, 'changeStatus'])->name('order.changestatus');
    Route::delete('order/{id}', [BankTransferPaymentController::class, 'orderDestroy'])->name('order.destroy');
    Route::get('order/{id}/action', [BankTransferPaymentController::class, 'action'])->name('order.action');

    Route::get('order', [StripePaymentController::class, 'index']);
    Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe']);
    Route::post('/stripe', [StripePaymentController::class, 'stripePost']);

//    Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal')->middleware(['auth', 'XSS', 'revalidate']);
//    Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name('plan.get.payment.status')->middleware(['auth', 'XSS', 'revalidate']);



            // Route::resource('systems', SystemController::class);
            Route::get('systems', [SystemController::class, 'index']);
            Route::post('add-brand-setting', [SystemController::class, 'store']);

            Route::post('email-settings', [SystemController::class, 'saveEmailSettings'])->name('email.settings');
            Route::post('hospital-email-settings', [SystemController::class, 'saveHospitalEmailSettings'])->name('hospital.email.settings');

            Route::post('hospital-settings', [SystemController::class, 'saveHospitalSettings'])->name('hospital.settings');
            Route::post('system-settings', [SystemController::class, 'saveSystemSettings'])->name('system.settings');
            Route::post('zoom-settings', [SystemController::class, 'saveZoomSettings'])->name('zoom.settings');
            Route::post('tracker-settings', [SystemController::class, 'saveTrackerSettings'])->name('tracker.settings');
            Route::post('slack-settings', [SystemController::class, 'saveSlackSettings'])->name('slack.settings');
            Route::post('telegram-settings', [SystemController::class, 'saveTelegramSettings'])->name('telegram.settings');
            Route::post('twilio-settings', [SystemController::class, 'saveTwilioSettings'])->name('twilio.setting');
            Route::get('print-setting', [SystemController::class, 'printIndex'])->name('print.setting');
            Route::get('settings', [SystemController::class, 'hospitalIndex'])->name('settings');
            Route::post('business-setting', [SystemController::class, 'saveBusinessSettings'])->name('business.setting');
            Route::post('hospital-payment-setting', [SystemController::class, 'saveHospitalPaymentSettings'])->name('hospital.payment.settings');
            Route::post('currency-settings', [SystemController::class, 'saveCurrencySettings'])->name('currency.settings');
            Route::post('hospital-preview', [SystemController::class, 'currencyPreview'])->name('currency.preview');


            Route::get('test-mail', [SystemController::class, 'testMail'])->name('test.mail');
            Route::post('test-mail', [SystemController::class, 'testMail'])->name('test.mail');
            Route::post('test-mail/send', [SystemController::class, 'testSendMail'])->name('test.send.mail');

            Route::post('stripe-settings', [SystemController::class, 'savePaymentSettings'])->name('payment.settings');
            Route::post('pusher-setting', [SystemController::class, 'savePusherSettings']);

            Route::post('recaptcha-settings', [SystemController::class, 'recaptchaSettingStore'])->name('recaptcha.settings.store');

            // Route::post('seo-settings', [SystemController::class, 'seoSettings'])->name('seo.settings.store')->middleware(['auth', 'XSS']);
            // Route::any('webhook-settings', [SystemController::class, 'webhook'])->name('webhook.settings')->middleware(['auth', 'XSS']);
            // Route::get('webhook-settings/create', [SystemController::class, 'webhookCreate'])->name('webhook.create')->middleware(['auth', 'XSS']);
            // Route::post('webhook-settings/store', [SystemController::class, 'webhookStore'])->name('webhook.store');
            // Route::get('webhook-settings/{wid}/edit', [SystemController::class, 'webhookEdit'])->name('webhook.edit')->middleware(['auth', 'XSS']);
            // Route::post('webhook-settings/{wid}/edit', [SystemController::class, 'webhookUpdate'])->name('webhook.update')->middleware(['auth', 'XSS']);
            // Route::delete('webhook-settings/{wid}', [SystemController::class, 'webhookDestroy'])->name('webhook.destroy')->middleware(['auth', 'XSS']);

            Route::post('cookie-setting', [SystemController::class, 'saveCookieSettings'])->name('cookie.setting');

            Route::post('cache-settings', [SystemController::class, 'cacheSettingStore'])->name('cache.settings.store');

            Route::get('referral-program', [ReferralProgramController::class, 'index']);
            Route::post('add-referral-program-setting', [ReferralProgramController::class, 'store']);
            Route::get('referral-program/hospital', [ReferralProgramController::class, 'hospitalIndex'])->name('referral-program.hospital');
            // Route::resource('referral-program', ReferralProgramController::class);
            Route::get('request-amount-sent/{id}', [ReferralProgramController::class, 'requestedAmountSent'])->name('request.amount.sent');
            Route::get('request-amount-cancel/{id}', [ReferralProgramController::class, 'requestCancel'])->name('request.amount.cancel');
            Route::post('request-amount-store', [ReferralProgramController::class, 'requestedAmountStore'])->name('request.amount.store');
            Route::get('request-amount/{id}/{status}', [ReferralProgramController::class, 'requestedAmount'])->name('amount.request');

    
    

    
    
});
