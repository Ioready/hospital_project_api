<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Email;

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
