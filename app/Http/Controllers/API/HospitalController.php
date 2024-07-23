<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Hospital;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Utility;
use App\Models\Plan;
use App\Models\Order;



use App\Models\Profile;



class HospitalController extends BaseController
{
    //

    public function allHospitals(){
        if (\Auth::user()) {
        $hospitals = DB::table('users')
        ->select('users.id', 'users.name', 'users.email', 'users.role_id', 'users.address', 'users.phone_number', 'users.images', 'users.is_active', 'users.type', 'users.is_enable_login', 'plans.card_title')
        ->where('users.type', 'hospital')
        ->leftJoin('plans', 'users.plan', '=', 'plans.id')
        ->get();
        
        foreach ($hospitals as $hospital) {
            if (!empty($hospital->images)) {
                $hospital->hospital_images = url('/storage/' . $hospital->images);
            } else {
                $hospital->hospital_images = url('/default_image/images.png'); // Or provide a default image path
            }
        }   
        return $this->sendResponse($hospitals, 'All hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }


    public function addHospitals(Request $request)
    {
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
                $user['type'] = 'hospital';
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
        if ($user->images) {
            Storage::disk('public')->delete($user->images);
        }

        $path = $request->file('logo')->store('images', 'public');
        $user->images = $path;
        // $user->save();

            $user->save();

              $lasrId =  DB::getPdo()->lastInsertId();

            $hospital = Hospital::create([
            'title' => $request->title,
            'email' => $request->input('email'),
            'password' =>Hash::make($request->password),
            'plan'=>Plan::first()->id,
            'address'=>$request->address,
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

    // public function addHospitals(Request $request){

    //     $user = Auth::user();
    //     if(!empty($user)){

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required',
    //         'password' => 'required|string|max:255',
    //         // 'frontend_website_link' => 'required|string|max:255',
    //         'address' => 'required|string|max:255',
    //         'phone' => 'required|string|max:255',
    //         // 'language'=> 'required|string|max:255',
    //         // 'package_duration' => 'required|max:255',
    //         // 'Country'=>'required|string|max:255',
    //         // 'package'=> 'required|string|max:255',
    //         // 'patient_limit'=> 'required|max:255',
    //         // 'doctor_limit'=> 'required|string|max:255',
    //         // 'permitted_modules'=> 'required|max:255',
    //         // 'price'=> 'required|max:255',
    //         // 'deposit_type'=> 'required|string|max:255',
    //         // 'do_you_want_trial_version'=> 'required|string|max:255',
    //         'logo'=> 'required|max:255',
    //         'status' => 'required|string|max:255',
        
    //         // Add other fields as necessary
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     if ($request->logo) {
    //         Storage::disk('public')->delete($request->logo);
    //     }

    //     $path = $request->file('logo')->store('images', 'public');
    //     $request->logo = $path;
    //     // $request->save();

    //     $hospital = Hospital::create([
    //         'title' => $request->title,
    //         'email' => $request->input('email'),
    //         'password' =>Hash::make($request->password),
    //         'frontend_website_link' => $request->input('frontend_website_link'),
    //         'address' => $request->input('address'),
    //         'phone' => $request->input('phone'),
    //         'language'=> $request->input('language'),
    //         'package_duration'=> $request->input('package_duration'),
    //         'Country'=>$request->input('Country'),
    //         'package'=> $request->input('package'),
    //         'patient_limit'=> $request->input('patient_limit'),
    //         'doctor_limit'=> $request->input('doctor_limit'),
    //         'permitted_modules'=> $request->input('permitted_modules'),
    //         'price'=> $request->input('price'),
    //         'deposit_type'=>$request->input('deposit_type'),
    //         'do_you_want_trial_version'=> $request->input('do_you_want_trial_version'),
    //         'logo'=> $request->logo,
    //         'status' => $request->input('status'),
           
    //     ]);

    //     $data[] = [
    //         'hospital'=>$hospital,
    //         'avatar'=>Storage::url($hospital->avatar),
    //         'status'=>200,
    //       ];
      
          
    //       return $this->sendResponse($hospital, 'Add hospital successfully.');
    //     } else { 
    //         return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    //     }
    // }

    public function editHospitals($id){
        if(!empty($id)){
           
            $hospital = DB::table('users')
        ->select('users.id', 'users.name', 'users.email', 'users.role_id', 'users.address', 'users.phone_number', 'users.images', 'users.is_active', 'users.type', 'users.is_enable_login', 'plans.card_title')
        ->where('users.type', 'hospital')->where('users.id',$id)
        ->leftJoin('plans', 'users.plan', '=', 'plans.id')
        ->first();

       
        if ($hospital) {
            $hospital->hospital_images =  url('/storage/' . $hospital->images);
            return $this->sendResponse($hospital, 'User Edit hospital successfully.');
        } else {
            return $this->sendError('Hospital not found.', ['error' => 'Hospital not found']);
        }
           
        // return $this->sendResponse($hospitals, 'User Edit hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }

    public function updateHospitals(Request $request ,$id){

        // $user = Auth::user();
        // if(!empty($user)){

       
        //     $validator = Validator::make($request->all(), [
        //         'title' => 'required|string|max:255',
        //         'email' => 'required',
        //         'password' => 'required|string|max:255',
        //         'frontend_website_link' => 'required|string|max:255',
        //         'address' => 'required|string|max:255',
        //         'phone' => 'required|string|max:255',
        //         'language'=> 'required|string|max:255',
        //         'package_duration' => 'required|max:255',
        //         'Country'=>'required|string|max:255',
        //         'package'=> 'required|string|max:255',
        //         'patient_limit'=> 'required|max:255',
        //         'doctor_limit'=> 'required|string|max:255',
        //         'permitted_modules'=> 'required|max:255',
        //         'price'=> 'required|max:255',
        //         'deposit_type'=> 'required|string|max:255',
        //         'do_you_want_trial_version'=> 'required|string|max:255',
        //         'logo'=> 'required|max:255',
        //         'status' => 'required|string|max:255',
            
        //         // Add other fields as necessary
        //     ]);

        // if ($validator->fails()) {
        //     return response()->json($validator->errors(), 422);
        // }

        

        
        // $hospital = Hospital::find($id);

        // if ($request->logo) {
        //     Storage::disk('public')->delete($request->logo);
        // }

        // $path = $request->file('logo')->store('images', 'public');
        // $request->logo = $path;
        // // $hospital->save();
        // // $pass = Hash::make($request->password);
        // $hospital->update($request->only(['title', 'email','password','frontend_website_link','address','phone','language','package_duration','Country','package','patient_limit','doctor_limit','permitted_modules','price','deposit_type','do_you_want_trial_version','logo','status']));
        // $hospital->update(['logo' => $request->logo]);

        // $data[] = [
        //     'hospital'=>$hospital,
        //     'avatar'=>Storage::url($hospital->avatar),
        //     'status'=>200,
        //   ];
      
          
        //   return $this->sendResponse($hospital, 'update hospital successfully.');
        // } else { 
        //     return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        // }


        if (\Auth::user()) {
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', '=', \Auth::user()->creatorId())->first();
            $objUser = \Auth::user()->id;

            if (\Auth::user()) {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required|max:120',
                        'email' => 'required|email',
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

                $user = User::where('type','hospital')->where('id',$id)->first();
                if(!empty($user)){

                
                $user['name'] = $request->name;
                $user['email'] = $request->email;
                $psw = $request->password;
                // $user['password'] = Hash::make($request->password);
                $user['type'] = 'hospital';
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

                
                if ($user->images) {
                    Storage::disk('public')->delete($user->images);
                }
        
                $path = $request->file('logo')->store('images', 'public');
                $user->images = $path;

                $user->save();
            }else{
                return $this->sendResponse($user, 'hospital not found.');
            }

            //   $lasrId =  DB::getPdo()->lastInsertId();

            // $hospital = Hospital::where('created_by');
            // 'title' => $request->title,
            // 'email' => $request->input('email'),
            // 'password' =>Hash::make($request->password),
            // 'plan'=>Plan::first()->id,
            // 'address'=>$request->address,
            
            // 'created_by' => \Auth::user()->creatorId();


                $objUser = User::find($id);
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
                return $this->sendResponse($objHospital, 'Update Hospital successfully.');
                // return redirect()->route('users.index')->with('success', __('Company successfully created.'));s
            } else {
                return $this->sendResponse($objHospital, 'success', __('User successfully created.'));
                

            }


        } else {
            return false;
            // return redirect()->back();
        }
    }


    // public function statusUpdateHospitals(Request $request ,$id){

    //     $user = Auth::user();
    //     if(!empty($user)){

       
    //         $validator = Validator::make($request->all(), [
                
    //             'status' => 'required|max:255',
    //         ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

        
    //     $hospital = Hospital::find($id);
    //     $hospital->update(['status' => $request->status]);
    //     $data[] = [
    //         'hospital'=>$hospital,
    //         'status'=>200,
    //       ];
      
          
    //       return $this->sendResponse($hospital, 'update status hospital successfully.');
    //     } else { 
    //         return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    //     }
    // }
    public function statusUpdateHospitals(Request $request, $id)
    {
    
    $user = Auth::user();
    if ($user) {

        $validator = Validator::make($request->all(), [
            'is_active' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {

            $hospital = User::find($id);

            if (!$hospital) {
                return response()->json(['error' => 'Hospital not found'], 404);
            }

            User::where('id', $id)->update(['is_active' => $request->is_active]);
            $hospitals = User::find($id);
            $data = [
                'hospital' => $hospitals,
                'status' => 200,
            ];

            return $this->sendResponse($data, 'Hospital status updated successfully.');
        } catch (\Exception $e) {
            
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }

    } else {
        return $this->sendError('Unauthorized.', ['error' => 'Unauthorized']);
    }
}


    public function deleteHospitals($id)
    {
        if (!empty($id)) {
            $hospital = User::find($id);

            if ($hospital) {
                $hospital->delete();
                
                return $this->sendResponse($hospital, 'Hospital deleted successfully.');
            } else {
                return $this->sendError('Hospital not found.', ['error' => 'Hospital not found']);
            }
        } else {
            return $this->sendError('Invalid ID.', ['error' => 'Invalid ID']);
        }
    }

    
    public function activeHospital(){
        $user = Auth::user();
        if(!empty($user)){

            
            $active_hospitalCount = User::all()->where('type','hospital')->where('is_active','0')->count();
            $active_hospital = User::all()->where('type','hospital')->where('is_active','0');
           
            $response = [
                'active_hospital_count' => $active_hospitalCount,
                'active_hospital' => $active_hospital,
                
                // 'status'=>200,
            ];


        return $this->sendResponse($response, 'User active  hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function inactiveHospital(){
        $user = Auth::user();
        if(!empty($user)){

            
            
            $inactive_hospitalCount = User::all()->where('type','hospital')->where('is_active',1)->count();
            $inactive_hospital = User::all()->where('type','hospital')->where('is_active',1);
            
            $response = [
                
                'count_inactive_hospital' => $inactive_hospitalCount,
                'inactive_hospital' => $inactive_hospital,
               
                // 'status'=>200,
            ];


        return $this->sendResponse($response, 'User inactive hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }


    public function licenseExpiredHospital() {
        $user = Auth::user();
        if(!empty($user)) {

            $license_expired_hospitals = [];
            $all_hospital = User::where('type','hospital')->get();

            foreach($all_hospital as $duration){

              
            // $plans = Plan::find($duration->plan);
            // $plans->
            // Calculate the date 90 days ago
            $date = now()->subDays($duration['package_duration'])->toDateString();  // use toDateString() to get the date in 'Y-m-d' format
    
            // Query hospitals with the created_at or updated_at date equal to 90 days ago
            $currentDate = date('Y-m-d');

            $license_expired_hospitals = [
                'all_hospital' => User::whereDate('plan_expire_date', '<=', $currentDate)->get()
            ];
        }
            $response = [
                'license_expired_hospitals' => $license_expired_hospitals,
                'date' => $date,
            ];
    
            return $this->sendResponse($response, 'Hospitals with license expired 90 days ago retrieved successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function upgradePlan($user_id)
    {
        $user = Auth::user();
        if(!empty($user)) {

        $user = User::find($user_id);
        $plans = Plan::get();
        $admin_payment_setting = Utility::getAdminPaymentSetting();

        // return view('user.plan', compact('user', 'plans', 'admin_payment_setting'));
        $data= array(

            'user'=>$user,
            'plans_list'=>$plans,
        );

        return $this->sendResponse($data, 'admin_payment_setting.');
    } else { 
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    }
    }

    public function hospitalUpgradePlan($user_id, $plan_id)
    {

        $plan = Plan::find($plan_id);
        if($plan->status == 1)
        {
            return $this->sendError('error','You are unable to upgrade this plan because it is disabled.');
            // return redirect()->back()->with('error', __('You are unable to upgrade this plan because it is disabled.'));
        }

        $user = User::find($user_id);
        $assignPlan = $user->assignPlan($plan_id);
        if ($assignPlan['is_success'] == true && !empty($plan)) {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            Order::create(
                [
                    'order_id' => $orderID,
                    'name' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'price_currency' => isset(\Auth::user()->planPrice()['currency'])?\Auth::user()->planPrice()['currency'] : '',
                    'txn_id' => '',
                    'payment_status' => 'success',
                    'receipt' => null,
                    'user_id' => $user->id,
                ]
            );

        //     return redirect()->back()->with('success', 'Plan successfully upgraded.');
        // } else {
        //     return redirect()->back()->with('error', 'Plan fail to upgrade.');
        // }
        return $this->sendResponse($user, 'Plan successfully upgraded.');
    } else { 
        return $this->sendError('error','Plan fail to upgrade.');
    }

    }
    
}
