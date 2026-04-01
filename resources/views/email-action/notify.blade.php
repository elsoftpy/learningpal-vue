@extends('layouts.email-action')

@section('title', __('Class Notification') . ' — ' . config('app.name'))

@section('page-title', __('Class Notification'))

@section('body')
<div class="w-full max-w-2xl space-y-4">
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-5">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">
            {{ __('Reminder Details') }}
        </p>
        <dl class="mt-3 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
            <div>
                <dt class="font-medium text-slate-500">{{ __('Student') }}</dt>
                <dd class="mt-1 text-base text-slate-900">{{ $actionContext['student_name'] }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-500">{{ __('Course') }}</dt>
                <dd class="mt-1 text-base text-slate-900">{{ $actionContext['course_name'] }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-500">{{ __('Teacher') }}</dt>
                <dd class="mt-1 text-base text-slate-900">{{ $actionContext['teacher_name'] }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-500">{{ __('Class Time') }}</dt>
                <dd class="mt-1 text-base text-slate-900">{{ $actionContext['class_time'] }}</dd>
            </div>
        </dl>
    </div>

    <p class="text-slate-700 text-base">
        {{ __('Please select one of the following options to let us know:') }}
    </p>

    {{-- Leave Pending option --}}
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
        <h2 class="text-lg font-semibold text-slate-800 mb-1">
            {{ __('Leave Class Pending') }}
        </h2>
        <p class="text-sm text-slate-600 mb-4">
            {{ __('Your class will be marked as pending and rescheduled at a later date.') }}
        </p>
        <form method="POST" action="{{ $pendingExecuteUrl }}">
            @csrf
            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 rounded-md bg-amber-500 hover:bg-amber-600 text-white font-medium text-sm transition-colors"
            >
                {{ __('Leave Pending') }}
            </button>
        </form>
    </div>

    {{-- Upload Task option --}}
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
        <h2 class="text-lg font-semibold text-slate-800 mb-1">
            {{ __('Request Class Record Upload') }}
        </h2>
        <p class="text-sm text-slate-600 mb-4">
            {{ __('Your class will be cancelled and the teacher will be asked to upload the class record to the platform.') }}
        </p>
        <form method="POST" action="{{ $uploadTaskExecuteUrl }}">
            @csrf
            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 rounded-md bg-red-500 hover:bg-red-600 text-white font-medium text-sm transition-colors"
            >
                {{ __('Upload Task') }}
            </button>
        </form>
    </div>

    <p class="text-xs text-slate-400 pt-2">
        {{ __('This page prevents automatic security scans from triggering actions on your behalf. Your manual selection is required.') }}
    </p>

</div>
@endsection
