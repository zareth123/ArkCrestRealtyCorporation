@extends('layouts.academy-exam')

@section('title', 'Module ' . sprintf('%02d', $moduleNumber) . ' Exam · ArkCrest Sales Academy')
@section('exam-title', 'Module ' . sprintf('%02d', $moduleNumber) . ' · Exam Mode')

{{-- $examExitUrl is read by layouts.academy-exam for the top-bar "Exit Exam"
     link — that link is protected by ExamLeaveGuard just like any other link
     on the page while an attempt is in progress. --}}
@php
    $moduleUrl = route('agent-training.module', $moduleNumber);
    $examExitUrl = $moduleUrl;
@endphp

@section('content')
    @include('training-course-quiz', [
        'module' => $moduleNumber,
        'questions' => $questions,
        'progress' => $module,
        'passingScore' => $passingScore,
        'nextModule' => $nextModule,
        'moduleUrl' => $moduleUrl,
        'resultsUrl' => route('agent-training.module.exam.results', $moduleNumber),
    ])
@endsection
