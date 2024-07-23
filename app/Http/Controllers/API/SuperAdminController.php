<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use App\Models\Hospital;
use App\Models\Plan;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


class SuperAdminController extends BaseController
{
    //
     /**
    * Register api
    *
    * @return \Illuminate\Http\Response
    */

    public function auth(){

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password]))
        { 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
            $success['name'] =  $user->name;
            $success['name'] =  $user->email;
            return $this->sendResponse($success, 'User Edit Profile successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
        
    }

    public function dashboard(){
        $user = Auth::user();

        if(!empty($user)){

            if($user->type == 'super admin'){

            $hospital = User::all()->where('type','hospital')->count();
            $active_hospital = User::all()->where('type','hospital')->where('is_active',0)->count();
            $inactive_hospital = User::all()->where('type','hospital')->where('is_active',1)->count();

            $plans = Plan::all()->count();
            $subscription = Plan::all();

            $license_expired_hospitals = [];
            $all_hospital = Hospital::all();

            foreach($all_hospital as $duration){

            $date = now()->subDays($duration['package_duration'])->toDateString();  // use toDateString() to get the date in 'Y-m-d' format
            $currentDate = date('Y-m-d');
            
            $license_expired_hospitals = User::where('type','hospital')->whereDate('plan_expire_date', '<=', $currentDate)->count();
        }

            $response = [
                'total_hospital' => $hospital,
                'active_hospital' => $active_hospital,
                'inactive_hospital' => $inactive_hospital,
                'licence_expired' => $license_expired_hospitals,
                'subscription' => $subscription,
                
            ];

            
        }else if($user->type == 'hospital'){

            $hospital = User::all()->where('type','hospital')->count();
            $active_hospital = User::all()->where('type','hospital')->where('is_active',0)->count();
            $inactive_hospital = User::all()->where('type','hospital')->where('is_active',1)->count();

            $plans = Plan::all()->count();
            $subscription = Plan::all();

            $license_expired_hospitals = [];
            $all_hospital = Hospital::all();

            foreach($all_hospital as $duration){

            $date = now()->subDays($duration['package_duration'])->toDateString();  // use toDateString() to get the date in 'Y-m-d' format
            $currentDate = date('Y-m-d');
            
            $license_expired_hospitals = User::where('type','hospital')->whereDate('plan_expire_date', '<=', $currentDate)->count();
        }

            $response = [
                'total_doctor' => $hospital,
                'active_doctor' => $active_hospital,
                'inactive_doctor' => $inactive_hospital,
                'total_patient' => $license_expired_hospitals,
                'today_patient' => $hospital,
                'total_appointment' => $hospital,
                'today_appointment' => $hospital,
                'total_earning' => $hospital,
                'today_earning' => $hospital,
                'total_department' => $hospital,
                'today_appointment_list' => $subscription,
                
            ];

        }else if($user->type == 'doctor'){

            $hospital = User::all()->where('type','hospital')->count();
            $active_hospital = User::all()->where('type','hospital')->where('is_active',0)->count();
            $inactive_hospital = User::all()->where('type','hospital')->where('is_active',1)->count();

            $plans = Plan::all()->count();
            $subscription = Plan::all();

            $license_expired_hospitals = [];
            $all_hospital = Hospital::all();

            foreach($all_hospital as $duration){

            $date = now()->subDays($duration['package_duration'])->toDateString();  // use toDateString() to get the date in 'Y-m-d' format
            $currentDate = date('Y-m-d');
            
            $license_expired_hospitals = User::where('type','hospital')->whereDate('plan_expire_date', '<=', $currentDate)->count();
        }

            $response = [
                'total_hospital' => $hospital,
                'active_hospital' => $active_hospital,
                'inactive_hospital' => $inactive_hospital,
                'licence_expired' => $license_expired_hospitals,
                'subscription' => $subscription,
                
            ];
        }else if($user->type == 'patient'){

        }else if($user->type == 'clinician'){

        }else{

        }


        return $this->sendResponse($response, 'User dashboard successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

       

    }

    public function superAdminProfileShow(){
        $user = Auth::user();
        if(!empty($user)){
        $user_id = $user->id;

        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'images' => url('/storage/'.$user->images),
        ];

        return $this->sendResponse($response, 'User show Profile successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 

    }

    public function superAdminEditProfile(){
        $user = Auth::user();
        if(!empty($user)){
        $user_id = $user->id;
        
        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'images' => url('/storage/'.$user->images),
        ];

        return $this->sendResponse($response, 'User Edit Profile successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }
   
    

    public function profileUpdate($id, Request $request){

      if(!empty($id)){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'images' => 'required|image|mimes:jpeg,png,jpg,gif',
            'email' => 'required|string|email|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $users = user::find($id);
    
        if ($users->images) {
            Storage::disk('public')->delete($users->images);
        }

        $path = $request->file('images')->store('images', 'public');
        $users->images = $path;
        $users->save();

        $users->update(['name' => $request->name, 'email'=>$request->email,'images'=>$path]);
        // $users->update(['name' => $request->name, 'email'=>$request->email, 'password'=>Hash::make($request->password),'images'=>$path]);

        $updatedUsers = user::find($id);
        $updatedUsers['name'] = $users->name;
        $updatedUsers['email'] = $users->email;
        $updatedUsers['phone_number'] = $users->phone_number;
        $updatedUsers['images'] = url('/storage/'.$users->images);
        $data[] = [
            'user'=>$updatedUsers,
            'status'=>200,
          ];
      
          
          return $this->sendResponse($data, 'User Edit Profile successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
      
      }

}
