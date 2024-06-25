<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    //

    public function allHospitals(){

        $hospital = Hospital::all();
        if(!empty($plans)){
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

        $plans = Hospital::create([
            'hospital_name' => $request->plan_name,
            'patient_limit' => $request->input('patient_limit'),
            'doctor_limit' => $request->input('doctor_limit'),
            'monthly_price' => $request->input('monthly_price'),
            'yearly_price' => $request->input('yearly_price'),
            'permission_module' => $request->input('permission_module'),
            'description'=> $request->input('description'),
            'avatar'=> $request->input('avatar'),
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
