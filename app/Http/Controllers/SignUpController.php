<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class SignUpController extends Controller
{
    

    public function create(Request $request)
    {
        $user_data = $request->all();
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
            'created_by' => '',
            'deleted_by' => ''
            

        ]);
        
        $user_data['password'] = Hash::make($request->password);
        $user_data['created_by'] = $request->name;
        $user = User::create($user_data);
        // if($user==null){
        //     return response()->json("Error in sign up!", 401);
        // }

        return response()->json($user, 201);
    }

   
}