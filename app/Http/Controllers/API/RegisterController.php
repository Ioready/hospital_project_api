<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\Utility;

class RegisterController extends BaseController

{
    /**
    * Register api
    *
    * @return \Illuminate\Http\Response
    */

    /** get all users */
    public function index()
    {
        $users = User::all()->cacheFor(now()->addMinutes(5))->get();
        return $this->sendResponse($users, 'Displaying all users data');
    }

    public function register(Request $request)
    {
       
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required'],
            'c_password' => 'required|same:password',
            'role_id' => ['required', Rule::in(Role::ROLE_SUPERADMIN,Role::ADMIN, Role::HOSPITAL, Role::DOCTOR, Role::NURSES, Role::ACCOUNTANT, Role::STAFF, Role::EMPLOYEE, Role::PATIENT)],
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            // 'module_permission' => 'nullable|string',
        ]);
 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            // 'module_permission' => json_encode($request->module_permission),
        ]);

        $token = csrf_token();

         $usersData = ([
            'csrf_token' =>$token,
            'access_token' => $user->createToken('client')->plainTextToken,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => $request->role_id,
        ]);

        $response = [
            'message'=>'User register successfully.',
            'data' => $user,
            'status' => 200,
        ];

        
        return $this->sendResponse($user, 'User register successfully.');
    }


    public function updatePassword(Request $request)
    {

        if (Auth::Check()) {

            $validator = \Validator::make(
                $request->all(), [
                    'old_password' => 'required',
                    'password' => 'required|min:6',
                    'password_confirmation' => 'required|same:password',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                
                return response()->json($messages->first(), 422);
                // return redirect()->back()->with('error', $messages->first());
            }

            $objUser = Auth::user();
            $request_data = $request->All();
            $current_password = $objUser->password;
            if (Hash::check($request_data['old_password'], $current_password)) {
                $user_id = Auth::User()->id;
                $obj_user = User::find($user_id);
                $obj_user->password = Hash::make($request_data['password']);
                $obj_user->save();

                return $this->sendResponse($objUser, 'Password successfully updated.');
            } else {

                return $this->sendResponse($objUser, 'Please enter correct current password.');
            }
        } else {
            return $this->sendResponse( \Auth::user()->id, 'Something is wrong.');
            
        }
    }

    public function login(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required|string',
        // ]);


        $validator = \Validator::make(
            $request->all(), [
                'email' => 'required|string|email',
            'password' => 'required|string',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            
            return response()->json($messages->first(), 422);
            // return redirect()->back()->with('error', $messages->first());
        }

        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);
        
        if (!$token) {
            return response()->json([
                'message' => 'please enter currect email and password.',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }


    
    
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
