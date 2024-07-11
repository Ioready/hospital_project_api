<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\Utility;
use Illuminate\Auth\Events\PasswordReset;
use Mail;
use App\Mail\TestMail;
use App\Mail\OTPMail;
use Illuminate\Support\Str;

// use Illuminate\Support\Facades\PasswordRule;

use Illuminate\Support\Facades\PasswordRule;

use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Validation\Rules;
use DB;


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
                'message' => 'Please enter currect email and password.',
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

    public function forgot(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::where('email', $request->email)->first();
        if(!empty($user)){

        $otp = Str::random(6);
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10); // OTP valid for 10 minutes
        $user->save();

        // Send OTP via email
        $mail  =  Mail::to($user->email)->send(new OTPMail($otp));

        return $this->sendResponse($request->email, 'OTP sent to your email address.');
        } else {
        return $this->sendResponse($request->email, 'Please enter correct current email.');
        }
     

        // $request->validate(['email' => 'required|email|exists:users,email']);

        // $status = Password::sendResetLink(
        //     $request->only('email')
        // );
        
        // return $status === Password::RESET_LINK_SENT
        //     ? response()->json(['message' => 'Password reset link sent to your email.'])
        //     : response()->json(['message' => 'Unable to send password reset link.'], 500);
   

    }

    public function confirmOtp(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }
   
    // Find the user by email
    $user = User::where('email', $request->email)->first();
    if (!empty($user)) {
        // Check if OTP is valid and not expired
        if ($user->otp !== $request->otp || now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

       
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        $token = $user->createToken('Personal Access Token')->plainTextToken;

        // $tokenResult = DB::table('password_reset_tokens')->where('email',$request->email)->orderBy('created_at','desc')->first();
        // $tokenResult = $user->createToken('Personal Access Token');
        // $token = $tokenResult->accessToken;

      
        return response()->json([
            'authorization' => [
                'email' => $request->email,
                'token' => $token,
                'type' => 'bearer',
                // 'expires_at' => $token->expires_at->toDateTimeString()
            ]], 200);
    } else {
        return response()->json(['message' => 'Please enter the correct current email.'], 422);
    }
}

public function reset(Request $request)
{
    $validator = Validator::make($request->all(), [
        // 'token' => 'required|string',
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:6',
        'password_confirmation' => 'required|same:password',
        
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Attempt to reset the password
    // $status = Password::reset(
    //     $request->only('email', 'password', 'password_confirmation', 'token'),
    //     function ($user, $password) {
    //         $user->forceFill([
    //             'password' => Hash::make($password),
    //             'remember_token' => Str::random(60),
    //         ])->save();

    //         event(new PasswordReset($user));
    //     }
    // );

    
        
    $user = User::where('email', $request->email)->first();
    if(!empty($user)){
    $user->password = Hash::make($request->password);
    $user->save();

    return $this->sendResponse($request->email, 'Password has been reset successfully');
    } else {

        return $this->sendResponse($request->email, 'Please enter correct current email.');
    }


    // return $status === Password::PASSWORD_RESET
    //     ? response()->json(['message' => 'Password has been reset successfully.'])
    //     : response()->json(['message' => 'Failed to reset password.'], 500);
}


    public function getTokenResetPassword()
    {
      

        return response()->json([
            'email' => '$email',
            'authorization' => [
                'token' => '$token',
                'type' => 'bearer',
            ]
        ]);
    }

//     public function reset(Request $request) {
//     $request->validate([
//         'token' => 'required',
//         'email' => 'required|email',
//         'password' => ['required', 'confirmed', Rules\Password::defaults()],
//     ]);

//     $status = Password::reset(
//         $request->only('email', 'password', 'password_confirmation', 'token'),
//         function ($user) use ($request) {
//             $user->forceFill([
//                 'password' => Hash::make($request->password),
//                 'remember_token' => Str::random(60),
//             ])->save();

//             event(new PasswordReset($user));
//         }
//     );


//     return $status == Password::PASSWORD_RESET
//                 ? redirect()->route('login')->with('status', __($status))
//                 : back()->withInput($request->only('email'))
//                         ->withErrors(['email' => __($status)]);
// }

    
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
