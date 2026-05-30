<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function print(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        
        if (empty($ids) || empty($ids[0])) {
            return redirect()->back()->with('error', 'هیچ سوالی انتخاب نشده است.');
        }

        $questions = Question::with(['options', 'attachments'])->whereIn('id', $ids)->get();

        return view('exam.print', compact('questions'));
    }
}
