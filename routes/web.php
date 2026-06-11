<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [AuthController::class, 'showRegistrationForm']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/admin/exam/print', [ExamController::class, 'print'])->name('exam.print')->middleware('auth');

Route::get('/print/question/{question}', function (\App\Models\Question $question) {
    return view('print.question', compact('question'));
})->name('print.question')->middleware('auth');
