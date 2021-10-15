<?php

namespace App\Http\Controllers;

use App\Models\cr;
use App\Mail\MyTestMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;
use GuzzleHttp\Psr7\Message;
use Exception;
use Log;
use SebastianBergmann\CodeCoverage\StaticAnalysis\Cache;
use Illuminate\Support\Facades\Mail;
use Tzsk\Otp\Facades\Otp;

class login extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function Authencticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
           
        if (Auth::attempt($credentials)) {
            $temp =  Auth::user();
           
            if(isset($temp->is_verified) && $temp->is_verified == 1){
               
                return response()->json(['status'=> true, 'message' => "LoginSuccess", 'user' => Auth::user()]);
               
            }else{
                return response()->json(['status'=> false, 'message' => "Your Email is Not Verified", 'user' => ''],400);

            }
         
        }
        return response()->json(['status'=> false, 'message' => "Inavalid credentials", 'user' => '']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
            //////////////////////////////    GENERATE OTP/////////////////////////
    public function generate_otp(Request $request)
    {  
        $otp = rand(1000,9999);
        Otp::expiry(1);
        Log::info("otp = ".$otp);
        $user = User::where('cnic','=',$request->cnic)->update(['otp' => $otp]);

        if($user){

        $mail_details = [
            'subject' => 'Testing Application OTP',
            'body' => 'Your OTP is : '. $otp
        ];
       
         Mail::to($request->cnic)->send(new MyTestMail($mail_details));
       
         return response(["status" => 200, "message" => "OTP sent successfully"]);
        }
        else{
            return response(["status" => 401, 'message' => 'Invalid']);
        }
    }

    ///////////////////// OTP PHONE VERIFY////////////////////////
    public function verify_otp(Request $request)
    {  
        $validator = validator::make($request->all(), [
            'email' => 'required|exists:users',
             'otp' => 'required|exists:users'
        ]);
    
        if(isset($validator) && $validator->fails()){
            return response()->json(['status' => false, 'error' => $validator->errors()->first()], 422);
        }else{
            $user = DB::table('users')->where([
                'email' => $request->email,
                'otp' => $request->otp,
                
            ]);

                  
            if ($user){


            $otp_expires_time = Carbon::now()->addSeconds(20);
            if(!$otp_expires_time > Carbon::now())
         {
    
            return response()->json(['status' => false, 'message'=> 'Otp'],422);
               }
                
                $user = User::where('email','=',$request->email)->update(['is_verified' => 1]);
                $user = User::where(['email'=> $request->email])->update(['otp' => null]);
                $user = User::where(['email'=> $request->email])->update(['otp_expires_time' => null]);
               
                return response(['status' => true, 'massage' => 'User Verified successfully'], 200);
            }

        
           
            else {
                return response(['status' => false, 'massage' => 'code is inccorect'], 422);
            }
        }
       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
    public function validate1(Request $request)
    {
        
        $request->validate([
          
            'name' => 'required|min:6',
            'email' => 'required|min:13|unique:users',
            'cnic' => 'required|min:13|max:13',
             'password' => 'required|min:6',
            
            
        ]);
        $data = $request->all();
        $data['password']=Hash::make($request->password);
       
        $otp = rand(1000,9999);
       
        Log::info("otp = ".$otp);
      
        $data['otp'] = $otp;
       
        $details = [
            'title' => 'Verification',
            'body' => 'Your OTP is'.$otp
        ];
       
        Mail::to($request->email)->send(new \App\Mail\MyTestMail($details));
        $user = user::create($data);
           
        return response()->json(['status'=>true, 'message'=>'create', 'user'=>$user], 201);
        
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, cr $cr)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
    public function destroy(cr $cr)
    {
        //
    }



    //////////////// for resetting passworsd using otp////////////////////////////////////

    public function submitForgetPasswordForm(Request $request)
    {  
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);
       

        $token = rand(1000,9999);
       
        DB::table('password_resets')->insert([
            'email' => $request->email, 
            'token' => $token, 
            'created_at' => Carbon::now()
          ]); 

        Mail::send('email', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return response()->json(['status'=> true, 'message' => "LoginSuccess"]);
    }


    public function verifyotp(Request $request)
    {  
        $validator = validator::make($request->all(), [
            'email' => 'required|exists:password_resets',
             'token' => 'required|exists:password_resets'
        ]);
    
        if(isset($validator) && $validator->fails()){
            return response()->json(['status' => false, 'error' => $validator->errors()->first()], 422);
        }else{
            $user = DB::table('password_resets')->where([
                'email' => $request->email,
                'token' => $request->token,
            ]);
            if ($user){
                return response(['status' => true, 'massage' => 'code match successfully'], 200);
            }
            else {
                return response(['status' => false, 'massage' => 'code is inccorect'], 422);
            }
        }
    }

    public function submitResetPasswordForm(Request $request)
    {
        
    //    write email validator
    $request->validate([
        'email' => 'required|exists:users',
        'password' => 'required|sring|min:6|confirmed',
        'password_confirmation' => 'required',
    ]);

        $user = User::where('email', $request->email)
                    ->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where(['email'=> $request->email])->delete();

        return redirect('/login')->with('message', 'Your password has been changed!');
    }
    
    
}
