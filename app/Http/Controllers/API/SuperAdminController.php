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

            $hospital = Hospital::all()->count();
            $active_hospital = Hospital::all()->where('status','active')->count();
            $inactive_hospital = Hospital::all()->where('status','inactive')->count();
            $plans = Plan::all()->count();
            $subscription = Plan::all();

            $license_expired_hospitals = [];
            $all_hospital = Hospital::all();

            foreach($all_hospital as $duration){

                // print_r($duration['package_duration']);die;
            
            // Calculate the date 90 days ago
            $date = now()->subDays($duration['package_duration'])->toDateString();  // use toDateString() to get the date in 'Y-m-d' format
    
            // Query hospitals with the created_at or updated_at date equal to 90 days ago
            $license_expired_hospitals= Hospital::whereDate('created_at', $date)->count();
        }
            // $response = [
            //     'license_expired_hospitals' => $license_expired_hospitals,
            //     'date' => $date,
            // ];

            $response = [
                'total_hospital' => $hospital,
                'active_hospital' => $active_hospital,
                'inactive_hospital' => $inactive_hospital,
                'licence_expired' => $license_expired_hospitals,
                'subscription' => $subscription,
                // 'status'=>200,
            ];


        return $this->sendResponse($response, 'User dashboard successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

       

    }

    public function superAdminProfileShow(){
        $user = Auth::user();
        if(!empty($user)){
        $user_id = $user->id;

        // Fetch the profile associated with the authenticated user
        $profile = Profile::where('user_id', $user_id)->first();

        // Combine user and profile data into a response
        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'images' => $user->images,
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
            'images' => $user->images,
        ];

        return $this->sendResponse($response, 'User Edit Profile successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }
   
    

    public function profileUpdate($id, Request $request){

        //validator place
      if(!empty($id)){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // 'email' => 'required|string|email|max:255|unique:users,email,',
            'images' => 'required|image|mimes:jpeg,png,jpg,gif',
            'email' => 'required|string|email|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $users = user::find($id);
    
        // $user = Auth::user();

        // Delete the old profile image if it exists
        if ($users->images) {
            Storage::disk('public')->delete($users->images);
        }

        $path = $request->file('images')->store('images', 'public');
        $users->images = $path;
        $users->save();

        $users->update(['name' => $request->name, 'email'=>$request->email,'images'=>$path]);
        // $users->update(['name' => $request->name, 'email'=>$request->email, 'password'=>Hash::make($request->password),'images'=>$path]);

       
        $data[] = [
            'user'=>$users,
            'status'=>200,
          ];
      
          
          return $this->sendResponse($data, 'User Edit Profile successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
      
      }

}
