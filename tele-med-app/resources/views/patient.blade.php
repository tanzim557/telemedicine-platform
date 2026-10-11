<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard | Cross-Border Telemedicine</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
    <meta name="csrf-token" content="{{ csrf_token() ?? '' }}">
</head>
<body>
    <div class="glass-container">
        <header>
            <h1>রোগীর ড্যাশবোর্ড</h1>
            <p class="subtitle">আপনার সমস্যার কথা বাংলায় বলুন (Speak in Bengali)</p>
        </header>

        <main>
            <div class="recording-section">
                <button id="recordBtn" class="record-btn" title="Record Audio">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 14c1.66 0 2.99-1.34 2.99-3L15 5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
                    </svg>
                </button>
                <p id="statusText" class="status-text">Click the microphone to start recording</p>
                
                <audio id="audioPreview" class="audio-preview" controls></audio>
                
                <button id="submitBtn" class="submit-btn" data-context="patient" data-user-id="1">
                    Submit to US Doctor
                </button>
            </div>

            <div id="loading" class="loading">
                <div class="spinner"></div>
                <p>AI is analyzing and translating your symptoms...</p>
            </div>

            <div id="summaryCard" class="summary-card">
                <h3>Consultation Summary (For US Doctor)</h3>
                <div class="summary-content">
                    <p><strong>Symptoms:</strong> Severe headache, fever since 3 days.</p>
                    <p><strong>Duration:</strong> 3 Days</p>
                    <p><strong>Severity:</strong> High</p>
                </div>
            </div>

            <div id="doctorResponseCard" class="summary-card" style="margin-top: 1.5rem; display: none; background: rgba(80, 227, 194, 0.1); border-color: var(--secondary-color);">
                <h3 style="color: var(--secondary-color);">ডাক্তারের পরামর্শ (Doctor's Advice)</h3>
                <div class="doctor-content summary-content" style="font-size: 1.1rem; line-height: 1.8;">
                    <!-- Dynamic doctor content -->
                </div>
                <div style="margin-top: 1.5rem;">
                    <p style="font-weight: 500; margin-bottom: 0.5rem; color: var(--secondary-color);">🔊 ভয়েস মেসেজ শুনুন:</p>
                    <audio id="doctorAudioPreview" class="audio-preview visible" controls style="margin-top: 0;"></audio>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/app.js"></script>
</body>
</html>
