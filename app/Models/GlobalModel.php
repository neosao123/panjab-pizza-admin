<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;


class GlobalModel extends Model
{

    // Read active data from table based on single condition
    public function read_user_information($tblname, $condition)
    {
        try {
            //$query = DB::table($tblname)->where($condition)->where('isActive', 1)->first();
            $query = DB::table($tblname)->where($condition)->first();
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }

    public function read_active_data($tblname)
    {
        try {
            $query = DB::table($tblname)->where('isActive', 1)->get();
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }

    //Update
    public function doEdit($data, $table, $code)
    {
        try {
            DB::table($table)->where('code', $code)->update($data);
            $queryStatus = true;
        } catch (Exception $e) {
            $queryStatus = false;
        }

        return $queryStatus;
    }

    public function doEditWithID($data, $table, $id)
    {
        try {
            DB::table($table)->where('id', $id)->update($data);
            $queryStatus = true;
        } catch (Exception $e) {
            $queryStatus = false;
        }

        return $queryStatus;
    }

    public function doEditWithCondition($data, $table, $code, $condition)
    {
        try {
            DB::table($table)->where($condition, $code)->update($data);
            $queryStatus = true;
        } catch (Exception $e) {
            $queryStatus = false;
        }

        return $queryStatus;
    }

    //create random numbers
    public function randomCharacters($n)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    public function selectActiveDataFromTable($tablename)
    {
        try {
            $query = DB::table($tablename)->where('isDelete', 0)->get();
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }


    public function checkrecord_exists($columnName, $value, $tablename)
    {
        try {
            $query = DB::table($tablename)->where($columnName, $value)->first();
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }

    public function doEditWithField($data, $tblname, $field, $value)
    {
        try {
            DB::table($tblname)->where($field, $value)->update($data);
            $queryStatus = true;
        } catch (Exception $e) {
            $queryStatus = false;
        }
        return $queryStatus;
    }

    // add record with created code without year
    public function addNew($transaction, $tblname, $initial)
    {
        try {
            $currentId = DB::table($tblname)->insertGetId($transaction);
            //Update Code with update query
            $hashCode = $initial . "_" . $currentId;
            $nowdate = date('Y-m-d H:i:s');
            DB::table($tblname)->where('id', $currentId)->update(['code' => $hashCode]);
            $res = $hashCode;
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    // add record with created code with year
    public function addNewWithYear($transaction, $tblname, $initial)
    {
        try {
            $currentId = DB::table($tblname)->insertGetId($transaction);
            //Update Code with update query
            $hashCode = $initial . date("y") . "_" . $currentId;
            $nowdate = date('Y-m-d H:i:s');
            DB::table($tblname)->where('id', $currentId)->update(['code' => $hashCode]);
            $res = $hashCode;
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    public function selectQueryWhereBetween($sel, $table, $joinArray = array(), $cond = array(), $orderBy = array(), $like = array(), $limit = '', $offset = '', $whereBetweenColumn = "", $whereBetweenDates = array())
    {
        DB::enableQueryLog();
        $query = DB::table($table)->select($sel);
        //print_r($cond);
        if (!empty($cond)) {
            foreach ($cond as $k => $v) {
                if ($v != null) {
                    $sign = $v[0];
                    if (!empty($v[1]) || $v[1] == '0' || $v[1] == '1') {
                        $value = $v[1];
                        $query->where($k, $sign, $value);
                    }
                }
            }
        }
        if ($whereBetweenColumn != "" && count($whereBetweenDates) == 2) {
            $query->whereBetween($whereBetweenColumn, $whereBetweenDates);
        }

        if (!empty($joinArray)) {
            foreach ($joinArray as $k => $v) {
                if ($v != null) {
                    $joinFrom = $v[0];
                    $joinTo = $v[1];
                    $query->join($k, $joinFrom, '=', $joinTo);
                }
            }
        }
        if (!empty($orderBy)) {
            foreach ($orderBy as $key => $val) {
                $query->orderBy($key, $val);
            }
        }

        if (!empty($like)) {
            $query->where(function ($query) use ($like) {
                foreach ($like as $key => $val) {
                    if (!empty($key) && !empty($val)) {
                        $query->orWhere($key, "LIKE", "%" . $val . "%");
                    }
                }
            });
        }
        if ($limit != "") {
            if ($offset != "") {
                $query->offset($offset)->limit($limit);
            } else {
                $query->limit($limit);
            }
        }
        $query_1 = DB::getQueryLog();
        //print_r($query_1);
        $result = $query->get();
        return $result;
    }


     public function selectQuery($sel, $table, $joinArray = array(), $cond = array(), $orderBy = array(), $like = array(), $limit = '', $offset = '',$extraCondition='')
    {
        DB::enableQueryLog();
        $query = DB::table($table)->select($sel);
        //print_r($cond);
        if (!empty($cond)) {
            foreach ($cond as $k => $v) {
                if ($v != null) {
                    $sign = $v[0];
                    if (!empty($v[1]) || $v[1] == '0' || $v[1] == '1') {
                        $value = $v[1];
                        $query->where($k, $sign, $value);
                    }
                }
            }
        }
        if (!empty($joinArray)) {
            foreach ($joinArray as $k => $v) {
                if ($v != null) {
                    $joinFrom = $v[0];
                    $joinTo = $v[1];
                    $query->join($k, $joinFrom, '=', $joinTo);
                }
            }
        }
        if (!empty($orderBy)) {
            foreach ($orderBy as $key => $val) {
                $query->orderBy($key, $val);
            }
        }
        
		 if($extraCondition!=''){
			$query->whereRaw($extraCondition);
		}
        if (!empty($like)) {
            $query->where(function ($query) use ($like) {
                foreach ($like as $key => $val) {
                    if (!empty($key) && !empty($val)) {
                        $query->orWhere($key, "LIKE", "%" . $val . "%");
                    }
                }
            });
        }
        if ($limit != "") {
            if ($offset != "") {
                $query->offset($offset)->limit($limit);
            } else {
                $query->limit($limit);
            }
        }
        $query_1 = DB::getQueryLog();
        //print_r($query_1);
        $result = $query->get();
        return $result;
    }

    public function selectQueryWithGroupBy($sel, $table, $joinArray = array(), $cond = array(), $orderBy = array(), $groupBy = array(), $like = array(), $limit = '', $offset = '', $extraCondition = '', $joinType = array())
    {
        DB::enableQueryLog();
        $query = DB::table($table)->select($sel);
        //print_r($cond);
        if (!empty($cond)) {
            foreach ($cond as $k => $v) {
                if ($v != null) {
                    $sign = $v[0];
                    if (!empty($v[1]) || $v[1] == '0' || $v[1] == '1') {
                        $value = $v[1];
                        $query->where($k, $sign, $value);
                    }
                }
            }
        }
        if (!empty($joinArray)) {
            foreach ($joinArray as $k => $v) {
                if ($v != null) {
                    $joinFrom = $v[0];
                    $joinTo = $v[1];
                    if (!empty($joinType) && $joinType[$k] != "" && $joinType[$k] == "left") {
                        $query->leftJoin($k, $joinFrom, '=', $joinTo);
                    } else {
                        $query->join($k, $joinFrom, '=', $joinTo);
                    }
                }
            }
        }
        if (!empty($orderBy)) {
            foreach ($orderBy as $key => $val) {
                $query->orderBy($key, $val);
            }
        }

        if (!empty($groupBy)) {
            foreach ($groupBy as $key) {
                $query->groupBy($key);
            }
        }

        if ($extraCondition != '') {
            $query->whereRaw($extraCondition);
        }

        if (!empty($like)) {
            $query->where(function ($query) use ($like) {
                foreach ($like as $key => $val) {
                    if (!empty($key) && !empty($val)) {
                        $query->orWhere($key, "LIKE", "%" . $val . "%");
                    }
                }
            });
        }
        if ($limit != "") {
            if ($offset != "") {
                $query->offset($offset)->limit($limit);
            } else {
                $query->limit($limit);
            }
        }
        $query_1 = DB::getQueryLog();
        //print_r($query_1);
        $result = $query->get();
        return $result;
    }

    /* setting is isDelete 1 */
    public function updateOnDeleteRecord($id, $tablename)
    {
        $nowdate = date('Y-m-d h:i:s');
        try {
            DB::table($tablename)->where('code', $id)->update(['isDelete' => '1']);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateOnDeleteRecordWithId($id, $tablename)
    {
        $nowdate = date('Y-m-d h:i:s');
        try {
            DB::table($tablename)->where('id', $id)->update(['isDelete' => '1']);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /* delete permant record from table */
    public function deletePermanent($id, $tablename)
    {
        try {
            DB::table($tablename)->where('code', '=', $id)->delete();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /* delete permant record from table */
    public function deleteAll($id, $tablename)
    {
        try {
            DB::table($tablename)->where('clientCode', '=', $id)->delete();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    //select 2 items from tables
    public function pluckData($tablename, $field1, $field2)
    {
        try {
            $query = DB::table($tablename)->pluck($field1, $field2);
            return $query;
        } catch (Exception $e) {
        }
    }

    public function selectDataByField($tablename, $condition)
    {
        try {
            $query = DB::table($tablename)->where($condition)->first();
            return $query;
        } catch (Exception $e) {
        }
    }

    //select data from table based on code
    public function selectDataByCode($tablename, $code)
    {
        try {
            $query = DB::table($tablename)->where('code', $code)->first();
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }

    public function selectDataById($tablename, $id)
    {
        try {
            $query = DB::table($tablename)->where('id', $id)->first();
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }

    public function selectAllDataFromTable($tablename)
    {
        try {
            $query = DB::table($tablename)->get();
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getDistinct($tablename, $column)
    {
        try {
            $query = DB::table($tablename)->distinct()->get([$column]);
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkForDuplicate($tablename = "", $columns, $whereCondition = array())
    {
        try {
            $query = DB::table($tablename);
            if (is_array($columns) && !empty($columns)) {
                $query->select($columns);
            } else if (is_string($columns) && $columns != "") {
                $query->select([$columns]);
            } else {
                $query->select(["$tablename.*"]);
            }
            $result = $query->where('isDelete', 0)->where($whereCondition)->count();
            if ($result > 0) return true;
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function activity_log($data)
    {
        $file = 'log-' . date("d-m-Y") . '.txt';
        $destinationPath = public_path() . "/logfile";
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $content = $data . PHP_EOL;
        file_put_contents("$destinationPath/$file", $content, FILE_APPEND);
    }

    public function check_user_login($token)
    {
        $result = DB::table('membermaster')
            ->select('membermaster.*')
            ->where('token', $token)
            ->get();
        if ($result && $result->count() > 0) {
        } else {
            $statusUpdated = DB::table('membermaster')
                ->where('code', Auth::guard('member')->user()->code)
                ->update(['loginStatus' => 'offline']);
            Auth::guard('member')->logout();
            session()->forget('MEMBER_LOGIN');
            session()->forget('MEMBER_TOKEN');
            session()->flash('success', 'Multiple sessions not allowed.');
            return redirect('/login');
        }
    }

    public function check_session($token)
    {
        $result = DB::table('membermaster')
            ->select('membermaster.*')
            ->where('token', $token)
            ->count();
        if ($result > 0) {
            return true;
        }
        return false;
    }

    public function getMenuRights($menuId, $roleCode)
    {
        $ret = '';
        $url = storage_path('app/public/rights/' . $roleCode . '.json');
        $rightscontents = file_get_contents($url);
        $rightJson = json_decode($rightscontents, true);
        foreach ($rightJson as $rt) {
            if ($rt['menu'] == $menuId) {
                $ret = $rt;
            }
        }
        return $ret;
    }
	
	
}
