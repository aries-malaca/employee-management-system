<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\File;
use ExactivEM\Http\Requests;

class FileController extends Controller
{
    function getFiles(Request $request){
        if($request->segment(3) !== null){
            return response()->json(File::where('employee_id', $request->segment(3))->get());
        }

        return response()->json(array());
    }


    function uploadFile(Request $request){
        //check if the file is submitted
        if($request->hasFile('file')) {
            $file = $request->file('file');

            $ext = $file->getClientOriginalExtension();
            //check if extension is valid
            if (in_array($ext, ['doc','docx','pdf','jpg','png','xls','xlsx'])) {
                $f = new File;
                $file->move('documents', $request->input('employee_id').'_'.$file->getClientOriginalName());
                $f->file_name = $request->input('employee_id').'_'.$file->getClientOriginalName();
                $f->description = $request->input('description');
                $f->category = $request->input('category');
                $f->employee_id = $request->input('employee_id');
                $f->save();
                return response()->json([ "command"=>"uploadFile","result"=>"success","file_name"=>$f->file_name] );
            }
        }
        return response()->json([ "command"=>"uploadFile","result"=>"failed"] );
    }

    function deleteFile(Request $request){
        File::destroy($request->input('id'));
        return response()->json([ "command"=>"deleteFile","result"=>"success"] );
    }
}
