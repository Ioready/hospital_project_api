<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Carbon\Carbon;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Str;

class CouponController extends BaseController
{
    //
    public function index(){
        $coupon = Coupon::all();

        if(!empty($coupon)){

            return $this->sendResponse($coupon, 'all coupon List.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }

    public function create(Request $request)
    {
        $codes = Str::random(8);

        $request->validate([
            // 'code' => 'required|unique:coupons',
            'discount' => 'required|numeric',
            'expiry_date' => 'required|date',
            'limit' => 'required|numeric',
        ]);

        $coupon = Coupon::create([
            'code' => $codes,
            'discount' => $request->discount,
            'expiry_date' => $request->expiry_date,
            'limit'=>$request->limit,
        ]);

        return response()->json(['coupon' => $coupon, 'message' => 'Coupon created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code,' . $id,
            'discount' => 'required|numeric',
            'expiry_date' => 'required|date',
            'limit' => 'required|numeric',
        ]);

        $coupon = Coupon::findOrFail($id);
        $coupon->update([
            'code' => $request->code,
            'discount' => $request->discount,
            'expiry_date' => $request->expiry_date,
            'limit'=>$request->limit,
        ]);

        return response()->json(['coupon' => $coupon, 'message' => 'Coupon updated successfully'], 200);
    }

    public function validateCoupon($code)
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid coupon code'], 404);
        }

        if (Carbon::now()->greaterThan($coupon->expiry_date)) {
            return response()->json(['message' => 'Coupon has expired'], 400);
        }

        return response()->json(['coupon' => $coupon, 'message' => 'Coupon is valid'], 200);
    }

    public function delete($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully'], 200);
    }

    public function editCoupon($id){
        
        $coupon =  Coupon::findOrFail($id);

        if(!empty($coupon)){

            return $this->sendResponse($coupon, 'edit coupon successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

    }

    public function show(Coupon $coupon)
    {
        $userCoupons = UserCoupon::where('coupon', $coupon->id)->with('userDetail')->get();

        return view('coupon.view', compact('userCoupons'));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'order_id' => 'required|exists:orders,id',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid coupon code'], 404);
        }

        if (Carbon::now()->greaterThan($coupon->expiry_date)) {
            return response()->json(['message' => 'Coupon has expired'], 400);
        }

        $order = Order::findOrFail($request->order_id);

        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Order does not belong to the authenticated user'], 403);
        }

        $discountAmount = $coupon->discount;
        $finalAmount = $order->total_amount - $discountAmount;

        $order->update([
            'coupon_id' => $coupon->id,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
        ]);

        return response()->json([
            'order' => $order,
            'message' => 'Coupon applied successfully',
        ], 200);
    }

}
