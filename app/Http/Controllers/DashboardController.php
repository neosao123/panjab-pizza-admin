<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Specialoffer;
use App\Models\Users;
use App\Models\Toppings;
use App\Models\SidesMaster;
use App\Models\OrderMaster;

class DashboardController extends Controller
{
	protected $model;
	public function index()
	{
		$cashiers = Users::where("usermaster.isDelete", "=", 0)->where("role", "R_3")->count();
		$specials = Specialoffer::where("specialoffer.isDelete", "=", 0)->count();
		$customers = Customer::where("customer.isDelete", "=", 0)->count();
		$toppings = Toppings::where("toppings.isDelete", "=", 0)->count();
		$poutine = SidesMaster::where("sidemaster.isDelete", "=", 0)->where("type", "=", "poutine")->count();
		$sides = SidesMaster::where("sidemaster.isDelete", "=", 0)->where("type", "=", "side")->count();
		$subs = SidesMaster::where("sidemaster.isDelete", "=", 0)->where("type", "=", "subs")->count();
		$storeorders = OrderMaster::where("ordermaster.orderFrom", "=", "store")->count();
		$onlineorders = OrderMaster::where("ordermaster.orderFrom", "=", "online")->count();
		return view('dashboard', compact('cashiers', 'specials', 'customers', 'toppings', 'poutine', 'sides', 'subs', 'storeorders', 'onlineorders'));
	}
}
