<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MedicalAiService
{
    protected $openaiKey;
    protected $googleTtsKey;

    public function __construct()
    {
        $this->openaiKey = env('OPENAI_API_KEY');
        $this->googleTtsKey = env('GOOGLE_CLOUD_API_KEY'); // Or use Google Auth JSON
    }

    /**
     * Transcribe Audio using OpenAI Whisper API
     */
    public function transcribeAudio($filePath, $language = 'bn')
    {
        try {
            // Using Local Whisper Large Model Server (whisper_server.py)
            $response = Http::timeout(300) // Local transcription might take longer
                ->attach(
                    'file', fopen($filePath, 'r'), 'audio.wav'
                )
                ->post('http://127.0.0.1:5001/transcribe', [
                    'language' => $language,
                ]);

            if ($response->successful()) {
                return $response->json()['text'] ?? '';
            }

            Log::error('Local Whisper API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Local Whisper API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract Clinical Summary from Bengali Patient Input using GPT-4o
     */
    public function extractClinicalSummary($bengaliText)
    {
        $prompt = "You are an expert medical translator and clinical summarizer. 
        A patient has described their symptoms in Bengali. 
        Please extract the key medical parameters (Symptoms, Duration, Severity) and translate this into a structured English Clinical Summary for a US Doctor.
        Return the result as a JSON object with 'summary' (string) and 'symptoms' (array of strings).
        
        Patient Text (Bengali): $bengaliText";

        return $this->callGpt4o($prompt);
    }

    /**
     * Summarize Doctor's Advice from English to Bengali using GPT-4o
     */
    public function summarizeDoctorAdvice($englishText)
    {
        $prompt = "You are an expert medical translator. 
        A US Doctor has provided consultation notes in English.
        Please analyze the speech and extract the key points (Diagnosis, Medicine Names with Dosage in 1-0-1 format, Special Lifestyle Instructions).
        Translate everything into clear, actionable Bengali text for the patient.
        Return the result as a JSON object with 'bengali_summary' (string) and 'medicines' (array of objects with 'name' and 'dosage').
        
        Doctor Text (English): $englishText";

        return $this->callGpt4o($prompt);
    }

    /**
     * Helper to call OpenAI GPT-4o API
     */
    private function callGpt4o($prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiKey,
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful medical assistant that returns only valid JSON.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                return json_decode($content, true);
            }

            Log::error('GPT-4o API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('GPT-4o API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate Bengali Audio from Text using Google Cloud TTS
     */
    public function generateBengaliTts($text, $outputPath)
    {
        try {
            $response = Http::post('https://texttospeech.googleapis.com/v1/text:synthesize?key=' . $this->googleTtsKey, [
                'input' => [
                    'text' => $text
                ],
                'voice' => [
                    'languageCode' => 'bn-BD',
                    'name' => 'bn-BD-Standard-A'
                ],
                'audioConfig' => [
                    'audioEncoding' => 'MP3'
                ]
            ]);

            if ($response->successful()) {
                $audioContent = $response->json()['audioContent'];
                file_put_contents($outputPath, base64_decode($audioContent));
                return true;
            }

            Log::error('Google TTS API Error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Google TTS API Exception: ' . $e->getMessage());
            return false;
        }
    }
}
