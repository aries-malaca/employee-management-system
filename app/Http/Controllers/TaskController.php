<?php

namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\Task;
use Illuminate\Support\Facades\Auth;
use Validator;

class TaskController extends Controller{
    function getMyTasks(){
        return response()
                    ->json(Task::where('employee_id', Auth::user()->id)
                                ->get());
    }

    function getEmployeeTasks(){
        return response()
            ->json(Task::whereIn('employee_id', $this->myDownLines())
                ->get());
    }

    function addTask(Request $request){
        //validation rules
        $validator = Validator::make($request->all(), [
            'task_title' => 'required',
            'task_description' => 'required',
            'task_started_date' => 'required',
            'task_target_completion_date' => 'required',
            'task_progress' => 'required'
        ]);
        //end of validation rules

        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"updateCompany","result"=>"failed","errors"=>$validator->errors()->all()]);
        }

        $task = new Task;
        $task->task_title = $request->input('task_title');
        $task->task_description = $request->input('task_description');
        $task->task_status = $request->input('task_status');
        $task->task_started_date = $request->input('task_started_date');
        $task->task_created_date = $request->input('task_created_date');
        $task->employee_id = Auth::user()->id;
        $task->task_completed_date = $request->input('task_completed_date');
        $task->task_approval = json_encode($request->input('task_approval'));
        $task->task_progress = $request->input('task_progress');
        $task->task_priority = $request->input('task_priority');
        $task->save();

        //writelog
        $details = 'Add Task '. $request->input('task_title');
        $this->writeLog("Tasks", $details);

        //bring back the view for success
        return response()->json(["command"=>"addTask","result"=>"success"]);

    }

    function updateTask(Request $request){

    }

    function deleteTask(Request $request){

    }
}