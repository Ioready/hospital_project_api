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
        // $response = [
        //     'hospital' => $hospital,
        //     'status'=>200,
            
        // ];

        return $this->sendResponse($hospital, 'All hospital successfully.');
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
      
          
          return $this->sendResponse($hospital, 'Add hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function editHospitals($id){
        if(!empty($id)){
            $hospital = Hospital::find($id);
       
        $response = [
            'data' => $hospital,
            'status'=>200,
        ];

        return $this->sendResponse($hospital, 'User Edit hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }

    public function updateHospitals(Request $request ,$id){

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

        
        $hospital = Hospital::find($id);
        // $pass = Hash::make($request->password);
        $hospital->update($request->only(['title', 'email','password','frontend_website_link','address','phone','language','package_duration','Country','package','patient_limit','doctor_limit','permitted_modules','price','deposit_type','do_you_want_trial_version','logo','status']));
        $data[] = [
            'hospital'=>$hospital,
            'avatar'=>Storage::url($hospital->avatar),
            'status'=>200,
          ];
      
          
          return $this->sendResponse($hospital, 'update hospital successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
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
    // Authenticate the user
    $user = Auth::user();
    if ($user) {

        // Validate the request
        $validator = Validator::make($request->all(), [
            'status' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Find the hospital by ID
            $hospital = Hospital::find($id);
            if (!$hospital) {
                return response()->json(['error' => 'Hospital not found'], 404);
            }

            // Update the hospital status
            $hospital->update(['status' => $request->status]);

            // Prepare the response data
            $data = [
                'hospital' => $hospital,
                'status' => 200,
            ];

            return $this->sendResponse($data, 'Hospital status updated successfully.');
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }

    } else {
        return $this->sendError('Unauthorized.', ['error' => 'Unauthorized']);
    }
}


    public function deleteHospitals($id)
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

    
    public function activeHospital(){
        $user = Auth::user();
        if(!empty($user)){

            
            $active_hospitalCount = Hospital::all()->where('status','active')->count();
            $active_hospital = Hospital::all()->where('status','active');
           
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

            
            
            $inactive_hospitalCount = Hospital::all()->where('status','inactive')->count();
            $inactive_hospital = Hospital::all()->where('status','inactive');
            
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
            $all_hospital = Hospital::all();

            foreach($all_hospital as $duration){

                // print_r($duration['package_duration']);die;
            
            // Calculate the date 90 days ago
            $date = now()->subDays($duration['package_duration'])->toDateString();  // use toDateString() to get the date in 'Y-m-d' format
    
            // Query hospitals with the created_at or updated_at date equal to 90 days ago
            $license_expired_hospitals['all_hospital'] = Hospital::whereDate('created_at', $date)->get();
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
    
}
