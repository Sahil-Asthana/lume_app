<?php

namespace App\Http\Controllers;

use App\Task;
use App\User;
use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NotificationController;

class TaskController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth.role:admin',['except' => ['update']]);
    }

    public function getTodaysTask (Request $request)
    {
        $task = Task::whereDate('due_date','=',DB::raw('CURDATE()'))->get();

        return response()->json($task);
    }
    public function showAllTasks(Request $request)
    {
         $task = Task::query();
         $role = auth()->user()->role;
         $userId = auth()->user()->id;
        //filtering

        if($request->filter != null && $request->filter != 'Apply filter'){
        if(strtolower($request->filter) === 'deleted_at'){   
            $task = Task::query()->where('deleted_at','<>', 'active');
        } else {
            $task = Task::query()->where('status','=',$request->filter);
            }
        }
        //searching 
        if($request->searchParam != null){
            $task = Task::query()->where('status','like',"%{$request->searchParam}%")
                                ->orWhere('title','like',"%{$request->searchParam}%")
                                ->orWhere('description','like',"%{$request->searchParam}%")
                                ->orWhere('assignee','like',"%{$request->searchParam}%")
                                ->orWhere('creator','like',"%{$request->searchParam}%")
                                ->orWhere('due_date','like',"%{$request->searchParam}%");
        }
        //sorting
        if($request->sort != null && $request->sort != 'Sort By'){
            if($request->sort != 'created_at') $task = Task::query()->orderBy($request->sort,'asc');
            else $task = Task::query()->orderBy($request->sort,'desc');
        }
        $task = $task->get();
        return response()->json($task);
    }

    public function showMyTasks(Request $request){
        $userId = auth()->user()->id;
        $task = Task::query()->where('assignee','=',$userId)
                             ->orWhere('creator','=',$userId)
                             ->get();
        return response()->json($task, 200);
    }

    public function create(Request $request)
    {
        $task_data = $request->all();
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'assignee' => 'required',
            'status' => '',
            'due_date' => 'required',
            'creator' => '',
            'deleted_by' => ''
        ]);

        $task_data['creator']= auth()->user()->id;
        if(auth()->user()->role === 'normal' && $request->assignee != auth()->user()->id){
            return response()->json('You can not create task for others', 401);
        }
        
        $task = Task::create($task_data);
        $notif_data = ['notification'=>'New task is created with title: '.$task['title'], 'user_id'=> $task['assignee']];
        NotificationController::create($notif_data);
        
        return response()->json($task, 201);
    }


    public function updateStatus(Request $request, $id){
        $task = Task::findOrFail($id);
        if(auth()->user()->id != $task->assignee){
            return response()->json('You can not change status', 401);
        }
        $task->update($request->only(['status']));
        $notif_data = ['notification'=>'Status has been changed for task with title: '.$task->title.' to '.$task->status, 'user_id'=> $task->creator];
        NotificationController::create($notif_data);
        // event(new MyEvent('hello world'));
        return response()->json($task, 200);
    } 

    public function editTask(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        if(auth()->user()->id != $task->creator){
            return response('You can not edit task', 401);
        }
        if($request->due_date == '' || $request->title == '' || $request->description == ''){
            return response()->json('One or more filed is empty', 422);
        }
        $task->update($request->only(['title','description','due_date']));
        $notif_data = ['notification'=>$task->title.' is updated by '.$task->creator, 'user_id'=> $task->assignee];
        NotificationController::create($notif_data);
        return response()->json($task, 200);
    }

    public function deleteTask($id)
    {
        $task = Task::findOrFail($id);
        if($task['deleted_by'] != 'active'){
            return response()->json('No task exist',404);
        }
        if(auth()->user()->id != $task->creator){
            return response()->json('Can not delete task', 401);
        }
        $task['deleted_by'] = auth()->user()->id;
        $task['status'] = 'deleted';
        $task['due_date'] = '';
        $task->forceFill([
            'deleted_at' => $task->freshTimestamp(),
        ])->save();
        $notif_data = ['notification'=>$task->title.' is deleted by '.$task->creator, 'user_id'=> $task->assignee];
        NotificationController::create($notif_data);
        return response()->json('Deleted Successfully', 200);
    }
}