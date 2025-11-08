<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Crust extends Model
{
    use HasFactory;
	protected $table = 'crust';
	protected $fillable = [
        'code',
        'crust',
        'isActive',
        'isDelete',
		'addID',
		'addIP',
		'addDate',
		'editID',
		'editIP',
		'editDate',
		'deleteID',
		'deleteIP',
		'deleteDate',
    ];
	
	public static function getCrust(){
		$crustArray=[];
		$getCrust=DB::table("crust")->select("crust.*")->where("crust.isActive",1)->where("crust.isDelete",0)->get();
		if($getCrust &&count($getCrust)>0){
			foreach($getCrust as $item){
					$data=["crustCode"=>$item->code,"crustName"=>$item->crust,"isActive"=>$item->isActive,"isDelete"=>$item->isDelete];
					array_push($crustArray,$data);
				}
				return response()->json(["message" => "Data found", "data" => $crustArray], 200);
		}
		return response()->json(["message" => "No Data found"], 200);
	}
}
