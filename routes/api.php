<?php


use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\login;
use App\Http\Controllers\ApiUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/signup',[login::class, 'create']);
Route::post('/verify',[login::class, 'verify_otp']);
Route::post('/login',[login::class, 'Authencticate']);
Route::post('/sender',[login::class, 'generate_otp']);
Route::post('/verify',[login::class, 'verify_otp']);
Route::post('/validate',[login::class, 'validate1']);



Route::get('send-mail', function () {
   
    $details = [
        'title' => 'Mail from ItSolutionStuff.com',
        'body' => 'This is for testing email using smtp'
    ];
   
    Mail::to('fr.5307020@gmail.com')->send(new \App\Mail\MyTestMail($details));
   
    dd("Email is Sent.");
});
Route::any('/sendOtp',[login::class, 'sendOtp']);





Route::post('login', 'ApiUserController@login')->name('newlogin');

Route::post('loginWithOtp', 'ApiUserController@loginWithOtp')->name('loginWithOtp');
Route::get('loginWithOtp', function () {
    return view('auth/OtpLogin');
})->name('loginWithOtp');

Route::any('/sendOtp', 'UserController@sendOtp');

Route::post('/reset',[login::class, 'submitForgetPasswordForm']);
Route::post('/resetotp',[login::class, 'verifyotp']);
Route::post('/confirm',[login::class, 'submitResetPasswordForm']);
