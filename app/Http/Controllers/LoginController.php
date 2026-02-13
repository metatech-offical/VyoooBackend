<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\GlobalFunction;
use App\Models\GlobalSettings;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function forgotPasswordForm(Request $request)
    {
        $request->validate([
            'new_password' => 'required',
        ]);

        $databaseUsername = env('DB_USERNAME');
        $databasePassword = env('DB_PASSWORD');

        if ($request->database_username == $databaseUsername && $request->database_password == $databasePassword) {
            $encryptedPassword = Crypt::encrypt($request->new_password);

            $admin = Admin::where('admin_username', 'admin')->first();

            if (!$admin) {
                return GlobalFunction::sendSimpleResponse(false, 'Admin user not found.');
            }

            $admin->admin_password = $encryptedPassword;
            $admin->save();

            return GlobalFunction::sendSimpleResponse(true, 'Password updated successfully.');

        } else {
            return GlobalFunction::sendSimpleResponse(false, 'Wrong credentials.');
        }
    }

    function login()
    {
        $setting = GlobalSettings::first();
        if ($setting) {
            Session::put('app_name', $setting->app_name);
        }
        // Storage link should be run once at deploy (e.g. php artisan storage:link via SSH).
        // Do not run here: symlink() is often disabled on shared hosting and causes 500.
        if (Session::get('username') && Session::get('userpassword') && Session::get('user_type')) {
            $adminUser = Admin::where('admin_username', Session::get('username'))->first();
            if ($adminUser) {
                try {
                    if (Crypt::decrypt($adminUser->admin_password) === Session::get('userpassword')) {
                        return redirect('dashboard');
                    }
                } catch (DecryptException $e) {
                    // APP_KEY changed or data encrypted with different key
                }
            }
        }
        return view('login');
    }

    function checkLogin(Request $request)
    {
        $data = Admin::where('admin_username', $request->username)->first();

        $passwordValid = false;
        if ($data && $request->username === $data->admin_username) {
            try {
                $passwordValid = $request->password === Crypt::decrypt($data->admin_password);
            } catch (DecryptException $e) {
                Log::warning('Login decrypt failed (APP_KEY mismatch?): ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot verify password. Server APP_KEY may not match the key used when the password was set. Reset the admin password on this server or set APP_KEY to match.',
                ]);
            }
        }

        if ($data && $passwordValid) {
            $request->session()->put('username', $data['admin_username']);
            $request->session()->put('userpassword', $request->password);
            // Full admin = 1, Tester = 0 (normalize "admin" string to 1 for compatibility)
            $userType = $data['user_type'];
            $request->session()->put('user_type', ($userType === 1 || $userType === '1' || $userType === 'admin') ? 1 : (int) $userType);

            return response()->json([
                'status' => true,
                'message' => 'Login Successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Wrong credentials!',
            ]);
        }
    }

    function logout()
    {
        session()->pull('username');
        session()->pull('user_type');
        session()->pull('userpassword');
        return redirect(url('/'));
    }
}
