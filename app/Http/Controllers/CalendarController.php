<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ExactivEM\Http\Requests;
use ExactivEM\Note;
use ExactivEM\News;
use Validator;

class CalendarController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        //default title and url
        $this->data['page']['title'] = 'Employee Dashboard';
    }
    
    function index(){
        $this->data['notes'] = Note::where('created_by_id',Auth::user()->id)->get()->all();
        $this->listNews();
        return view('calendar', $this->data);
    }

    function processDelete(Request $request){
        $note_info = Note::find($request->input('id'));

        //writelog
        $details = 'Deleted Note '. $note_info->title;
        $this->writeLog("Note", $details);
        
        //delete the note
        if($notes = Note::destroy($request->input('id')) ) {
            //return with success message
            return redirect()->back()->with('deleting', 'success');
        }
    }

    function processAdd(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'title' => 'required|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails())
        {
            return redirect()->back()
                    ->withErrors($validator,'adding_notes')
                    ->withInput();
        }
        
        $notes = new Note;
        $notes->title = $request['title'];
        $notes->description = $request['description'];
        $notes->created_by_id = Auth::user()->id;
        //save the note
        $notes->save();

        //writelog
        $details = 'Added Note '. $notes->title;
        $this->writeLog("Note", $details);

        //return success message
        return redirect()->back()->with('adding', 'success');
    }
}