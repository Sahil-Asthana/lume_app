<?php

namespace App\Http\Controllers;

use App\Task;
use App\User;
use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NotificationController;
use Illuminate\Contracts\Event\Dispatcher;
use App\Events\NotificationEvent;
use App\Events\PusherEvent;
use Pusher\Pusher;



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

    public function getTaskForUser (Request $request, $id) 
    {
        $task = Task::query()->where('assignee','=',$id)->get();
        return response()->json($task);
    }

    public function getMyTasks(Request $request){
        $userId = auth()->user()->id;
        $task = Task::query()->where('assignee','=',$userId)
                             ->orWhere('creator','=',$userId)->get();
        return response()->json($task);
    }

    public function showAllTasks(Request $request)
    {
         $task = Task::query();
         $this->searchParam = $request->searchParam;
         $this->id = $request->id;
        if($request->searchParam==null && $request->filter==null && $this->id!=null){
            $task=$task->where('assignee','=',$this->id)
                                ->orWhere('creator','=',$this->id);
        }

        //filtering
        if($request->filter != null && $request->filter != 'Apply filter'){
        if(strtolower($request->filter) === 'deleted_at'){   
            $task = $task->where('deleted_at','<>', 'active')
                            ->where(function($query){
                                if($this->id) { $query->where('assignee','=',$this->id)
                                                      ->orWhere('creator','=',$this->id);}
                                })
                            ->where(function($query){
                                if($this->searchParam){
                                        $query->where('status','like',"%{$this->searchParam}%")
                                        ->orWhere('title','like',"%{$this->searchParam}%")
                                        ->orWhere('description','like',"%{$this->searchParam}%")
                                        ->orWhere('assignee','like',"%{$this->searchParam}%")
                                        ->orWhere('creator','like',"%{$this->searchParam}%");}
                                    }); 
        } else {
            $task = $task->where('status','=',$request->filter)
                         ->where(function($query){
                               if($this->id) { $query->where('assignee','=',$this->id)
                                 ->orWhere('creator','=',$this->id);}
                                })
                         ->where(function($query){
                            if($this->searchParam){
                                    $query->where('status','like',"%{$this->searchParam}%")
                                    ->orWhere('title','like',"%{$this->searchParam}%")
                                    ->orWhere('description','like',"%{$this->searchParam}%")
                                    ->orWhere('assignee','like',"%{$this->searchParam}%")
                                    ->orWhere('creator','like',"%{$this->searchParam}%");}
                                });            
            }
        }
        //searching 
        if($request->searchParam != null && ($request->filter == null || $request->filter == 'Apply filter' )){
            $task = $task->where(function($query){
                                    if($this->id) { $query->where('assignee','=',$this->id)
                                    ->orWhere('creator','=',$this->id);}
                                    })
                        ->where(function($query){
                            $query->where('status','like',"%{$this->searchParam}%")
                                ->orWhere('title','like',"%{$this->searchParam}%")
                                ->orWhere('description','like',"%{$this->searchParam}%")
                                ->orWhere('assignee','like',"%{$this->searchParam}%")
                                ->orWhere('creator','like',"%{$this->searchParam}%");
                            });
        }
        //sorting
        if($request->sort != null && $request->sort != 'Sort By'){
            if($request->sort != 'created_at') $task = $task->orderBy($request->sort,'asc');
            else $task = $task->orderBy($request->sort,'desc');
        }
        $task = $task->paginate(10);
        return response()->json($task);
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
        $type = 'created';
        $user = User::findOrFail($request->assignee);
        event(new NotificationEvent($user, $task, $type));
        //event(new PusherEvent('New Task is created with : '.$task->title, $user));
        return response()->json($task, 201);
    }

    public function updateStatus(Request $request, $id){
        $task = Task::findOrFail($id);
        if(auth()->user()->id != $task->assignee){
            return response()->json('You can not change status', 401);
        }
        $task->update($request->only(['status']));

        $type = "updated";
        $user = User::findOrFail($task->creator);
        event(new NotificationEvent($user, $task, $type));
        //event(new PusherEvent('Status has been changed for task with title: '.$task->title, $user));
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

        $type = 'edited';
        $user = User::findOrFail($task->assignee);
        event(new NotificationEvent($user, $task, $type));
        //event(new PusherEvent($task->title.' is modified by the creator', $user));
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
        $task->forceFill([
            'deleted_at' => $task->freshTimestamp(),
        ])->save();

        $type = 'deleted';
        $user = User::findOrFail($task->assignee);
        event(new NotificationEvent($user, $task, $type));
        //event(new PusherEvent($task->title.' is deleted', $user));
        return response()->json('Deleted Successfully', 200);
    }

    public function bulkDelete(Request $request){
        $ids = $request->arrayId;
        foreach($ids as $id){
            $task = Task::findOrFail($id);
            if(auth()->user()->id == $task->creator && $task['deleted_by'] == 'active'){
                $task['deleted_by'] = auth()->user()->id;
                $task['status'] = 'deleted';
                $task->forceFill([
                    'deleted_at' => $task->freshTimestamp(),
                ])->save();

                $type = 'deleted';
                $user = User::findOrFail($task->assignee);
                event(new NotificationEvent($user, $task, $type));
                //event(new PusherEvent($task->title.' is deleted', $user));
            }
        }
        return response()->json('Deleted Successfully', 200);
    }
}