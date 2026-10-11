<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\PatientInput;
use App\Models\DoctorResponse;
use App\Services\MedicalAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConsultationController extends Controller
{
    protected $aiService;

    public function __construct(MedicalAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle patient audio submission
     */
    public function submitPatientAudio(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:wav,webm,mp3,m4a',
            'patient_id' => 'required|exists:users,id',
        ]);

        $audioFile = $request->file('audio');
        $fileName = 'patient_' . time() . '_' . Str::random(5) . '.' . $audioFile->getClientOriginalExtension();
        $filePath = $audioFile->storeAs('patient_audio', $fileName, 'public');
        $fullPath = storage_path('app/public/' . $filePath);

        // 1. Transcribe Bengali Audio to Bengali Text
        $bengaliText = $this->aiService->transcribeAudio($fullPath, 'bn');

        if (!$bengaliText) {
            return response()->json(['error' => 'Failed to transcribe audio'], 500);
        }

        // 2. Extract Clinical Summary & Translate to English
        $aiResult = $this->aiService->extractClinicalSummary($bengaliText);

        if (!$aiResult) {
            return response()->json(['error' => 'Failed to generate clinical summary'], 500);
        }

        // 3. Save to Database
        $consultation = Consultation::create([
            'patient_id' => $request->patient_id,
            'status' => 'pending',
        ]);

        PatientInput::create([
            'consultation_id' => $consultation->id,
            'raw_audio_path' => $filePath,
            'translated_english_summary' => $aiResult['summary'],
            'symptoms_json' => $aiResult['symptoms'] ?? [],
        ]);

        return response()->json([
            'message' => 'Consultation submitted successfully',
            'consultation_id' => $consultation->id
        ]);
    }

    /**
     * Get details for Doctor Dashboard
     */
    public function getDoctorDashboard(Request $request, $consultation_id)
    {
        $consultation = Consultation::with('patientInput', 'patient')->findOrFail($consultation_id);
        
        return response()->json([
            'consultation' => $consultation
        ]);
    }

    /**
     * Handle doctor audio advice submission
     */
    public function submitDoctorAudio(Request $request, $consultation_id)
    {
        $request->validate([
            'audio' => 'required|file|mimes:wav,webm,mp3,m4a',
            'doctor_id' => 'required|exists:users,id',
        ]);

        $consultation = Consultation::findOrFail($consultation_id);

        $audioFile = $request->file('audio');
        $fileName = 'doctor_' . time() . '_' . Str::random(5) . '.' . $audioFile->getClientOriginalExtension();
        $filePath = $audioFile->storeAs('doctor_audio', $fileName, 'public');
        $fullPath = storage_path('app/public/' . $filePath);

        // 1. Transcribe English Audio to English Text
        $englishText = $this->aiService->transcribeAudio($fullPath, 'en');

        if (!$englishText) {
            return response()->json(['error' => 'Failed to transcribe doctor audio'], 500);
        }

        // 2. Summarize Advice & Translate to Bengali
        $aiResult = $this->aiService->summarizeDoctorAdvice($englishText);

        if (!$aiResult) {
            return response()->json(['error' => 'Failed to summarize advice'], 500);
        }

        // 3. Generate Bengali TTS
        $ttsFileName = 'tts_' . time() . '_' . Str::random(5) . '.mp3';
        $ttsFilePath = 'bengali_tts/' . $ttsFileName;
        $ttsFullPath = storage_path('app/public/' . $ttsFilePath);

        // Ensure directory exists
        if (!file_exists(storage_path('app/public/bengali_tts'))) {
            mkdir(storage_path('app/public/bengali_tts'), 0777, true);
        }

        $ttsSuccess = $this->aiService->generateBengaliTts($aiResult['bengali_summary'], $ttsFullPath);

        // 4. Save to Database
        DoctorResponse::create([
            'consultation_id' => $consultation->id,
            'raw_audio_path' => $filePath,
            'extracted_bengali_summary' => $aiResult['bengali_summary'],
            'medicines_json' => $aiResult['medicines'] ?? [],
            'tts_audio_path' => $ttsSuccess ? $ttsFilePath : null,
        ]);

        $consultation->update([
            'doctor_id' => $request->doctor_id,
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'Advice submitted successfully'
        ]);
    }

    /**
     * Get patient's final results
     */
    public function getPatientResponse(Request $request, $consultation_id)
    {
        $consultation = Consultation::with('doctorResponse', 'doctor')->findOrFail($consultation_id);

        return response()->json([
            'consultation' => $consultation
        ]);
    }
}
