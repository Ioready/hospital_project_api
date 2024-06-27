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
            $response = [
                'total_hospital' => $hospital,
                'active_hospital' => $active_hospital,
                'inactive_hospital' => $inactive_hospital,
                'licence_expired' => $plans,
                'subscription' => $subscription,
                // 'status'=>200,
            ];


        return $this->sendResponse($response, 'User Edit Profile successfully.');
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
            'user' => $user,
            'profile' => $profile
        ];

        return $this->sendResponse($response, 'User Edit Profile successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 

    }

    public function superAdminEditProfile(){
        $user = Auth::user();
        if(!empty($user)){
        $user_id = $user->id;

        // Fetch the profile associated with the authenticated user
        $profile = Profile::where('user_id', $user_id)->first();

        // Combine user and profile data into a response
        $response = [
            'user' => $user,
            'profile' => $profile
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
            // 'email' => 'required|string|email|max:255|unique:users,email,' . $users->id,
            'country_id' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
            // 'avatar' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        
            // Add other fields as necessary
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        

        $users = user::find($id);
        $users->name = $request->name;
        // $users->thumbnail = $request->avatar->store('avatars','public');
        $users->save();

        // $users->update($request->only(['name']));
        
        $profile = Profile::where('user_id', $id)->first();

        if (!$profile) {
            $profile = Profile::create([
                'user_id' => $users->id,
                'mobile_number' => $request->input('mobile_number'),
                'country_id' => $request->input('country_id'),
                'state' => $request->input('state'),
                'city' => $request->input('city'),
                'postal_code' => $request->input('postal_code'),
                'status' => $request->input('status'),
               
            ]);
            // return response()->json(['error' => 'Profile not found'], 404);
        }else{

       

        $profile->update($request->only(['mobile_number', 'country_id','state','city','postal_code','status']));
        // Add other fields as necessary
    }
        $data[] = [
            'user'=>$users,
            'profile'=>$profile,
            'avatar'=>Storage::url($profile->avatar),
            'status'=>200,
          ];
      
          
          return $this->sendResponse($data, 'User Edit Profile successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
      
      }

}
