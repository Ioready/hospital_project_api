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
        $users = User::all();
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
            'module_permission' => 'nullable|string',
        ]);
 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'module_permission' => json_encode($request->module_permission),
        ]);

        $token = csrf_token();

         $user = ([
            'csrf_token' =>$token,
            'access_token' => $user->createToken('client')->plainTextToken,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => $request->role_id,
        ]);

        // $user['csrf_token'] = $token;

        //     // Add the Sanctum token to the user data
        // $user['sanctum_token'] = $user->createToken('MyApp')->plainTextToken;

        $response = [
            'data' => $user,
            'status' => 200,
        ];

        
        return $this->sendResponse($response, 'User register successfully.');
    }

    /**
    * Login api
    *
    * @return \Illuminate\Http\Response
    */

    public function login(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            
            // Generate CSRF token
            $token = csrf_token();

            // Add CSRF token to user data
            $user['csrf_token'] = $token;

            // Add the Sanctum token to the user data
            $user['sanctum_token'] = $user->createToken('MyApp')->plainTextToken;

            // Prepare response data
            $response = [
                'data' => $user,
                'status' => 200,
            ];


            return $this->sendResponse($user, 'User login successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 

        //     return response()->json($response, 200);
        // } else {
        //     return response()->json(['error' => 'Unauthorised'], 401);
        // }
    }

    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete(); // Invalidates all tokens for the user

            return $this->sendResponse(null, 'User logged out successfully.');
        } else {
            return $this->sendError('User not authenticated.', ['error' => 'User not authenticated']);
        }
    }
}
