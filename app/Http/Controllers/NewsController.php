<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use ExactivEM\Http\Requests;
use ExactivEM\News;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        //default title and url
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
        $this->data['page']['title'] = 'News';
        $this->data['page']['url'] =  'news';
    }
    
    function index(){
        //check user's restriction to the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        return view('news', $this->data);
    }

    function getNews(){
        return response()->json(News::leftJoin('users','news.posted_by_id','=','users.id')
                                        ->select('users.name','users.id as user_id','picture','news.*')
                                        ->orderBy('news.created_at','DESC')
                                        ->orderBy('priority','DESC')
                                        ->get());
    }

    function getActiveNews(){
        return response()->json(News::leftJoin('users','news.posted_by_id','=','users.id')
                                        ->where('is_active', 1)
                                        ->select('users.name','users.id as user_id','news.*','picture')
                                        ->orderBy('news.created_at','DESC')
                                        ->orderBy('priority','DESC')
                                        ->get());
    }

    function addNews(Request $request){
        //validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required'
        ]);
        //end of validation rules

        if ($validator->fails()) {
            return response()->json(["command"=>"addCompany","result"=>"failed","errors"=>$validator->errors()->all()]);
        }
        $news = new News;
        $news->title = $request->input('title');
        $news->description = $request->input('description');
        $news->posted_by_id = Auth::user()->id;
        $news->is_active = $request->input('is_active');
        $news->priority = $request->input('priority');
        $news->save();

        //write log
        $details = 'Added News '. $news->title;
        $this->writeLog("News", $details);
        //bring back the view for success
        return response()->json(["command"=>"addNews","result"=>"success"]);
    }

    function updateNews(Request $request){
        //validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required'
        ]);
        //end of validation rules

        if ($validator->fails()) {
            return response()->json(["command"=>"addCompany","result"=>"failed","errors"=>$validator->errors()->all()]);
        }
        $news = News::find($request->input('id'));
        $news->title = $request->input('title');
        $news->description = $request->input('description');
        $news->posted_by_id = Auth::user()->id;
        $news->is_active = $request->input('is_active');
        $news->priority = $request->input('priority');
        $news->save();

        //write log
        $details = 'Updated News '. $news->title;
        $this->writeLog("News", $details);
        //bring back the view for success
        return response()->json(["command"=>"updateNews","result"=>"success"]);
    }

    function deleteNews(Request $request){
        $news = News::find($request->input('id'));

        //write log
        $details = 'Deleted News '. $news->title;
        $this->writeLog("News", $details);

        News::destroy($request->input('id'));

        return response()->json(["command"=>"deleteNews","result"=>"success"]);
    }
}