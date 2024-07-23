<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Email;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

class EmailController extends BaseController
{
    //

    public function index(){

        $email = Email::all();
        if(!empty($email)){

            return $this->sendResponse($email, 'all email List.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function newEmail(Request $request){

        $user = Auth::user();
        if(!empty($user)){

        $validator = validator::make($request->all(), [
            
            'from' =>'required',
            'subject' => 'required',
            'message'=>'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $email = Email::create([
            'from' => $request->from,
            'subject' => $request->subject,
            'message' =>$request->message,
        ]);

        return $this->sendResponse($email, 'new email successfully.');
    } else { 
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    }
    }

    public function sendEmail(Request $request){

        $user = Auth::user();
        if(!empty($user)){

        $validator = validator::make($request->all(), [
            'to'=>'required',
            'from' =>'required',
            'subject' => 'required',
            'message'=>'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $email = Email::create([
            'to' => $request->to,
            'from' => $request->from,
            'subject' => $request->subject,
            'message' =>$request->message,
        ]);

        // $otp = Str::random(6);
        // $otp = rand(100000, 999999);
        // $user->otp = $otp;
        // $user->otp_expires_at = now()->addMinutes(10); // OTP valid for 10 minutes
        // $user->save();

        // Send OTP via email
        $mail  =  Mail::to($request->to)->send(new SendMail($request->message));

        return $this->sendResponse($email, 'new email successfully.');
    } else { 
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    }
    }

    public function editEmail($id){

        $email = Email::find($id);
        if(!empty($email)){

            return $this->sendResponse($email, 'Edit email successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'from' =>'required',
            'subject' => 'required',
            'message'=>'required',
        ]);

        $email = Email::findOrFail($id);
        $email->update([
            'from' => $request->from,
            'subject' => $request->subject,
            'message' =>$request->message,
        ]);

        return response()->json(['email' => $email, 'message' => 'email updated successfully'], 200);
    }

    public function showEmail($id){

        $email = Email::find($id);
        if(!empty($email)){

            return $this->sendResponse($email, 'Show email successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function deleteEmail($id){

        $email = Email::find($id);
        if(!empty($email)){
           $delete= $email->delete();
            return $this->sendResponse($delete, 'delete email successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }


}
