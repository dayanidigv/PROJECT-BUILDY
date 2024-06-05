<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
 
class AuthController extends Controller
{
    public function login (){
        return view('auth.login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('message', 'You have been logged out successfully.');
    }

    public function loginPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Attempt authentication
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect based on user role
            if (Auth::user()->role == 'admin') {
                return redirect()->intended(route('admin.index'));
            } else {
                return redirect()->intended(route('manager.dashboard'));
            }
        }

        // Authentication failed - check if it's due to an incorrect password
        $user = User::where('email', $request->email)->first();
        if ($user && !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ])->withInput();
        }
    
        // Authentication failed due to incorrect credentials
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }       
}
