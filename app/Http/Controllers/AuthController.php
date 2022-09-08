<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use Tymon\JWTAuth\Contracts\JWTSubject;

class AuthController extends Controller 
{

    public function __construct()
    {
       // $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);
        

        if (! $token = Auth::attempt($credentials)) {
            
            return response()->json(['message' => 'Unauthorized'], 401);
        } 

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }


/////////////////////////////////////////////////////////////////////


    public function emailRequestVerification(Request $request)
    {
        if ( $request->user()->hasVerifiedEmail() ) {
            return response()->json('Email address is already verified.');
        }
        $request->user()->sendEmailVerificationNotification();
        
        return response()->json('Email request verification sent to '. Auth::user()->email);
    }
 
    public function emailVerify(Request $request)
    {
        $this->validate($request, [
        'token' => 'required|string',
        ]);

    if ( ! $request->user() ) {
            return response()->json('Invalid token', 401);
        }
        
        if ( $request->user()->hasVerifiedEmail() ) {
            return response()->json('Email address '.$request->user()->getEmailForVerification().' is already verified.');
        }
    $request->user()->markEmailAsVerified();
    return response()->json('Email address '. $request->user()->email.' successfully verified.');
    }

}
