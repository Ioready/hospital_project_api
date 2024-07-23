<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\Utility;
use Illuminate\Auth\Events\PasswordReset;
use Mail;
use DB;
use App\Mail\TestMail;
use App\Mail\OTPMail;
use Illuminate\Support\Str;
use App\Models\Plan;
use Illuminate\Support\Facades\Storage;

class DoctorController extends BaseController
{
    //

    public function addDoctor(Request $request){

        if (\Auth::user()) {
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', '=', \Auth::user()->creatorId())->first();
            $objUser = \Auth::user()->id;

            if (\Auth::user()) {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required|max:120',
                        'email' => 'required|email|unique:users',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json($messages->first(), 422);
                    
                }


                $enableLogin = 0;
                if (!empty($request->password_switch) && $request->password_switch == 'on') {
                    $enableLogin = 1;
                    $validator = \Validator::make(
                        $request->all(), ['password' => 'required|min:6']
                    );

                    if ($validator->fails()) {

                        return response()->json($validator->errors(), 422);
                        
                    }
                }
                $userpassword = $request->password;
                $settings = Utility::settings();

                do {
                    $code = rand(100000, 999999);
                } while (User::where('referral_code', $code)->exists());

                $user = new User();
                $user['name'] = $request->name;
                $user['email'] = $request->email;
                $psw = $request->password;
                $user['password'] = Hash::make($request->password);
                $user['type'] = $request->user_role;
                $user['address'] = $request->address;
                $user['phone_number'] = $request->phone;
                $user['plan'] = 1;
                $user['referral_code'] = $code;
                $user['created_by'] = \Auth::user()->id;
                $user['plan'] = Plan::first()->id;
                $user['role_id'] = 2;
                
                if ($settings['email_verification'] == 'on') {

                    $user['email_verified_at'] = null;
                } else {
                    $user['email_verified_at'] = date('Y-m-d H:i:s');
                }
                $user['is_enable_login'] = $enableLogin;

                // $users = user::find($id);
    
        // $user = Auth::user();

        // Delete the old profile image if it exists
        // if ($user->images) {
        //     Storage::disk('public')->delete($user->images);
        // }

        // $path = $request->file('logo')->store('images', 'public');
        // $user->images = $path;
        // $user->save();

            // $user->save();

              $lasrId =  DB::getPdo()->lastInsertId();

            $hospital = Doctor::create([
            'name' => $request->name,
            'email' => $request->input('email'),
            'password' =>Hash::make($request->password),
            'hospital_id'=>$objUser,
            'date_of_birth'=>$request->date_of_birth,
            'status'=>'active',
            'created_by' => \Auth::user()->creatorId()
                    ]);


                $objUser = User::find($lasrId);
                $user = User::find(\Auth::user()->created_by);
                $total_user = $objUser->countUsers();
                $plan = Plan::find($objUser->plan);
                
                $userpassword = $request->password;

                $date = now()->subDays($objUser['package_duration'])->toDateString(); 
               

                $objHospital['id']= $objUser->id;
                $objHospital['name']= $objUser->name;
                $objHospital['email']= $objUser->email;
                $objHospital['address']= $objUser->address;
                $objHospital['phone_number']= $objUser->phone_number;
                $objHospital['images']= $objUser->images;
                $objHospital['is_active']= $objUser->is_active;
                $objHospital['type']= $objUser->type;
                $objHospital['is_enable_login']= $objUser->is_enable_login;
                $objHospital['plan']= $plan->card_title;
                $objHospital['plan_expire']= $plan->created_at;   
            }
            // Send Email
            // $setings = Utility::settings();
            // if ($setings['new_user'] == 1) {

            //     $user->password = $request->password;
            //     $user->type = $role_r->name;
            //     $user->userDefaultDataRegister($user->id);

            //     $userArr = [
            //         'email' => $user->email,
            //         'password' => $userpassword,
            //     ];
            //     $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $userArr);

            //     if (\Auth::user()->type == 'super admin') {
            //         return $this->sendResponse($user, 'success', __('Hospital successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            //         // return redirect()->route('users.index')->with('success', __('Company successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            //     } else {
            //         return $this->sendResponse($user, 'success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            //         // return redirect()->route('users.index')->with('success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

            //     }
            // }

            if (\Auth::user()->type == 'super admin') {
                return $this->sendResponse($objHospital, 'Hospital successfully created.');
                // return redirect()->route('users.index')->with('success', __('Company successfully created.'));s
            } else {
                return $this->sendResponse($objHospital, 'success', __('User successfully created.'));
                

            }


        } else {
            return false;
            // return redirect()->back();
        }
    }
}
