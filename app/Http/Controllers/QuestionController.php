<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Question;

class QuestionController extends Controller
{
    
    
    public function index()
    {
        $data = DB::table('users')
                    ->join('questions', 'users.id', '=', 'questions.user_id')
                    ->select('users.id', 
                            'users.username',
                            'questions.title',
                            'questions.content','questions.id',
                            DB::raw('DATE_FORMAT(questions.created_at, "%b %e") as date_create'),
                            DB::raw('DATE_FORMAT(questions.created_at, "%H:%i") as time_create'))
                    ->get();
        
        return view('pages.question-list',['questions'=>$data]);
    }
    
    public function create()
    {
        return view('pages.question-add',['url'=>route('question.add')]);
    }

    public function store(Request $request)
    {
        $data = [
            'user_id' => 1,
            'title'   => $request->input('title'),
            'content' => $request->input('question'),
        ];
        
        $question = Question::create($data);
        return redirect()->route('question.detail',['id'=>$question->id]);
    }

    public function detailQuestion($id)
    {
        $question = DB::table('questions')
                    ->join('users', 'questions.user_id', '=', 'users.id')
                    ->select('users.id', 
                            'users.username',
                            'questions.title','questions.content',
                            'questions.id','questions.votes','questions.likes','questions.dislikes',
                            DB::raw('DATE_FORMAT(questions.created_at, "%b %e") as date_create'),
                            DB::raw('DATE_FORMAT(questions.created_at, "%H:%i") as time_create'))
                    ->where('questions.id',$id)
                    ->first();

        $question_comments = DB::table('question_comments')
                    ->join('users', 'question_comments.user_id', '=', 'users.id')
                    ->select('users.id', 
                            'users.username',
                            'question_comments.content',
                            DB::raw('DATE_FORMAT(question_comments.created_at, "%b %e") as date_create'),
                            DB::raw('DATE_FORMAT(question_comments.created_at, "%H:%i") as time_create'))
                    ->where('question_comments.question_id',$id)
                    ->get();
        
        $answers = DB::table('answers')
                    ->join('users', 'answers.user_id', '=', 'users.id')
                    ->select('users.id', 
                            'users.username',
                            'answers.content','answers.votes','answers.likes','answers.dislikes',
                            DB::raw('DATE_FORMAT(answers.created_at, "%b %e") as date_create'),
                            DB::raw('DATE_FORMAT(answers.created_at, "%H:%i") as time_create'))
                    ->where('answers.question_id',$id)
                    ->get();
        
        $data = [
            'question' => $question,
            'comments' => $question_comments,
            'answers'  => $answers
        ];
        
        return view('pages.question-detail',$data);
    }

    public function edit($id){
        $question = Question::find($id);
        $data = [
                'question' => $question,
                'url'      => route('question.edit',['id'=>$id])
            ];
        
        return view('pages.question-add',$data);
    }

    public function update($id,Request $request){
        $question = Question::find($id);

        $question->title   = $request->input('title');
        $question->content = $request->input('question');
        $question->save();

        return redirect()->route('question.detail',['id'=>$id]);
    }

    public function destroy($id){
        $question = DB::table('questions')->where('id',$id)->delete();
        return redirect()->route('question.list');
    }

}
