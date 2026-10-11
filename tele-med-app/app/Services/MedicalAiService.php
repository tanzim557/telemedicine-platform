<?php

namespace App\Services;

use App\Services\MedicalKnowledgeBase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MedicalAiService
{
    protected $openaiKey;
    protected $googleTtsKey;
    protected $localWhisperUrl;

    public function __construct()
    {
        $this->openaiKey       = env('OPENAI_API_KEY');
        $this->googleTtsKey    = env('GOOGLE_CLOUD_API_KEY');
        $this->localWhisperUrl = env('LOCAL_WHISPER_URL', 'http://127.0.0.1:5001');
    }

    /**
     * Transcribe Audio using OpenAI Whisper API
     */
    public function transcribeAudio($filePath, $language = 'bn')
    {
        // ── Priority 1: Local Whisper Server (best quality, free, offline) ──
        try {
            $healthCheck = Http::timeout(2)->get($this->localWhisperUrl . '/health');
            if ($healthCheck->successful()) {
                Log::info('Using LOCAL Whisper server.');
                $response = Http::timeout(300)
                    ->attach('file', fopen($filePath, 'r'), 'audio.webm')
                    ->post($this->localWhisperUrl . '/transcribe', [
                        'language' => $language,
                    ]);
                if ($response->successful()) {
                    $text = $response->json()['text'] ?? null;
                    if ($text) return $text;
                }
            }
        } catch (\Exception $e) {
            Log::info('Local Whisper not running — trying OpenAI cloud.');
        }

        // ── Priority 2: OpenAI Whisper Cloud API ──
        if (!empty($this->openaiKey)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->openaiKey,
                ])
                ->attach('file', fopen($filePath, 'r'), 'audio.webm')
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model'    => 'whisper-1',
                    'language' => $language,
                ]);

                if ($response->successful()) {
                    return $response->json()['text'] ?? null;
                }

                $errorBody = $response->json();
                if (isset($errorBody['error']['code']) && $errorBody['error']['code'] === 'credit_balance_exhausted') {
                    Log::warning('OpenAI credit exhausted — using browser Web Speech fallback.');
                } else {
                    Log::error('OpenAI Whisper Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('OpenAI Whisper Exception: ' . $e->getMessage());
            }
        }

        // ── Priority 3: Return null — controller uses browser Web Speech text ──
        return null;
    }

    /**
     * Extract Clinical Summary from Bengali Patient Input using GPT-4o
     */
    public function extractClinicalSummary($bengaliText)
    {
        $prompt = "You are an expert medical translator and clinical summarizer. 
        A patient from Bangladesh has described their symptoms in colloquial Bengali (or sometimes mixed with English words). 
        Carefully understand their speech. Extract the key medical parameters (Symptoms, Duration, Severity) and translate this into a structured, highly professional English Clinical Summary for a US Doctor.
        If they say things like 'matha betha' (headache), 'jor' (fever), 'kashi' (cough), ensure it is properly diagnosed.
        Return the result as a STRICT JSON object with 'summary' (a brief string paragraph) and 'symptoms' (an array of strings). Do NOT include any markdown formatting like ```json in the output.
        
        Patient Text (Bengali): $bengaliText";

        return $this->callGpt4o($prompt);
    }

    /**
     * Summarize Doctor's Advice from English to Bengali using GPT-4o
     */
    public function summarizeDoctorAdvice($englishText)
    {
        $prompt = "You are an empathetic, expert medical translator. 
        A US Doctor has provided consultation notes in English for a Bengali patient.
        Please analyze the speech and extract the key points (Diagnosis, Medicine Names with Dosage in 1-0-1 format, Special Lifestyle Instructions).
        Translate everything into highly readable, empathetic, and clear Bengali text so the patient can easily understand it. Use simple Bengali terminology.
        Return the result as a STRICT JSON object with 'bengali_summary' (string, empathetic advice) and 'medicines' (array of objects with 'name' and 'dosage' in Bengali). Do NOT include any markdown formatting like ```json in the output.
        
        Doctor Text (English): $englishText";

        return $this->callGpt4o($prompt);
    }

    /**
     * Helper to call OpenAI GPT-4o API
     */
    private function callGpt4o($prompt)
    {
        if (empty($this->openaiKey) || str_contains($this->openaiKey, 'your-openai-key')) {

            if (str_contains($prompt, 'Patient Text (Bengali)')) {
                // ─── PATIENT PIPELINE (No API Key Mode) ─────────────────────────────

                // Step 1: Extract Bengali text from prompt
                preg_match('/Patient Text \(Bengali\):\s*(.*)/si', $prompt, $matches);
                $bnText = trim($matches[1] ?? 'No text provided');

                // Step 2: Map Bengali colloquial words → medical English terms
                $bnMap = MedicalKnowledgeBase::getBengaliSymptomMap();
                $lowerBn = mb_strtolower($bnText);
                $extractedSymptoms = [];

                foreach ($bnMap as $bnWord => $enTerm) {
                    if (str_contains($lowerBn, $bnWord)) {
                        $extractedSymptoms[] = $enTerm;
                    }
                }

                // Step 3: Also translate and extract from English translation
                $response = Http::get('https://translate.googleapis.com/translate_a/single?client=gtx&sl=bn&tl=en&dt=t&q=' . urlencode($bnText));
                $enText = '';
                if ($response->successful()) {
                    $data = $response->json();
                    foreach ($data[0] as $chunk) {
                        $enText .= $chunk[0];
                    }
                }

                // Step 4: Extract extra symptoms from English translation via duration/severity keywords
                $durationKeywords = ['yesterday', 'today', 'days', 'weeks', 'months', 'since', 'morning', 'night'];
                $foundDurations = [];
                $words = explode(' ', strtolower($enText));
                foreach ($words as $i => $word) {
                    if (in_array($word, $durationKeywords)) {
                        $ctx = array_slice($words, max(0, $i - 2), 5);
                        $foundDurations[] = implode(' ', $ctx);
                    }
                }

                // Step 5: Also run English symptom scan on translation
                $enDictionary = [
                    'fever' => 'fever', 'headache' => 'headache', 'cough' => 'cough',
                    'pain' => 'pain', 'hurt' => 'pain', 'ache' => 'pain', 'swollen' => 'swelling',
                    'nausea' => 'nausea', 'vomit' => 'vomiting', 'cold' => 'cold',
                    'weakness' => 'weakness', 'fatigue' => 'fatigue', 'tired' => 'fatigue',
                    'sleep' => 'insomnia', 'dizziness' => 'dizziness', 'dizzy' => 'dizziness',
                    'breath' => 'shortness of breath', 'bleeding' => 'bleeding',
                    'rash' => 'skin rash', 'itch' => 'itching', 'blurred' => 'blurred vision',
                    'thirst' => 'excessive thirst', 'urination' => 'frequent urination',
                    'chest' => 'chest pain', 'stomach' => 'abdominal pain', 'back' => 'back pain',
                    'throat' => 'sore throat', 'vomiting' => 'vomiting', 'chills' => 'chills',
                    'sweating' => 'sweating', 'insomnia' => 'insomnia', 'anxiety' => 'anxiety',
                    'constipation' => 'constipation', 'diarrhea' => 'diarrhea', 'bloating' => 'bloating',
                    'pressure' => 'blood pressure issue',
                ];
                $lowerEn = strtolower($enText);
                foreach ($enDictionary as $kw => $term) {
                    if (str_contains($lowerEn, $kw)) {
                        $extractedSymptoms[] = $term;
                    }
                }
                $extractedSymptoms = array_unique($extractedSymptoms);

                // Step 6: Diagnose and build clinical summary using knowledge base
                $matches2 = MedicalKnowledgeBase::diagnose($extractedSymptoms);
                $topDisease = !empty($matches2) ? reset($matches2) : null;

                if ($topDisease && $topDisease['score'] > 0) {
                    $summary = "CLINICAL SUMMARY (AI-Analyzed):\n";
                    $summary .= "Likely Condition: {$topDisease['name']}\n";
                    $summary .= "Urgency Level: " . strtoupper($topDisease['urgency']) . "\n";
                    $summary .= "Specialty: {$topDisease['category']}\n";
                    $summary .= "Presenting Symptoms: " . implode(', ', $extractedSymptoms) . ".";
                    if (!empty($foundDurations)) {
                        $summary .= " Duration noted: " . $foundDurations[0] . ".";
                    }
                    $summary .= "\nRecommendation: {$topDisease['advice']}";
                    if ($topDisease['urgency'] === 'critical') {
                        $summary .= "\n⚠️ URGENT: " . $topDisease['warning'];
                    }
                } else {
                    $summary = "Patient presents with: " . implode(', ', $extractedSymptoms) . ". Requires clinical evaluation. Duration: " . (!empty($foundDurations) ? $foundDurations[0] : 'Not specified') . ".";
                }

                return [
                    'summary'  => $summary,
                    'symptoms' => empty($extractedSymptoms) ? ['General Illness'] : array_values($extractedSymptoms),
                ];

            } else {
                // ─── DOCTOR PIPELINE (No API Key Mode) ──────────────────────────────

                // Step 1: Extract doctor's English text from prompt
                preg_match('/Doctor Text \(English\):\s*(.*)/si', $prompt, $matches);
                $enText = trim($matches[1] ?? 'Take care.');

                // Step 2: Detect symptoms mentioned by doctor in their advice
                $doctorSymptoms = [];
                $enDictionary2 = [
                    'fever' => 'fever', 'headache' => 'headache', 'cough' => 'cough',
                    'pain' => 'pain', 'swelling' => 'swelling', 'nausea' => 'nausea',
                    'vomiting' => 'vomiting', 'weakness' => 'weakness', 'fatigue' => 'fatigue',
                    'dizziness' => 'dizziness', 'breath' => 'shortness of breath',
                    'sleep' => 'insomnia', 'rash' => 'skin rash', 'itching' => 'itching',
                    'stomach' => 'stomach pain', 'chest' => 'chest pain', 'throat' => 'sore throat',
                    'diarrhea' => 'diarrhea', 'pressure' => 'blood pressure', 'diabetes' => 'diabetes',
                    'infection' => 'infection', 'asthma' => 'asthma', 'migraine' => 'migraine',
                    'back' => 'back pain', 'acidity' => 'acidity', 'anemia' => 'anemia',
                ];
                $lowerDr = strtolower($enText);
                foreach ($enDictionary2 as $kw => $term) {
                    if (str_contains($lowerDr, $kw)) {
                        $doctorSymptoms[] = $term;
                    }
                }

                // Step 3: Diagnose from doctor's notes and fetch standard medicines
                $adviceResult = MedicalKnowledgeBase::buildBengaliAdvice($doctorSymptoms, $enText);

                // Step 4: Translate any extra doctor advice to Bengali via free Google Translate
                $response = Http::get('https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=bn&dt=t&q=' . urlencode($enText));
                $bnText = '';
                if ($response->successful()) {
                    $data = $response->json();
                    foreach ($data[0] as $chunk) {
                        $bnText .= $chunk[0];
                    }
                }

                $finalBengali = $adviceResult['bengali_summary'];
                if (!empty($bnText)) {
                    $finalBengali .= "\n\nডাক্তারের অতিরিক্ত পরামর্শ: " . $bnText;
                }

                // Add warning if urgent
                if (!empty($adviceResult['warning'])) {
                    $warnResponse = Http::get('https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=bn&dt=t&q=' . urlencode($adviceResult['warning']));
                    if ($warnResponse->successful()) {
                        $warnData = $warnResponse->json();
                        $bnWarn = '';
                        foreach ($warnData[0] as $chunk) { $bnWarn .= $chunk[0]; }
                        $finalBengali .= "\n\n⚠️ সতর্কতা: " . $bnWarn;
                    }
                }

                return [
                    'bengali_summary' => $finalBengali,
                    'disease'         => $adviceResult['disease'],
                    'medicines'       => $adviceResult['medicines'],
                ];
            }
        }

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
        if (empty($this->googleTtsKey) || str_contains($this->googleTtsKey, 'your-google-tts-key')) {
            Log::info("Using mock TTS (No API Key).");
            // Copy a valid empty or silent mp3 file instead of corrupt text data
            $dummyPath = base_path('dummy.mp3');
            if (file_exists($dummyPath)) {
                copy($dummyPath, $outputPath);
            } else {
                // If missing, just touch an empty file (might still fail in some browsers but better than corrupt string)
                touch($outputPath);
            }
            return true;
        }

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
