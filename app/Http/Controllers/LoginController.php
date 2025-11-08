<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\GlobalModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    protected $model;
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (session('USER_LOGIN')) {
            return redirect('/dashboard');
        } else {
            return view('login', ["pageTitle" => "Login"]);
        }
    }

    public function login(Request $r)
    {
        if ($r->isMethod('post')) {
            $r->validate([
                'username' => 'required',
                'password' => 'required',
            ]);
            $username = $r->input('username');
            $password = $r->input('password');
            $remember = $r->input('rememberme') == true ? '1' : '0';
            $result = Users::where(['username' => $username])->first();
            if (empty($result)) {
                $r->session()->flash('fail', 'Please Enter Valid Username & Password');
                return redirect('/');
            } else {
                if (Auth::guard('admin')->attempt(['username' => $username, 'password' => $password])) {
                    Auth::login($result);
                    $username = Auth::guard('admin')->user()->username;
                    $r->session()->put('USER_LOGIN', true);
                    if ($remember == '1') {
                        Cookie::queue('username', $username, time() + (10 * 365 * 24 * 60 * 60));
                        Cookie::queue('password', $password, time() + (10 * 365 * 24 * 60 * 60));
                    } else {
                        if (Cookie::get('username')) Cookie::queue('username', '');
                        if (Cookie::get('password')) Cookie::queue('password', '');
                    }
                    return redirect('/dashboard');
                }
                $r->session()->flash('error', 'Invalid Username or password.');
                return redirect('/login');
            }
        } else {
            return redirect('/login')->with('error', 'Unknown action...');
        }
    }

    public function updatePassword()
    {
        $r = Users::find(1);
        $r->password = Hash::make('123456');
        $r->save();
    }

    public function logout(Request $r)
    {
        Auth::guard('admin')->logout();
        session()->forget('USER_LOGIN');
        $r->session()->flash('success', 'Successfully Logout');
        return redirect('/login');
    }


    public function reset(Request $r)
    {
        return view('auth.verify');
    }


    public function resetPassword(Request $request)
    {
        $email = $request->input('useremail');
        $result = $this->model->checkrecord_exists('userEmail', $email, 'usermaster');

        if ($result) {
            $token = $this->model->randomCharacters(5);
            $token .= date('Hdm');
            $sendLink = url('recoverPassword/' . $token);
            $details = [
                'title' => 'Mail from Mr Singh Pizza.',
                'link' => $sendLink,
            ];

            Mail::to($email)->send(new \App\Mail\ForgotAdminEmail($details));

            $code = $result->code;
            $data = array('resetToken' => $token);
            $resultAfterMail = $this->model->doEditWithField($data, 'usermaster', 'code', $code);
            if ($resultAfterMail) {
                $request->session()->flash('success', 'Reset Link was sent to your email...');
                return redirect('/reset-password');
            } else {
                $request->session()->flash('error', 'Some Error is Occur');
                return redirect('/reset-password');
            }
        } else {
            $request->session()->flash('error', 'No users were found with the email address provided! Sorry cannot reset the password');
            return redirect('/reset-password');
        }
    }

    public function verifyTokenLink(Request $request)
    {
        $token = $request->token;
        $result = $this->model->checkrecord_exists('resetToken', $token, 'usermaster');
        if ($result) {
            return view('auth.reset', compact('result'));
        } else {
            $request->session()->flash('message', 'Password Reset Link is Expired. Please Forgot Password Again to Continue.');
            return redirect('/login');
        }
    }


    public function updateMemberPassword(Request $request)
    {
        $token = $request->input('token');
        $code = $request->input('code');
        $result = $this->model->checkrecord_exists('resetToken', $token, 'usermaster');
        if ($result) {
            $rules = [
                'password'  =>  'min:6|confirmed|required',
                'password_confirmation' => 'min:6|required',
            ];

            $messages = [
                'password.required' => 'Password is required',
                'password.confirmed' => 'Password is not matched'
            ];
            $this->validate($request, $rules, $messages);

            $data = array(
                'password' => Hash::make($request->input('password')),
                'resetToken' => null,
            );
            $result = $this->model->doEdit($data, 'usermaster', $code);
            if ($result) {
                $request->session()->flash('message', 'Password Reset Successfully.. Please Login to Continue');
                return redirect('/login');
            } else {
                $request->session()->flash('message', 'Problem During Reset Password.. Please Try Again');
                return redirect('/login');
            }
        } else {
            $request->session()->flash('message', 'Reset Link is broken! Please try again...');
            return redirect('/login');
        }
    }
}
