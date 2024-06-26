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

class HospitalController extends BaseController
{
    //

    public function allHospitals(){

        $hospital = Hospital::all();
        if(!empty($hospital)){
        $response = [
            'hospital' => $hospital,
            'status'=>200,
            
        ];

        return $this->sendResponse($response, 'All hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    public function addHospitals(Request $request){

        $user = Auth::user();
        if(!empty($user)){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'email' => 'required',
            'password' => 'required|string|max:255',
            'frontend_website_link' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'language'=> 'required|string|max:255',
            'package_duration' => 'required|max:255',
            'Country'=>'required|string|max:255',
            'package'=> 'required|string|max:255',
            'patient_limit'=> 'required|max:255',
            'doctor_limit'=> 'required|string|max:255',
            'permitted_modules'=> 'required|max:255',
            'price'=> 'required|max:255',
            'deposit_type'=> 'required|string|max:255',
            'do_you_want_trial_version'=> 'required|string|max:255',
            'logo'=> 'required|max:255',
            'status' => 'required|string|max:255',
        
            // Add other fields as necessary
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $hospital = Hospital::create([
            'title' => $request->title,
            'email' => $request->input('email'),
            'password' =>Hash::make($request->password),
            'frontend_website_link' => $request->input('frontend_website_link'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'language'=> $request->input('language'),
            'package_duration'=> $request->input('package_duration'),
            'Country'=>$request->input('Country'),
            'package'=> $request->input('package'),
            'patient_limit'=> $request->input('patient_limit'),
            'doctor_limit'=> $request->input('doctor_limit'),
            'permitted_modules'=> $request->input('permitted_modules'),
            'price'=> $request->input('price'),
            'deposit_type'=>$request->input('deposit_type'),
            'do_you_want_trial_version'=> $request->input('do_you_want_trial_version'),
            'logo'=> $request->input('logo'),
            'status' => $request->input('status'),
           
        ]);

        $data[] = [
            'hospital'=>$hospital,
            'avatar'=>Storage::url($hospital->avatar),
            'status'=>200,
          ];
      
          
          return $this->sendResponse($data, 'Add hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function editHospital($id){
        if(!empty($id)){
            $hospital = Hospital::find($id);
       
        $response = [
            'data' => $hospital,
            'status'=>200,
        ];

        return $this->sendResponse($response, 'User Edit hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }

    public function updateHospital($id, Request $request){

        $user = Auth::user();
        if(!empty($user)){

        $validator = Validator::make($request->all(), [
            'hospital_name' => 'required|string|max:255',
            'patient_limit' => 'required',
            'doctor_limit' => 'required|string|max:255',
            'monthly_price' => 'required|string|max:255',
            'yearly_price' => 'required|string|max:255',
            'permission_module' => 'required|string|max:255',
            'description'=> 'required|string|max:255',
            'avatar' => 'required|max:255',
            'status' => 'required|string|max:255',
        
            // Add other fields as necessary
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        
        $hospital = Hospital::find($id);
        $hospital->update($request->only(['hospital_name', 'patient_limit','doctor_limit','monthly_price','yearly_price','permission_module','description','status','status']));
        $data[] = [
            'hospital'=>$hospital,
            'avatar'=>Storage::url($hospital->avatar),
            'status'=>200,
          ];
      
          
          return $this->sendResponse($data, 'update hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function deletePHospital($id)
    {
        if (!empty($id)) {
            $hospital = Hospital::find($id);

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
}
