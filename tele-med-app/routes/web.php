<?php

use Illuminate\Support\Facades\Route;
use App\Models\Consultation;

Route::get('/', function () {
    return view('patient');
});

Route::get('/doctor', function () {
    $consultation = Consultation::with('patientInput')->latest()->first();
    return view('doctor', compact('consultation'));
});
