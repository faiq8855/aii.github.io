<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiUserController extends Controller
{
    public function login(Request $request){
        Log::info($request);
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            return view('home');
        }
        else{
            return Redirect::back ();
        }
    }
    
    public function loginWithOtp(Request $request){
        Log::info($request);
        $user  = User::where([['email','=',request('email')],['otp','=',request('otp')]])->first();
        if( $user){
            Auth::login($user, true);
            User::where('email','=',$request->email)->update(['otp' => null]);
            return view('home');
        }
        else{
            return Redirect::back ();
        }
    }
    
    public function sendOtp(Request $request){
    
        $otp = rand(1000,9999);
        Log::info("otp = ".$otp);
        $user = User::where('email','=',$request->email)->update(['otp' => $otp]);
        // send otp to email using email api
        return response()->json([$user],200);
    }
}
