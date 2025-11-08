<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Customeraddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;


class CustomerController extends Controller
{
	private $role, $rights;
	public function __construct(GlobalModel $model)
	{
		$this->model = $model;
		$this->middleware('auth');
		$this->middleware(function ($request, $next) {
			$this->role = Auth::guard('admin')->user()['role'];
			$this->rights = $this->model->getMenuRights('5.1', $this->role);
			if ($this->rights == '') {
				return redirect('access/denied');
			}
			return $next($request);
		});
	}


	public function index()
	{
		if ($this->rights != '' && $this->rights['view'] == 1) {
			return view('customers.list');
		} else {
			return view('noright');
		}
	}

	public function getCustomer(Request $r)
	{
		$html = [];
		$search = $r->search;
		$like = array('customer.fullName' => $search);
		$extraCondition = "customer.fullName is NOT Null";
		$condition = array('customer.isDelete' => array('=', 0));
		$orderBy = array('customer' . '.id' => 'DESC');
		$result = $this->model->selectQuery('customer.*', 'customer', array(), $condition, $orderBy, $like, 10, '', $extraCondition);
		if ($result) {
			foreach ($result as $item) {
				$html[] = array('id' => $item->code, 'text' => ucwords(strtolower($item->fullName)));
			}
		}
		echo  json_encode($html);
	}

	public function getEmail(Request $r)
	{
		$html = [];
		$search = $r->search;
		$like = array('customer.email' => $search);
		$extraCondition = "customer.email is NOT Null";
		$condition = array('customer.isDelete' => array('=', 0));
		$orderBy = array('customer.id' => 'DESC');

		$result = $this->model->selectQuery(DB::raw('DISTINCT(email) as email, customer.id'), 'customer', array(), $condition, $orderBy, $like, 10, '', $extraCondition);
		if ($result) {
			foreach ($result as $item) {
				$html[] = array('id' => $item->email, 'text' => strtolower($item->email));
			}
		}
		echo  json_encode($html);
	}


	public function getMobile(Request $r)
	{
		$html = [];
		$search = $r->search;
		$like = array('customer.mobileNumber' => $search);
		$condition = array('customer.isDelete' => array('=', 0));
		$orderBy = array('customer.id' => 'DESC');
		$result = $this->model->selectQuery(DB::raw('DISTINCT(mobileNumber) as mobileNumber, customer.id'), 'customer', array(), $condition, $orderBy, $like, 10, '');
		if ($result) {
			foreach ($result as $item) {
				$html[] = array('id' => $item->mobileNumber, 'text' => $item->mobileNumber);
			}
		}
		echo  json_encode($html);
	}


	public function getCustomerList(Request $req)
	{
		$customercode = $req->customercode;
		$mobile = $req->mobile;
		$email = $req->email;
		$export = $req->export;
		$search = $limit = $offset = '';
		$srno = 1;
		$draw = 0;
		$total = 0;
		$orderBy = [];
		if ($export == 0) {
			$search = $req->input('search.value');
			$limit = $req->length;
			$offset = $req->start;
			$srno = $_GET['start'] + 1;
			$draw = $_GET["draw"];
		}
		$tableName = "customer";
		$orderColumns = array("customer.*");
		$condition = array('customer.isDelete' => array('=', 0), 'customer.code' => array('=', $customercode), 'customer.mobileNumber' => array('=', $mobile), 'customer.email' => array('=', $email));
		$orderBy = array('customer' . '.id' => 'DESC');
		$join = array();
		$like = array('customer.fullName' => $search, 'customer.lastName' => $search, 'customer.firstName' => $search, 'customer.email' => $search, 'customer.mobileNumber' => $search);
		//$limit = $req->length;
		//$offset = $req->start;
		$extraCondition = "";
		$result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
		//$srno = $_GET['start'] + 1;
		$dataCount = 0;
		$data = array();
		if ($result && $result->count() > 0) {
			foreach ($result as $row) {
				$role = '';
				$status = '<span class="badge badge-danger"> InActive </span>';
				$printStatus = "InActive";
				if ($row->isActive == 1) {
					$status = '<span class="badge badge-success">Active</span>';
					$printStatus = "Active";
				}
				if ($this->rights != '' && $this->rights['view'] == 1) {
					$actions = '<div class="btn-group">
								<button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<i class="ti-settings"></i>
								</button>
								<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';

					$actions .= '<a class="dropdown-item" href="' . url("customers/view/" . $row->code) . '"><i class="ti-eye mr-2"></i> Open</a>';

					$actions .= '</div>
							</div>';
				}
				if ($export == 0) {
					$data[] = array(
						$srno,
						$actions,
						$row->fullName,
						$row->email,
						$row->mobileNumber,
						$status,

					);
				} else {
					$data[] = array(
						$srno,
						$row->fullName,
						$row->email,
						$row->mobileNumber,
						$printStatus,

					);
				}
				$srno++;
			}
			$dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', ''));
		}
		$output = array(
			"draw" => intval($draw),
			"recordsTotal" => $dataCount,
			"recordsFiltered" => $dataCount,
			"data" => $data
		);
		echo json_encode($output);
	}

	public function view(Request $r)
	{
		if ($this->rights != '' && $this->rights['update'] == 1) {
			$data['viewRights'] = $this->rights['view'];
			$code = $r->code;
			$customers = Customer::where('customer.code', $code)->first();
			if (!empty($customers)) {
				$data['queryresult'] = $customers;
				$data['customerAddress'] = Customeraddress::where('customeraddress.customerCode', $code)->get();
				return view('customers.view', $data);
			}
		} else {
			return view('noright');
		}
	}
}
