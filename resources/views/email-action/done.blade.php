@extends('layouts.email-action')

@section('title', __('Request Received') . ' — ' . config('app.name'))

@section('page-title', __('Request Received'))

@section('body')
@php
    $doneStatus = $doneStatus ?? session('done_status');
@endphp

<div class="w-full max-w-2xl">
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-8 text-center">

        @if ($doneStatus === 'expired')

            <div class="flex justify-center mb-4 text-amber-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-slate-800 mb-2">
                {{ __('Link Expired') }}
            </h2>
            <p class="text-slate-600">
                {{ __('This link has expired. Please request a new one if you still need to take action.') }}
            </p>

        @elseif ($doneStatus === 'already')

            <div class="flex justify-center mb-4 text-amber-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-slate-800 mb-2">
                {{ __('Already Processed') }}
            </h2>
            <p class="text-slate-600">
                {{ __('This request has already been processed. No further action is needed.') }}
            </p>

        @else

            <div class="flex justify-center mb-4 text-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-slate-800 mb-2">
                {{ __('Request Received') }}
            </h2>
            <p class="text-slate-600">
                {{ __('Your request has been registered. We will be in touch shortly.') }}
            </p>

        @endif

    </div>
</div>
@endsection
