<?php

namespace App\Http\Controllers;

use App\Task;
use App\User;
use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth.role:admin',['except' => ['update']]);
    }
    public function create(array $data){
        Notification::create($data);
    }
    public function listNotification(Request $request)
    {
         $userId = auth()->user()->id;
         $notif = Notification::query()->where('user_id','=', $userId)->get();
        return response()->json($notif, 200);
    }

    public function deleteNotification($id)
    {
        $notif = Notification::findOrFail($id);
        $notif->delete();
        return response()->json("Deleted Successfully", 200);

    }

    public function clearNotification(Request $request)
    {
        $userId = auth()->user()->id;
        $notif = Notification::query()->where('user_id',$userId)->delete();
        return response()->json("Deleted Successfully", 200);

    }

}