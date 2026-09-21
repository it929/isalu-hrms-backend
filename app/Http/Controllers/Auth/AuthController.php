<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';
    protected $username = 'username';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    // }


    public function login_old_12_02_2026(Request $request)
    {
        Session::forget('userSession');
        // dd(123456);
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            // 'g-recaptcha-response' => 'required|string',
            //'g-recaptcha-response' => new Captcha(),
        ]);

        //if (Auth::attempt(['username' => $request['username'], 'password' => $request['password'] ]) || Auth::attempt(['email' => $request['email'], 'password' => $request['password'] ]) ) {

        if (Auth::attempt(['username' => $request['username'], 'password' => $request['password']])) {

            //Send login notification Email
            // Auth::user()->user_type != "TECHNICAL" ?  $this->loginNotificationEmail(\Request::ip()) : '';
            //$this->loginNotificationEmail(\Request::ip());
            //end email

            // return redirect()->intended($this->redirectPath());
            // return redirect('/');
            return $this->redirectToPreviousPage($request);
        } else {
            return redirect('/login')->with('error', 'Username or password not correct');
        }
    }


    public function login(Request $request)
    {
        Session::forget('userSession');

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        // dd($request->all());

        if (Auth::attempt(['username' => $request['username'], 'password' => $request['password']])) {

            $user = Auth::user();

            $isSuperAdmin = ((int)($user->is_global ?? 0) === 1)
                || (strtolower($user->user_type ?? '') === 'technical')
                || \DB::table('assign_user_role')
                    ->leftJoin('user_role', 'assign_user_role.roleID', '=', 'user_role.roleID')
                    ->where('assign_user_role.userID', $user->id)
                    ->where(function ($q) {
                        $q->where('assign_user_role.roleID', 1)
                          ->orWhere('user_role.rolename', 'like', '%Super Admin%');
                    })->exists();

            // Find corresponding staff record in tblper
            $staff = \DB::table('tblper')->where('UserID', $user->id)->first();
            if (!$staff && is_numeric($user->username)) {
                $staff = \DB::table('tblper')->where('ID', (int)$user->username)->first();
            }
            if (!$staff && is_numeric($user->id)) {
                $staff = \DB::table('tblper')
                    ->where('ID', (int)$user->id)
                    ->where(function ($q) use ($user) {
                        $q->whereNull('UserID')->orWhere('UserID', 0)->orWhere('UserID', $user->id);
                    })
                    ->first();
            }
            if (!$staff) {
                $staff = \DB::table('tblper')->where('fileNo', $user->username)->first();
                if (!$staff && !empty($user->email)) {
                    $staff = \DB::table('tblper')
                        ->where('email', $user->email)
                        ->where(function ($q) use ($user) {
                            $q->whereNull('UserID')->orWhere('UserID', 0)->orWhere('UserID', $user->id);
                        })
                        ->first();
                }
            }

            // Block inactive staff (staff_status == 0) from logging in, unless Super Admin
            if ($staff && (int)$staff->staff_status === 0 && !$isSuperAdmin) {
                Auth::logout();
                return redirect('/login')->with('error', 'Your staff account is inactive. Inactive staff are not permitted to log in. Please contact HR administration.');
            }

            // ✅ Check if user is still using default password
            if (Hash::check('12345', $user->password) && $user->user_type != "Technical") {
                // dd($request->all());

                return redirect('/user/editAccount')
                    ->with('warning', 'For security reasons, please change your default password.');
            }

            // ✅ Force password change
            // if (!$user->password_changed) {
            //     return redirect('/user/editAccount')
            //         ->with('warning', 'Please change your default password before continuing.');
            // }

            return $this->redirectToPreviousPage($request);
        } else {
            return redirect('/login')->with('error', 'Username or password not correct');
        }
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'username' => 'required|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
        ]);
    }

    //Let us send email for each login to notify admin.
    // protected function loginNotificationEmail($ip)
    // {
    //     try{
    //         //$ip = '197.157.217.183'; /* Static IP address */
    //         $currentUserInfo = \Location::get($ip);

    //         Mail::send(
    //             'mail.loginNotifier', //e.g post/mail.blade.php <view file mentioned here>
    //             [
    //                 'userName' => (Auth::user() ? Auth::user()->name : ''),
    //                 'userEmail' => (Auth::user() ? Auth::user()->email_address : ''),
    //                 'division' => (Auth::user() ? DB::table('tbldivision')->where('divisionID', Auth::user()->divisionID)->value('division') : ''),
    //                 'ipAddress'        => $ip,
    //                 'userCountry' => ($currentUserInfo ? $currentUserInfo->countryName : ''),
    //                 'regionName' => ($currentUserInfo ? $currentUserInfo->regionName .', '. $currentUserInfo->cityName : ''),
    //             ],
    //             function($message){
    //                 $message->to(env('MAIL_TO_EMAIL_ADDRESS', ''), Auth::user()->name);
    //                 $message->subject('Login Notification');
    //                 $message->from(env('MAIL_FROM_ADDRESS', ''), env('MAIL_FROM_NAME', 'ISALU HOSPITAL LIMITED'));
    //             }
    //          );
    //     }catch(Throwable $err){
    //         //unable to send email notification
    //         #code....
    //         die($err);
    //     }

    //     //return;
    // }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function redirectToPreviousPage(Request $request)
    {
        // Check if there's an intended URL in the session
        if ($request->session()->has('url.intended')) {
            // Get the intended URL and remove it from the session
            $intendedUrl = $request->session()->pull('url.intended');
            return redirect()->intended($intendedUrl);
        }

        // Default redirect if no intended URL found
        return redirect('/');
    }
}
