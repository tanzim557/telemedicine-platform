<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsultationController;

Route::post('/consultation/patient', [ConsultationController::class, 'submitPatientAudio']);
Route::post('/consultation/doctor/{id}', [ConsultationController::class, 'submitDoctorAudio']);
Route::get('/consultation/doctor/{id}', [ConsultationController::class, 'getDoctorDashboard']);
Route::get('/consultation/patient/{id}', [ConsultationController::class, 'getPatientResponse']);
