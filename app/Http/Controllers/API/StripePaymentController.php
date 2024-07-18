<?php

namespace  App\Http\Controllers\API;


use App\Models\Coupon;
// use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Session;
use Stripe;
// use Customer;
use Validator;
// use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use Stripe\Token;
// use Stripe;
// use Charge;
use App\Helper\Helpers;

use App\Http\Controllers\API\BaseController as BaseController;

class StripePaymentController extends BaseController
{
    public $settings;

    public function index()
    {
        $objUser = \Auth::user();
        if(!empty($objUser)){    

            $orders = Order::select([
                                        'orders.*',
                                        'users.name as user_name',
                                    ])->join('users', 'orders.user_id', '=', 'users.id')->orderBy('orders.created_at', 'DESC')->with('total_coupon_used.coupon_detail')->with(['total_coupon_used.coupon_detail'])->with(['plan'])->get();

            $userOrders = Order::select('*')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('orders')
                    ->groupBy('user_id');
            })
            ->orderBy('created_at', 'desc')
            ->get();        
                

            // $orders = Order::select([
            //                             'orders.*',
            //                             'users.name as user_name',
            //                         ])->join('users', 'orders.user_id', '=', 'users.id')->orderBy('orders.created_at', 'DESC')->where('users.id', '=', $objUser->id)->with('total_coupon_used.coupon_detail')->with(['total_coupon_used.coupon_detail'])->get();

            return $this->sendResponse($orders, 'Order list.');
            } else { 
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
           
        }
    }

    public function refund(Request $request , $id , $user_id)
    {
        Order::where('id', $request->id)->update(['is_refund' => 1]);

        $user = User::find($user_id);

        $assignPlan = $user->assignPlan(1);

        return redirect()->back()->with('success' , __('We successfully planned a refund and assigned a free plan.'));
    }

    public function stripe($code)
    {

        try {
            $plan_id       = \Illuminate\Support\Facades\Crypt::decrypt($code);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Plan Not Found.'));
        }
        $plan_id               = \Illuminate\Support\Facades\Crypt::decrypt($code);
        $plan                  = Plan::find($plan_id);
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        if($plan)
        {
            return view('stripe', compact('plan', 'admin_payment_setting'));
        }
        else
        {
            return redirect()->back()->with('error', __('Plan is deleted.'));
        }
    }

    public function buyNowStripe(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|numeric',
           
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $objUser = \Auth::user();

        $data = \DB::table('admin_payment_settings');

        $planID  = $request->plan_id;
        $plan    = Plan::find($planID);
        
        $price = $plan->price;

        if($plan->type == 'month'){
            $date = date('Y-m-d',strtotime('+1 months'));
           
           }else if($plan->type == 'year'){
            $date = date('Y-m-d',strtotime('+1 years'));
        }else{
            $date = date('Y-m-d');
        }
       

        if(!empty($request->coupon))
        {
            $coupons = Coupon::where('code', strtoupper($request->coupon))->where('status', 'active')->first();
            if(!empty($coupons))
            {
                $usedCoupun     = $coupons->used_coupon();
                $discount_value = ($plan->price / 100) * $coupons->discount;
                $price          = $plan->price - $discount_value;
          
               $price = (number_format($price, 0, '.', ''));

                if($coupons->limit == $usedCoupun)
                {
                   
                    return $this->sendError('This coupon code has expired.');
                }
            }
            else
            {
                return $this->sendError('This coupon code is invalid or has expired.');
                
            }
        }

        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        $admin_payment_setting = [];
        if (\Auth::check()) {

            $user_id = 1;
            

        }
        $data = $data->get();
        foreach ($data as $row) {
            $admin_payment_setting[$row->name] = $row->value;
        }
        // Set Stripe API key
        // Stripe::setApiKey('sk_test_afm5UcS9SFFjYgSs5hTWIG7Y00G5E2b2Zx');
     $stripe =   Stripe\Stripe::setApiKey($admin_payment_setting['stripe_secret']);
       
        try {
        //  $stripe =   Stripe\Stripe::setApiKey($admin_payment_setting['stripe_secret']);
      
                $stripe = new \Stripe\StripeClient('sk_test_afm5UcS9SFFjYgSs5hTWIG7Y00G5E2b2Zx');

                $response = $stripe->checkout->sessions->create([
                'line_items' => [
                    [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $plan->card_title],
                        'unit_amount' => $price,
                    ],
                    'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => route('success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
                ]);

                


          

                
                // try {
                    // $paymentIntent = \Stripe\PaymentIntent::create([
                    //     'amount' => $price * 100,
                    //     'currency' => 'usd',
                    //     'payment_method' => $request->payment_method_id,
                    //     'confirmation_method' => 'manual',
                    //     'confirm' => true,
                    // ]);
            
                    // if ($paymentIntent->status == 'requires_action' || $paymentIntent->status == 'requires_source_action') {
                    //     return response()->json(['requires_action' => true, 'payment_intent_client_secret' => $paymentIntent->client_secret], 200);
                    // } else if ($paymentIntent->status == 'succeeded') {
                    //     Order::create([
                    //         'order_id' => $paymentIntent->id,
                    //         'name' => $user->name,
                    //         'card_number' => $paymentIntent->charges->data[0]->payment_method_details->card->last4,
                    //         'card_exp_month' => $paymentIntent->charges->data[0]->payment_method_details->card->exp_month,
                    //         'card_exp_year' => $paymentIntent->charges->data[0]->payment_method_details->card->exp_year,
                    //         'plan_name' => $plan->card_title,
                    //         'plan_id' => $plan->id,
                    //         'price' => $price,
                    //         'price_currency' => 'usd',
                    //         'txn_id' => $paymentIntent->charges->data[0]->balance_transaction,
                    //         'payment_type' => 'STRIPE',
                    //         'payment_status' => 'succeeded',
                    //         'receipt' => $paymentIntent->charges->data[0]->receipt_url,
                    //         'user_id' => $user->id,
                    //     ]);
                    // }


                //         return response()->json(['message' => 'Payment successful'], 200);
                //     } else {
                //         return response()->json(['error' => 'Payment failed'], 500);
                //     }
                // } catch (\Exception $e) {
                //     return response()->json(['error' => $e->getMessage()], 500);
                // }


                if(isset($response->id) && $response->id !='' ){


                // $customer = Customer::create([
                //     'email' => $objUser->email,
                //     'source' => $response->url,
                // ]);
    
                $charge = Charge::create([
                    'customer' => 'cus_QPbB60IKzibz0y',
                    'amount' => $price,
                    'currency' => 'usd',
                    'description' => $plan->card_title,
                    'metadata' => ['order_id' => $orderID],
                ]);
    
                Order::create([
                    'order_id' => $orderID,
                    'name' => $objUser->name,
                    'email' => $objUser->name,
                    'card_number' => $charge->payment_method_details->card->last4,
                    'card_exp_month' => $charge->payment_method_details->card->exp_month,
                    'card_exp_year' => $charge->payment_method_details->card->exp_year,
                    'plan_name' => $plan->card_title,
                    'plan_id' => $plan->id,
                    'price' => $price,
                    'price_currency' => $charge->currency,
                    'txn_id' => $charge->balance_transaction,
                    'payment_type' => 'STRIPE',
                    'payment_status' => $charge->status,
                    'receipt' => $charge->receipt_url,
                    'user_id' => $objUser->id,
                ]);

                if(!empty($request->coupon))
                    {

                        $userCoupon         = new UserCoupon();
                        $userCoupon->user   = $objUser->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order  = $orderID;
                        $userCoupon->save();

                        $usedCoupun = $coupons->used_coupon();
                        if($coupons->limit <= $usedCoupun)
                        {
                            $coupons->status = 'active';
                            $coupons->save();
                        }
                    }

                    
                    User::where('id',$objUser->id)->update(['plan'=>$plan->id,'plan_expire_date'=>$date]);

                    session()->put('product_name',$request->product_name);
                    // session()->put('plan_id',$request->plan_id);
                    session()->put('response',$response);



                    return response()->json(['url' => $response], 200);
                }else{

                    return response()->json(route('cancel'));
                }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

//     public function buyNowStripe(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'plan_id' => 'required|numeric',
//         'payment_method_id' => 'required|string',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => $validator->errors()], 422);
//     }

//     $objUser = \Auth::user();

//         $data = \DB::table('admin_payment_settings');

//         $admin_payment_setting = [];
//         if (\Auth::check()) {

//             $user_id = 1;
            

//         }
//         $data = $data->get();
//         foreach ($data as $row) {
//             $admin_payment_setting[$row->name] = $row->value;
//         }

//     $user = \Auth::user();
//     $plan = Plan::find($request->plan_id);
//     $price = $plan->price;

//     if (!empty($request->coupon)) {
//         $coupon = Coupon::where('code', strtoupper($request->coupon))->where('status', 'active')->first();
//         if ($coupon) {
//             $usedCoupon = $coupon->used_coupon();
//             $discountValue = ($plan->price / 100) * $coupon->discount;
//             $price = $plan->price - $discountValue;
//             $price = (number_format($price, 0, '.', ''));
//             if ($coupon->limit == $usedCoupon) {
//                 return $this->sendError('This coupon code has expired.');
//             }
//         } else {
//             return $this->sendError('This coupon code is invalid or has expired.');
//         }
//     }

//     $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
//     Stripe\Stripe::setApiKey($admin_payment_setting['stripe_secret']);

//     try {
//         $paymentIntent = \Stripe\PaymentIntent::create([
//             'amount' => $price * 100,
//             'currency' => 'usd',
//             'payment_method' => $request->payment_method_id,
//             'confirmation_method' => 'manual',
//             'confirm' => true,
//         ]);

//         if ($paymentIntent->status == 'requires_action' || $paymentIntent->status == 'requires_source_action') {
//             return response()->json(['requires_action' => true, 'payment_intent_client_secret' => $paymentIntent->client_secret], 200);
//         } else if ($paymentIntent->status == 'succeeded') {
//             Order::create([
//                 'order_id' => $paymentIntent->id,
//                 'name' => $user->name,
//                 'card_number' => $paymentIntent->charges->data[0]->payment_method_details->card->last4,
//                 'card_exp_month' => $paymentIntent->charges->data[0]->payment_method_details->card->exp_month,
//                 'card_exp_year' => $paymentIntent->charges->data[0]->payment_method_details->card->exp_year,
//                 'plan_name' => $plan->card_title,
//                 'plan_id' => $plan->id,
//                 'price' => $price,
//                 'price_currency' => 'usd',
//                 'txn_id' => $paymentIntent->charges->data[0]->balance_transaction,
//                 'payment_type' => 'STRIPE',
//                 'payment_status' => 'succeeded',
//                 'receipt' => $paymentIntent->charges->data[0]->receipt_url,
//                 'user_id' => $user->id,
//             ]);

//             return response()->json(['message' => 'Payment successful'], 200);
//         } else {
//             return response()->json(['error' => 'Payment failed'], 500);
//         }
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }


    public function success(Request $request)
    {

        if(isset($request->session_id)){

            // $stripe = new \Stripe\StripeClient('sk_test_afm5UcS9SFFjYgSs5hTWIG7Y00G5E2b2Zx');
            $stripe = new \Stripe\StripeClient('sk_test_afm5UcS9SFFjYgSs5hTWIG7Y00G5E2b2Zx');

                // try {
                    // $response = $stripe->checkout->sessions->retrieve($request->session_id);

                    // $customer = $stripe->customer->sessions->retrieve($request->session_id);
     
                    $session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
                    // $card = $stripe->customers->retrieveSource('cus_123','card');
                    // $stripe = new \Stripe\StripeClient('sk_test_afm5UcS9SFFjYgSs5hTWIG7Y00G5E2b2Zx');

$card = $stripe->customers->all(['limit' => 3]);

                    // $customer = $stripe->customers->sessions->retrieve($session->email);
                    print_r($card);die;
                    // source
                    // print_r($response);die;
                // $customer = $stripe->customers->retrieve($session->customer);

                // $payment = new Payment();
                // $payment->payment_id =$response->id;


                // Order::create([
                //     'order_id' => $response->id,
                //     'name' => $request->name,
                //     'card_number' => isset($data['payment_method_details']['card']['last4']) ? $data['payment_method_details']['card']['last4'] : '',
                //     'card_exp_month' => isset($data['payment_method_details']['card']['exp_month']) ? $data['payment_method_details']['card']['exp_month'] : '',
                //     'card_exp_year' => isset($data['payment_method_details']['card']['exp_year']) ? $data['payment_method_details']['card']['exp_year'] : '',
                //     'plan_name' => $plan->name,
                //     'plan_id' => $plan->id,
                //     'price' => $price,
                //     'price_currency' => !empty($admin_payment_setting['currency']) ? $admin_payment_setting['currency']: '',
                //     'txn_id' => isset($data['balance_transaction']) ? $data['balance_transaction'] : '',
                //     'payment_type' => __('STRIPE'),
                //     'payment_status' => isset($data['status']) ? $data['status'] : 'success',
                //     'receipt' => isset($data['receipt_url']) ? $data['receipt_url'] : 'free coupon',
                //     'user_id' => $objUser->id,
                // ]);



                // $customer = $stripe->customers->retrieve($session->customer);
                print_r($stripe);die;
                // echo "<h1>Thanks for your order, $customer->name!</h1>";
                http_response_code(200);

                // } catch (Error $e) {
                // http_response_code(500);
                // echo json_encode(['error' => $e->getMessage()]);
                // }

            
            return $this->sendResponse($stripe, 'Plan successfully activated.');
        }else{
            return response()->json(route('cancel'));
        }

        $objUser = \Auth::user();
        // $planID  = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $planID  = $request->plan_id;
        // print_r($planID);die;
        $plan    = Plan::find($planID);

        $data = \DB::table('admin_payment_settings');

        $admin_payment_setting = [];
        if (\Auth::check()) {

            $user_id = 1;
            // $data = $data->where('created_by', '=', $user_id);

        }
        $data = $data->get();
        foreach ($data as $row) {
            $admin_payment_setting[$row->name] = $row->value;
        }
// $userData = Auth::user();
        return $this->sendResponse($objUser, 'Plan successfully activated.');

        if($plan)
        {

            try
            {
                $price = $plan->price;

                if(!empty($request->coupon))
                {
                    $coupons = Coupon::where('code', strtoupper($request->coupon))->where('status', 'active')->first();
                    if(!empty($coupons))
                    {
                        $usedCoupun     = $coupons->used_coupon();
                        $discount_value = ($plan->price / 100) * $coupons->discount;
                        $price          = $plan->price - $discount_value;


                        if($coupons->limit == $usedCoupun)
                        {
                           
                            return $this->sendError('This coupon code has expired.');
                        }
                    }
                    else
                    {
                        return $this->sendError('This coupon code is invalid or has expired.');
                        
                    }
                }

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                if($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1)
                {


                    Order::create([
                                      'order_id' => $orderID,
                                      'name' => $request->name,
                                      'card_number' => isset($data['payment_method_details']['card']['last4']) ? $data['payment_method_details']['card']['last4'] : '',
                                      'card_exp_month' => isset($data['payment_method_details']['card']['exp_month']) ? $data['payment_method_details']['card']['exp_month'] : '',
                                      'card_exp_year' => isset($data['payment_method_details']['card']['exp_year']) ? $data['payment_method_details']['card']['exp_year'] : '',
                                      'plan_name' => $plan->name,
                                      'plan_id' => $plan->id,
                                      'price' => $price,
                                      'price_currency' => !empty($admin_payment_setting['currency']) ? $admin_payment_setting['currency']: '',
                                      'txn_id' => isset($data['balance_transaction']) ? $data['balance_transaction'] : '',
                                      'payment_type' => __('STRIPE'),
                                      'payment_status' => isset($data['status']) ? $data['status'] : 'success',
                                      'receipt' => isset($data['receipt_url']) ? $data['receipt_url'] : 'free coupon',
                                      'user_id' => $objUser->id,
                                  ]);

                    if(!empty($request->coupon))
                    {

                        $userCoupon         = new UserCoupon();
                        $userCoupon->user   = $objUser->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order  = $orderID;
                        $userCoupon->save();

                        $usedCoupun = $coupons->used_coupon();
                        if($coupons->limit <= $usedCoupun)
                        {
                            $coupons->status = 'active';
                            $coupons->save();
                        }
                    }


                    return $this->sendResponse($orders, 'Order list.');

                    Utility::referralTransaction($plan);

                    if($data['status'] == 'succeeded')
                    {
                        $assignPlan = $objUser->assignPlan($plan->id);
                        if($assignPlan['is_success'])
                        {
                            return $this->sendResponse($assignPlan, 'Plan successfully activated.');
                            
                        }
                        else
                        {
                            return $this->sendError('Unauthorised.', ['error'=>$assignPlan['error']]);
                            
                        }
                    }
                    else
                    {
                        return $this->sendError('Unauthorised.', ['error'=>'Your payment has failed.']);
                       
                    }
                }
                else
                {
                    return $this->sendError('Unauthorised.', ['error'=>'Transaction has been failed.']);
                   
                }
            }
            catch(\Exception $e)
            {

                return $this->sendError('Unauthorised.', ['error'=>$e->getMessage()]);
                
            }
        }
        else
        {
            return $this->sendError('Unauthorised.', ['error'=>'Plan is deleted']);
            
        }
    }



    public function addPayment(Request $request, $id)
    {


        $invoice                 = Invoice::find($id);
        $hospital_payment_setting = Utility::getHospitalPaymentSetting($invoice->created_by);


        $settings  = DB::table('settings')->where('created_by', '=', $invoice->created_by)->get()->pluck('value', 'name');

     if($invoice)
        {
            if($request->amount > $invoice->getDue())
            {
                return redirect()->back()->with('error', __('Invalid amount.'));
            }
            else
            {
                try
                {

                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    $price   = $request->amount;
                    Stripe\Stripe::setApiKey($hospital_payment_setting['stripe_secret']);

                    $data = Stripe\Charge::create([
                                                      "amount" => 100 * $price,
                                                      "currency" => $settings['site_currency'],
                                                      "source" => $request->stripeToken,
                                                      "description" => 'Invoice ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                                                      "metadata" => ["order_id" => $orderID],
                                                  ]);

                    if($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1)
                    {
                        $payments = InvoicePayment::create([

                                                               'invoice_id' => $invoice->id,
                                                               'date' => date('Y-m-d'),
                                                               'amount' => $price,
                                                               'account_id' => 0,
                                                               'payment_method' => 0,
                                                               'order_id' => $orderID,
                                                               'currency' => $data['currency'],
                                                               'txn_id' => $data['balance_transaction'],
                                                               'payment_type' => __('STRIPE'),
                                                               'receipt' => $data['receipt_url'],
                                                               'reference' => '',
                                                               'description' => 'Invoice ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                                                           ]);

                        if($invoice->getDue() <= 0)
                        {
                            $invoice->status = 4;
                        }
                        elseif(($invoice->getDue() - $request->amount) == 0)
                        {
                            $invoice->status = 4;
                        }
                        else
                        {
                            $invoice->status = 3;
                        }
                        $invoice->save();

                        $invoicePayment              = new Transaction();
                        $invoicePayment->user_id     = $invoice->customer_id;
                        $invoicePayment->user_type   = 'Customer';
                        $invoicePayment->type        = 'STRIPE';
                        $invoicePayment->created_by  = $invoice->invoice_id;
                        $invoicePayment->payment_id  = $invoicePayment->id;
                        $invoicePayment->category    = 'Invoice';
                        $invoicePayment->amount      = $price;
                        $invoicePayment->date        = date('Y-m-d');
                        $invoicePayment->payment_id  = $payments->id;
                        $invoicePayment->description = 'Invoice ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                        $invoicePayment->account     = 0;
                        Transaction::addTransaction($invoicePayment);

                        //for customer balance update
                        Utility::updateUserBalance('customer', $invoice->customer_id, $request->amount, 'debit');

                        //For Notification
                        $setting  = Utility::settingsById($invoice->created_by);
                        $customer = Customer::find($invoice->customer_id);
                        $notificationArr = [
                            'payment_price' => $price,
                            'invoice_payment_type' =>$invoicePayment->type,
                            'customer_name' => $customer->name,
                        ];
                        //Slack Notification
                        if(isset($setting['payment_notification']) && $setting['payment_notification'] ==1)
                        {
                            Utility::send_slack_msg('new_invoice_payment', $notificationArr,$invoice->created_by);
                        }
                        //Telegram Notification
                        if(isset($setting['telegram_payment_notification']) && $setting['telegram_payment_notification'] == 1)
                        {
                            Utility::send_telegram_msg('new_invoice_payment', $notificationArr,$invoice->created_by);
                        }
                        //Twilio Notification
                        if(isset($setting['twilio_payment_notification']) && $setting['twilio_payment_notification'] ==1)
                        {
                            Utility::send_twilio_msg($customer->contact,'new_invoice_payment', $notificationArr,$invoice->created_by);
                        }
                        //webhook
                        $module ='New Invoice Payment';
                        $webhook=  Utility::webhookSetting($module,$invoice->created_by);
                        if($webhook)
                        {
                            $parameter = json_encode($invoicePayment);
                            $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                            if($status == true)
                            {
                                return redirect()->back()->with('success', __('Payment successfully added!'));
                            }
                            else
                            {
                                return redirect()->back()->with('error', __('Webhook call failed.'));
                            }
                        }

                        return redirect()->back()->with('success', __(' Payment successfully added.'));

                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Transaction has been failed.'));
                    }
                }
                catch(\Exception $e)
                {
//                    dd($e);
                    return redirect()->back()->with('error', __($e->getMessage()));
                }
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    // public function success(Request $request)
    // {
    //     $unique_id = OrderManager::gen_unique_id();
    //     $order_ids = [];
    //     foreach (CartManager::get_cart_group_ids() as $group_id) {
    //         $data = [
    //             'payment_method' => 'stripe',
    //             'order_status' => 'confirmed',
    //             'payment_status' => 'paid',
    //             'transaction_ref' => session('transaction_ref'),
    //             'order_group_id' => $unique_id,
    //             'cart_group_id' => $group_id
    //         ];
    //         $order_id = OrderManager::generate_order($data);
    //         array_push($order_ids, $order_id);
    //     }
    //     CartManager::cart_clean();
    //     if (auth('customer')->check()) {
    //         Toastr::success('Payment success.');
    //         return view('web-views.checkout-complete');
    //     }
    //     return response()->json(['message' => 'Payment succeeded'], 200);
    // }


    public function fail()
    {
        if (auth('customer')->check()) {
            Toastr::error('Payment failed.');
            return redirect('/account-order');
        }
        return response()->json(['message' => 'Payment failed'], 403);
    }

}
