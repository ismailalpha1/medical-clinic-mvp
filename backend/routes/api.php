<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClinicSubmissionController;
use App\Http\Controllers\Api\ContactInquiryController;

Route::get('/health', fn () => response()->json(['status' => 'ok']));
Route::get('/appointments/availability', [AppointmentController::class, 'availability'])->middleware('throttle:30,1');
Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('throttle:5,1');
Route::post('/clinic-submissions', [ClinicSubmissionController::class, 'store'])->middleware('throttle:10,1');
Route::post('/contact-inquiries', [ContactInquiryController::class, 'store'])->middleware('throttle:10,1');
