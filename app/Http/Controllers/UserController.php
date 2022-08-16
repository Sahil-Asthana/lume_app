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
        $user = DB::table('users');

        //filtering
        if(strtolower($request->role) === 'admin'){
            $user = DB::table('users')->where('role', '=', 'admin');
        } elseif(strtolower($request->role) === 'normal'){
            $user = DB::table('users')->where('role','=','normal');
        }
        if(strtolower($request->deleted_by) === '1'){   
            $user = DB::table('users')->where('deleted_by','<>', 'active');
        }

        //sorting
        if(strtolower($request->sort) === 'name'){
            $user = DB::table('users')->orderBy('name', 'desc');
        } elseif(strtolower($request->sort) === 'email'){
            $user = DB::table('users')->orderBy('email', 'asc');
        } elseif(strtolower($request->sort) === 'created_at'){
            $user = DB::table('users')->orderBy('created_at', 'desc');
        }
        
        $user = $user->get(); 
       // return view('user.index', ['users' => $user]);
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
        $user = auth()->user()->id;
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
        if($user['deleted_by'] != null){
            return response('No user exist');
        }
        $user['deleted_by'] = auth()->user()->id;
        $user->forceFill([
            'deleted_at' => $user->freshTimestamp(),
        ])->save();
        return response('Deleted Successfully', 200);
    }
}