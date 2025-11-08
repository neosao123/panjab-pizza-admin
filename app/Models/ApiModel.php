<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;

class ApiModel extends Model
{
	public function generateOTPMaster($contactNumber)
	{
		//$otp = $this->randomOTP(6);
		$otp = '123456';
		try {
			$result = DB::table('registerOTP')->where('contactNumber', '=', $contactNumber)->get();
			if ($result && $result->count() > 0) {
				DB::table('registerOTP')->where('contactNumber', $contactNumber)->update(array('otp' => $otp));
				return $otp;
			} else {
				DB::table('registerOTP')->insertGetId(['contactNumber' => $contactNumber, "otp" => $otp]);
				return $otp;
			}
		} catch (Exception $e) {
			return false;
		}
	}  //generateOTPMaster

	public function generateOTP($contactNumber)
	{
		$otp = 123456; // $this->randomOTP(6);
		if ($contactNumber == "8482940592") {
			$otp = '123456';
		}

		try {
			$result = DB::table('registerOTP')->where('mobile', '=', $contactNumber)->get();
			if ($result && $result->count() > 0) {
				DB::table('registerOTP')->where('mobile', $contactNumber)->update(array('otp' => $otp));
				return $otp;
			} else {
				DB::table('registerOTP')->insertGetId(['mobile' => $contactNumber, "otp" => $otp]);
				return $otp;
			}
		} catch (Exception $e) {
			return false;
		}
	}  //generateOTPMaster


	//generate random otp
	public function randomOTP($n)
	{
		$characters = '0123456789';
		$randomString = '';

		for ($i = 0; $i < $n; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
		}

		return $randomString;
	}
	//check otp exists
	public function checkRegisterOTP($otp, $contactNumber)
	{
		$result = DB::table('registerOTP')->where('mobile', '=', $contactNumber)->where('otp', '=', $otp)->get();
		if ($result && $result->count() > 0) {
			DB::table('registerOTP')->where('mobile', $contactNumber)->delete();
			return true;
		} else {
			return false;
		}
	}

	public function read_user_information($condition)
	{
		try {
			$result = DB::table('clientmaster')
				->select('clientmaster.code', 'clientmaster.name', 'clientmaster.emailId', 'clientmaster.cityCode', 'clientmaster.mobile', 'clientmaster.comCode', 'clientmaster.status', 'clientmaster.forgot', 'clientmaster.cartCode', 'clientmaster.isActive', 'citymaster.cityName', 'clientmaster.isCodEnabled')
				->join('citymaster', 'citymaster.code', '=', 'clientmaster.cityCode')
				->where($condition)
				->get();
			if ($result && $result->count() > 0) {
				return $result;
			} else {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}

	public function read_Delivery_information($condition)
	{
		try {
			// DB::enableQueryLog();
			$result = DB::table('usermaster')
				->select('usermaster.code', 'usermaster.name', 'usermaster.role', 'usermaster.userEmail', 'usermaster.profilePhoto', 'usermaster.mobile', 'usermaster.isActive')
				->where($condition)
				->first();
			// $query_1 = DB::getQueryLog();
			//print_r($query_1);
			if ($result) {
				return $result;
			} else {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}

	public function login_delivery($condition)
	{
		try {
			//DB::enableQueryLog();
			$result = DB::table('usermaster')
				->select('usermaster.code', 'usermaster.password')
				->where($condition)
				->first();
			if ($result) {
				return $result;
			} else {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}
}
