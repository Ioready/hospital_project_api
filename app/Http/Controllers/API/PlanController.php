<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Storage;

class PlanController extends BaseController
{
    //
    public function allPlans(){

        $plans = Plan::all();
        if(!empty($plans)){
        $response = [
            'plans' => $plans,
            'status'=>200,
            
        ];

        return $this->sendResponse($response, 'All Plans successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    public function addPlans(Request $request){

        $user = Auth::user();
        if(!empty($user)){

        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|string|max:255',
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

        $plans = Plan::create([
            'plan_name' => $request->plan_name,
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
            'plans'=>$plans,
            'avatar'=>Storage::url($plans->avatar),
            'status'=>200,
          ];
      
          
          return $this->sendResponse($data, 'Add Plans successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function editPlans($id){
        if(!empty($id)){
            $plan = Plan::find($id);
       
        $response = [
            'data' => $plan,
            'status'=>200,
        ];

        return $this->sendResponse($response, 'User Edit plans successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }

    public function updatePlans($id, Request $request){

        $user = Auth::user();
        if(!empty($user)){

        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|string|max:255',
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

        
        $plans = Plan::find($id);
        $plans->update($request->only(['plan_name', 'patient_limit','doctor_limit','monthly_price','yearly_price','permission_module','description','status','status']));
        $data[] = [
            'plans'=>$plans,
            'avatar'=>Storage::url($plans->avatar),
            'status'=>200,
          ];
      
          
          return $this->sendResponse($data, 'update Plans successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function deletePlans($id)
    {
        if (!empty($id)) {
            $plan = Plan::find($id);

            if ($plan) {
                $plan->delete();
                
                return $this->sendResponse($plan, 'Plan deleted successfully.');
            } else {
                return $this->sendError('Plan not found.', ['error' => 'Plan not found']);
            }
        } else {
            return $this->sendError('Invalid ID.', ['error' => 'Invalid ID']);
        }
    }

}
