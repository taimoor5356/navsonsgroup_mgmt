<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function postLogin(Request $request)
    {
        try {
            $remember = !empty($request->remember) ? true : false;
            if (!empty($request->email) && !empty($request->password)) {
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
                    $user = Auth::user();
                    $token = $user->createToken('MyApp')->plainTextToken;
                    // if ($user->user_type == 1) {
                    //     return response()->json([
                    //         'status' => true,
                    //         'token' => $token,
                    //         'data' => $user,
                    //     ]);
                    // } else 
                    if ($user->user_type == 2) {
                        return response()->json([
                            'status' => true,
                            'token' => $token,
                            'data' => $user,
                        ]);
                    } else{
                        return response()->json([
                            'status' => false,
                            'message' => 'Customers Allowed Only',
                        ]);   
                    }

                    // } else if ($user->user_type == 3) {
                    //     return response()->json([
                    //         'status' => true,
                    //         'token' => $token,
                    //         'data' => $user,
                    //     ]);
                    // }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid credentials',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
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
            $token = $user->createToken('MyApp')->plainTextToken;
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Registration successful',
                'token' => $token
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function resetPassword(Request $request)
    {
        DB::beginTransaction();
        try {
            if (Auth::user()) {
                if (Auth::user()->email == $request->email) {
                    $user = User::where('email', $request->email)->first();
                    if ($user) {
                        $user->password = Hash::make($request->password);
                        $user->save();
                        DB::commit();
                        return response()->json([
                           'status' => true,
                           'message' => 'Password reset successful'
                        ]);
                    } else {
                        return response()->json([
                           'status' => false,
                           'message' => 'User not found'
                        ]);
                    }
                } else {
                    return response()->json([
                       'status' => false,
                       'message' => 'Email address not correct'
                    ]);
                }
            } else {
                return response()->json([
                   'status' => false,
                   'message' => 'No logged in user'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
               'status' => false,
               'message' => $e->getMessage()
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
           'message' => 'Logged out successfully'
        ]);
    }
}
