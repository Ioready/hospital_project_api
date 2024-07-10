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
            'card_label' => 'required|string|max:255',
            'card_title' => 'required|string|max:255',
            'title_description' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'exclusive_and_including_tax' => 'required|string|max:255',
            'text_area' => 'required|string|max:255',
            'button_name' => 'required|string|max:255',
            'button_link' => 'required|string|max:255',
            'feature_title' => 'required|string|max:255',
            'feature_list' => 'required|string|max:255',
            'user_permission' => 'required|string|max:255',
            'permission_by_module' => 'required|string|max:255',
            // 'images' => 'required|max:255',
            'status' => 'required|string|max:255',
           
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        



        $plans = Plan::create([
            'card_label' => $request->card_label,
            'card_title' => $request->input('card_title'),
            'title_description' => $request->input('title_description'),
            'price' => $request->input('price'),
            'type' => $request->input('type'),
            'exclusive_and_including_tax' => $request->input('exclusive_and_including_tax'),
            'tax_name'=> $request->input('tax_name'),
            'tax_percentage'=> $request->input('tax_percentage'),
            'text_area' => $request->input('text_area'),
            'button_name' => $request->input('button_name'),
            'button_link' => $request->input('button_link'),
            'feature_title' => $request->input('feature_title'),
            'feature_title' => $request->input('feature_title'),
            'feature_list' => $request->input('feature_list'),
            'user_permission' => $request->input('user_permission'),
            'permission_by_module' => $request->input('permission_by_module'),
            'images' => '',
            'status' => $request->input('status'),
            
           
        ]);

        // if ($plans->images) {
        //     Storage::disk('public')->delete($user->images);
        // }

        // $path = $request->file('images')->store('images', 'public');
        // $plans->images = $path;
        // $plans->save();

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

    public function showPlans($id){
        if(!empty($id)){
            $plan = Plan::find($id);
       
        $response = [
            'data' => $plan,
            'status'=>200,
        ];

        return $this->sendResponse($response, 'User show plans successfully.');
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
            'card_label' => 'required|string|max:255',
            'card_title' => 'required|string|max:255',
            'title_description' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'exclusive_and_including_tax' => 'required|string|max:255',
            'text_area' => 'required|string|max:255',
            'button_name' => 'required|string|max:255',
            'button_link' => 'required|string|max:255',
            'feature_title' => 'required|string|max:255',
            'feature_list' => 'required|string|max:255',
            'user_permission' => 'required|string|max:255',
            'permission_by_module' => 'required|string|max:255',
            // 'images' => 'required|max:255',
            'status' => 'required|string|max:255',
        
            // Add other fields as necessary
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $plans = Plan::find($id);
        // if ($plans->images) {
        //     Storage::disk('public')->delete($user->images);
        // }

        // $path = $request->file('images')->store('images', 'public');
        // $plans->images = $path;
        // $plans->save();

        $plans->update($request->only(['card_label', 'card_title','title_description','price','type','exclusive_and_including_tax','text_area','button_name','button_link','feature_title','feature_list','user_permission','permission_by_module','status']));
        $data[] = [
            'plans'=>$plans,
            // 'avatar'=>Storage::url($plans->avatar),
            'status'=>200,
          ];
      
          
          return $this->sendResponse($data, 'update Plans successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }


    public function activePlans(){
        $user = Auth::user();
        if(!empty($user)){

            $plan = Plan::where('status',0)->get();
       
        $response = [
            'data' => $plan,
            'status'=>200,
        ];

        return $this->sendResponse($response, 'Active plans List.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }

    public function inactivePlans(){
        $user = Auth::user();
        if(!empty($user)){

            $plan = Plan::where('status',1)->get();
       
        $response = [
            'data' => $plan,
            'status'=>200,
        ];

        return $this->sendResponse($response, 'Inactive plans List.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }

    public function statusUpdatePlans($id, Request $request){


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
            $plan = Plan::find($id);
            if (!$plan) {
                return response()->json(['error' => 'plan not found'], 404);
            }

            // Update the hospital status
            $plan->update(['status' => $request->status]);

           

            return $this->sendResponse($plan, 'plan status updated successfully.');
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }

    } else {
        return $this->sendError('Unauthorized.', ['error' => 'Unauthorized']);
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
