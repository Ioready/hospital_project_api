<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ReferralSetting;
use App\Models\ReferralTransaction;
use App\Models\User;
use App\Models\TransactionOrder;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;

class ReferralProgramController extends BaseController
{
    public function index()
    {
        $user = Auth::user();
        if(!empty($user)){
        $setting = ReferralSetting::where('created_by',\Auth::user()->id)->first();
        $payRequests = TransactionOrder::where('status' , 1)->get();

        $transactions = ReferralTransaction::get();
        $data[] = [
            'transactions'=>$transactions,
            'payRequests'=>$payRequests,
            'status'=>200,
          ];
        return $this->sendResponse($data, 'All Referral Program successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 

        
    }

    public function store(Request $request)
    {

        $user = Auth::user();
        if(!empty($user)){

        $validator = \Validator::make(
            $request->all(), [

                               'percentage' => 'required',
                               'minimum_threshold_amount' => 'required',
                               'guideline' => 'required',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if($request->has('is_enable') && $request->is_enable == 'on')
        {
            $is_enable = 1;
        }
        else
        {
            $is_enable = 0;
        }
        
        $setting = ReferralSetting::where('created_by' , \Auth::user()->id)->first();

        if($setting == null)
        {
            $setting = new ReferralSetting();
        }
        $setting->percentage = $request->percentage;
        $setting->minimum_threshold_amount = $request->minimum_threshold_amount;
        $setting->is_enable  = $is_enable;
        $setting->guideline = $request->guideline;
        $setting->created_by = \Auth::user()->creatOrId();
        $setting->save();
        return $this->sendResponse($setting, 'Referral Program Setting successfully Updated.');
    } else { 
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    } 

       
    }

    public function hospitalIndex()
    {
        $user = Auth::user();
        if(!empty($user)){

        $setting = ReferralSetting::where('created_by',1)->first();

        $objUser = \Auth::user();

        $transactions = ReferralTransaction::where('referral_code' , $objUser->referral_code)->get();

        $transactionsOrder = TransactionOrder::where('req_user_id',$objUser->id)->get();
        $paidAmount = $transactionsOrder->where('status' , 2)->sum('req_amount');

        $paymentRequest = TransactionOrder::where('status' , 1)->where('req_user_id',$objUser->id)->first();

        $data[] = [
            'paymentRequest'=>$paymentRequest,
            'paidAmount'=>$paidAmount,
            'transactionsOrder'=>$transactionsOrder,
            'transactions'=>$transactions,
            'status'=>200,
          ];
        return $this->sendResponse($data, 'All Referral Program successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 

        
    }

    public function requestedAmountSent($id)
    {
        $user = Auth::user();
        if(!empty($user)){

        // $id  = \Illuminate\Support\Facades\Crypt::decrypt($id);
        $paidAmount = TransactionOrder::where('req_user_id',\Auth::user()->id)->where('status' , 1)->sum('req_amount');
        $user = User::find(\Auth::user()->id);

        $netAmount = $user->commission_amount - $paidAmount;

        return $this->sendResponse($netAmount, 'Request Send Successfully.');
    } else { 
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    }

        // return view('referral-program.request_amount' , compact('id' , 'netAmount'));
    }

    public function requestCancel($id)
    {
        if(!empty($id)){
        $transaction = TransactionOrder::where('req_user_id',$id)->orderBy('id','desc')->first();
        // $transaction->status = 0;
        // $transaction->req_user_id = \Auth::user()->id;
        $transaction->delete();

        return $this->sendResponse($transaction, 'Request Cancel Successfully.');
    } else { 
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    }

        
    }

    public function requestedAmountStore(Request $request)
    {
        $user = Auth::user();
        if(!empty($user)){

        $order = new TransactionOrder();
        $order->req_amount =  $request->request_amount;
        $order->req_user_id = \Auth::user()->id;
        $order->status = 1;
        $order->date = date('Y-m-d');
        $order->save();

        return $this->sendResponse($order, 'Request Send Successfully.');
    } else { 
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    }
    
    }

    public function requestedAmount($id , $status)
    {

        $setting = ReferralSetting::where('created_by',1)->first();

        $transaction = TransactionOrder::find($id);

        $paidAmount = TransactionOrder::where('req_user_id',$transaction->req_user_id)->where('status' , 2)->sum('req_amount');
        $user = User::find($transaction->req_user_id);

        $netAmount = $user->commission_amount - $paidAmount;

        $minAmount = isset($setting) ? $setting->minimum_threshold_amount : 0;
        if($status == 0)
        {
            $transaction->status = 0;

            $transaction->save();
            return $this->sendResponse($transaction, 'Request Rejected Successfully.');
            // return redirect()->route('referral-program.index')->with('error', __('Request Rejected Successfully.'));
        }
        elseif($transaction->req_amount > $netAmount)
        {
            $transaction->status = 0;

            $transaction->save();
            return $this->sendResponse($transaction, 'This request cannot be accepted because it exceeds the commission amount.');
            // return redirect()->route('referral-program.index')->with('error', __('This request cannot be accepted because it exceeds the commission amount.'));
        }
        elseif($transaction->req_amount < $minAmount)
        {
            $transaction->status = 0;

            $transaction->save();
            return $this->sendResponse($transaction, 'This request cannot be accepted because it less than the threshold amount.');
            // return redirect()->route('referral-program.index')->with('error', __('This request cannot be accepted because it less than the threshold amount.'));
        }
        else
        {
            $transaction->status = 2;

            $transaction->save();
            return $this->sendResponse($transaction, 'Request Aceepted Successfully.');
            // return redirect()->route('referral-program.index')->with('success', __('Request Aceepted Successfully.'));
        }        
    }
}
