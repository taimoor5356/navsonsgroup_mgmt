<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function login()
    {
        $data['header_title'] = 'Login';
        return view('auth.login', $data);
    }
    public function postLogin(Request $request)
    {
        try {
            $remember = !empty($request->remember) ? true : false;
            if (!empty($request->email) && !empty($request->password)) {
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
                    $authUser = Auth::user();
                    if ((isset($authUser->role) && $authUser->role?->name == 'admin')) {
                        return redirect()->route('admin.dashboard');
                    } else if ((isset($authUser->role) && $authUser->role?->name == 'customer')) {
                        return redirect()->route('customer.dashboard');
                    } else if ((isset($authUser->role) && $authUser->role?->name == 'admin customer')) {
                        return redirect()->route('customer.dashboard');
                    } else if ((isset($authUser->role) && $authUser->role?->name == 'admin')) {
                        return redirect()->route('user.dashboard');
                    }
                } else {
                    return redirect()->back()->with('error', 'Invalid credentials');
                }
            } else {
                return redirect()->back()->with('error', 'Invalid credentials');
            }
        } catch (Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Invalid credentials');
        }
    }
    public function register()
    {
        $data['header_title'] = 'Register';
        return view('auth.register', $data);
    }
    public function postRegister(Request $request)
    {
        DB::beginTransaction();
        try {
            request()->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string',
            ]);
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->user_type = 3;
            $user->password = Hash::make($request->password);
            $user->save();
            DB::commit();
            return redirect('/login')->with('success', 'Registered Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
            // return $e->getMessage();
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
