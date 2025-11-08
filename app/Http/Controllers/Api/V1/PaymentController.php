<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Users;
use App\Models\Customer;
use App\Models\CashierCartMaster;
use App\Models\CashierCartLineEntries;
use App\Models\OrderMaster;
use App\Models\OrderLineEntries;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use Stripe;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
	public function __construct(GlobalModel $model, ApiModel $apimodel)
	{
		$this->model = $model;
		$this->apimodel = $apimodel;
	}

	public function verify(Request $r)
	{
		$orderCode = $r->orderCode;
		$sessionId = $r->sessionId;

		$stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $stripeSession = $stripe->checkout->sessions->retrieve($sessionId,[]);

        Log::info("STRIPE SESSION DATA - ORDER-AMOUNT-PAID" .json_encode($stripeSession));

        if(isset($stripeSession->id)) {
            $stripeSessionId = $stripeSession->id;
            $payment_status = $stripeSession->payment_status;
            $result=DB::table("ordermaster")->select("ordermaster.*")->where("stripesessionid",$stripeSessionId)->first();
    	    if(!empty($result)){
    	        $orderStatus = $result->orderStatus;
    			if ($result->orderStatus === 'pending') {
    				if($payment_status=="paid") {
    					$data['paymentOrderId'] = $stripeSession->payment_intent;
                        $data['orderStatus'] = 'placed';
                        $data['paymentStatus'] = "paid";
                        $data['webHookResponse'] = json_encode($stripeSession);
                        $data['paymentOrderId'] = $stripeSession->payment_intent;
                        $orderStatus = "placed";
                        $this->model->doEdit($data, 'ordermaster', $result->code);

                        $msg = "Payment has been verified and your order has been placed successfully";
    				    $err = 300;

    				} else if($payment_status=="unpaid") {
    				    $msg = "Payment is unpaid, so cannot place ypur order at the time. Please pay the amount to place the order or make another order and pay the amount..";
    				    $err = 300;
    				}
    			} else if($result->orderStatus === 'shipped') {
    			     $msg = "Your Order is shipped and will be delivered soon";
    			     $err = 200;
    			} else if($result->orderStatus === 'cancel') {
    			     $msg = "Your order is cancelled successfully";
    			     $err = 200;
    			} else {
    			     $msg = "Your order is delivered to your address successfully";
    			     $err = 200;
    			}

    			$data = [
    			    "orderStatus" => ucwords($orderStatus),
    			    "txnStatus" => ucwords($stripeSession->payment_status),
    			    "txnId" => $result->txnId,
    			    "txnDate" => date('d/m/Y h:i A',strtotime($result->created_at)),
    			    "amount"  => $result->grandTotal
    			];

    			return response()->json(["err"=>$err,"message" => $msg, "data"=>$data], 200);
        	} else {
        	    return response()->json(["message" => "Failed to place the order at the moment. Please visit My-Account page and navigate to Orders page to check the latest status"], 400);
        	}
        } else {
            return response()->json(["message" => "Failed verify the payment at the moment. Please visit My-Account page and navigate to Orders page to check the latest status"], 400);
        }
	}

	public function cancel(Request $r)
	{
		$orderCode = $r->orderCode;
		$sessionId = $r->sessionId;

		$stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $stripeSession = $stripe->checkout->sessions->retrieve($sessionId,[]);

        Log::info("STRIPE SESSION DATA - ORDER-CANCELLED" .json_encode($stripeSession));

        if(isset($stripeSession->id)) {
            $stripeSessionId = $stripeSession->id;
            $payment_status = $stripeSession->payment_status;
            $result=DB::table("ordermaster")->select("ordermaster.*")->where("stripesessionid",$stripeSessionId)->first();
            if(!empty($result)){
             	$data['paymentOrderId'] = $stripeSession->payment_intent;
                $data['orderStatus'] = 'cancel';
                $data['paymentStatus'] = "cancel";
                $data['webHookResponse'] = json_encode($stripeSession);
                $data['paymentOrderId'] = $stripeSession->payment_intent;
                $orderStatus = "placed";
                $this->model->doEdit($data, 'ordermaster', $result->code);
            }
        }


	    return response()->json(["message" => "Order cancelled successfully"], 200);
	}

}