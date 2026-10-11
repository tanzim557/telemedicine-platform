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
        set_time_limit(300);
        $request->validate([
            'audio'      => 'required|file',
            'patient_id' => 'required|exists:users,id',
        ]);

        $audioFile = $request->file('audio');
        $fileName  = 'patient_' . time() . '.' . ($audioFile->getClientOriginalExtension() ?: 'webm');
        $filePath  = $audioFile->storeAs('patient_audio', $fileName, 'public');
        $fullPath  = storage_path('app/public/' . $filePath);

        // 1. Try Whisper first; fall back to browser Web Speech API text
        $bengaliText = $this->aiService->transcribeAudio($fullPath, 'bn');

        if (!$bengaliText) {
            // Whisper unavailable — use live transcript from browser
            $bengaliText = $request->input('transcribed_text');
        }

        if (!$bengaliText) {
            return response()->json(['error' => 'Could not transcribe audio. Please try again.'], 500);
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
            'consultation_id' => $consultation->id,
            'data' => [
                'clinical_summary' => json_encode([
                    'summary' => $aiResult['summary'],
                    'symptoms' => $aiResult['symptoms'] ?? []
                ])
            ]
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
        set_time_limit(300);
        $request->validate([
            'audio'     => 'required|file',
            'doctor_id' => 'required|exists:users,id',
        ]);

        $consultation = Consultation::findOrFail($consultation_id);

        $audioFile = $request->file('audio');
        $fileName  = 'doctor_' . time() . '.' . ($audioFile->getClientOriginalExtension() ?: 'webm');
        $filePath  = $audioFile->storeAs('doctor_audio', $fileName, 'public');
        $fullPath  = storage_path('app/public/' . $filePath);

        // 1. Try Whisper first; fall back to browser Web Speech API text
        $englishText = $this->aiService->transcribeAudio($fullPath, 'en');

        if (!$englishText) {
            $englishText = $request->input('transcribed_text');
        }

        if (!$englishText) {
            return response()->json(['error' => 'Could not transcribe audio. Please try again.'], 500);
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
            'message' => 'Advice submitted successfully',
            'data' => [
                'bengali_summary' => json_encode([
                    'bengali_summary' => $aiResult['bengali_summary'],
                    'medicines' => $aiResult['medicines'] ?? []
                ])
            ]
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
