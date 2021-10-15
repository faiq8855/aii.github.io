<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
     
    public function sendOtp(Request $request){
    
        $otp = rand(1000,9999);
        Log::info("otp = ".$otp);
        $user = User::where('email','=',$request->email)->update(['otp' => $otp]);
        // send otp to email using email api
        return response()->json([$user],200);
    }
}
