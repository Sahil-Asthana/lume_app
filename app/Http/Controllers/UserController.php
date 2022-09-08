<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
//use Illuminate\Database\Eloquent\SoftDeletes;

class UserController extends Controller
{
    //use SoftDeletes;
    public function __construct()
    {
       // $this->middleware('auth.role:admin',['except' => ['update']]);
    }
    public function showAllUsers(Request $request)
    {
         $user = User::query();
        //filtering

        if($request->filter != null && $request->filter != 'Apply filter'){
        if(strtolower($request->filter) === 'deleted_at'){   
            $user = User::query()->where('deleted_at','<>', 'active');
        } else {
            $user = User::query()->where('role','=',$request->filter);
            }
        }
        // searching param
        if($request->searchParam != null){
            $user = User::query()->where('name','like',"%{$request->searchParam}%")
                                ->orWhere('email','like',"%{$request->searchParam}%")
                                ->orWhere('created_by','like',"%{$request->searchParam}%");
        }

        //sorting
        if($request->sort != null && $request->sort != 'Sort By'){
            if($request->sort != 'created_at') $user = User::query()->orderBy($request->sort,'asc');
            else $user = User::query()->orderBy($request->sort,'desc');
        }
        $user = $user->get();
        return response()->json($user);
    }

    public function showOneUser($id)
    {
        return response()->json(User::find($id));
    }

    public function create(Request $request)
    {
        $user_data = $request->all();
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
            'role' => 'required',
            'created_by' => '',
            'deleted_by' => ''
            

        ]);
        $user_data['password'] = Hash::make($request->password);
        $user_data['created_by']= auth()->user()->id;
        $user = User::create($user_data);
        return response()->json($user, 201);
    }
    public function updateByUser(Request $request){
        $userId = auth()->user()->id;
        $user = User::findOrFail($userId);
        $user->update($request->only(['email','name']));
        return response()->json($user, 200);
    } 

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->only(['role']));
        return response()->json($user, 200);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        if($user['deleted_by'] != 'active'){
            return response()->json('No user exist',404);
        }
        $user['deleted_by'] = auth()->user()->id;
        $user->forceFill([
            'deleted_at' => $user->freshTimestamp(),
        ])->save();
        return response()->json('Deleted Successfully', 200);
    }
}