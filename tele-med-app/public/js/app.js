document.addEventListener('DOMContentLoaded', () => {
    const recordBtn    = document.getElementById('recordBtn');
    const statusText   = document.getElementById('statusText');
    const audioPreview = document.getElementById('audioPreview');
    const submitBtn    = document.getElementById('submitBtn');
    const loadingDiv   = document.getElementById('loading');
    const summaryCard  = document.getElementById('summaryCard');

    let mediaRecorder;
    let audioChunks = [];
    let audioBlob   = null;
    let isRecording = false;
    let liveTranscript = '';  // Web Speech API fallback text

    /* ─── Web Speech API (fallback for when Whisper has no credits) ─ */
    const context = submitBtn ? submitBtn.dataset.context : 'patient';
    let recognition = null;
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SR();
        recognition.continuous     = true;
        recognition.interimResults = true;
        recognition.lang           = context === 'patient' ? 'bn-BD' : 'en-US';
        recognition.onresult = (event) => {
            liveTranscript = '';
            for (let i = 0; i < event.results.length; i++) {
                liveTranscript += event.results[i][0].transcript;
            }
        };
        recognition.onerror = (e) => {
            console.warn('SpeechRecognition error (non-fatal):', e.error);
        };
    }

    /* ─── Recording ─────────────────────────────────────────────── */
    if (recordBtn) {
        recordBtn.addEventListener('click', async () => {
            if (!isRecording) {
                // START
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
                    audioChunks   = [];

                    mediaRecorder.ondataavailable = (e) => {
                        if (e.data.size > 0) audioChunks.push(e.data);
                    };

                    mediaRecorder.onstop = () => {
                        audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        audioPreview.src = URL.createObjectURL(audioBlob);
                        audioPreview.style.display = 'block';
                        submitBtn.style.display = 'block';
                        stream.getTracks().forEach(t => t.stop());
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    audioBlob   = null;
                    liveTranscript = '';
                    if (recognition) { try { recognition.start(); } catch(e) {} }
                    recordBtn.classList.add('recording');
                    statusText.innerText = '🔴 Recording... Click again to stop.';
                    submitBtn.style.display = 'none';
                    summaryCard.classList.remove('visible');
                    if (audioPreview) audioPreview.style.display = 'none';

                } catch (err) {
                    statusText.innerText = 'Microphone access denied. Please allow permissions.';
                }

            } else {
                // STOP
                mediaRecorder.stop();
                if (recognition) { try { recognition.stop(); } catch(e) {} }
                isRecording = false;
                recordBtn.classList.remove('recording');
                statusText.innerText = 'Recording saved! Click Submit to analyze.';
            }
        });
    }

    /* ─── Submit ─────────────────────────────────────────────────── */
    if (submitBtn) {
        submitBtn.addEventListener('click', async () => {
            if (!audioBlob) return;

            const context        = submitBtn.dataset.context;
            const userId         = submitBtn.dataset.userId;
            const consultationId = submitBtn.dataset.consultationId;

            const formData = new FormData();
            formData.append('audio', audioBlob, 'recording.webm');
            // Always send browser transcript as fallback (used if Whisper has no credit)
            if (liveTranscript) {
                formData.append('transcribed_text', liveTranscript);
            }
            if (context === 'patient') {
                formData.append('patient_id', userId);
            } else {
                formData.append('doctor_id', userId);
            }

            submitBtn.style.display = 'none';
            loadingDiv.classList.add('visible');
            statusText.innerText = '🤖 OpenAI Whisper is transcribing your voice...';

            try {
                const url = context === 'patient'
                    ? '/api/consultation/patient'
                    : `/api/consultation/doctor/${consultationId}`;

                const response = await fetch(url, {
                    method : 'POST',
                    body   : formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept'      : 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Server error occurred');
                }

                loadingDiv.classList.remove('visible');
                summaryCard.classList.add('visible');

                if (context === 'patient') {
                    statusText.innerText = '✅ AI Analysis Complete! Waiting for Doctor...';
                    if (data.data && data.data.clinical_summary) {
                        const s = JSON.parse(data.data.clinical_summary);
                        const contentDiv = summaryCard.querySelector('.summary-content');
                        if (contentDiv) {
                            const formattedSummary = (s.summary || '').replace(/\n/g, '<br>');
                            let html = `<div style="line-height:1.9;">${formattedSummary}</div>`;
                            if (s.symptoms && s.symptoms.length > 0) {
                                html += `<p style="margin-top:1rem;"><strong>🩺 Detected Symptoms:</strong></p>`;
                                html += `<ul style="margin:0.5rem 0 0 1rem;">`;
                                s.symptoms.forEach(sym => { html += `<li>${sym}</li>`; });
                                html += `</ul>`;
                            }
                            contentDiv.innerHTML = html;
                        }
                    }

                    // Start polling for Doctor's response
                    const doctorCard = document.getElementById('doctorResponseCard');
                    const doctorContentDiv = doctorCard.querySelector('.doctor-content');
                    const doctorAudio = document.getElementById('doctorAudioPreview');
                    
                    let pollInterval = setInterval(async () => {
                        try {
                            const res = await fetch(`/api/consultation/patient/${data.consultation_id}`);
                            const pollData = await res.json();
                            
                            if (pollData.consultation && pollData.consultation.status === 'completed') {
                                clearInterval(pollInterval);
                                statusText.innerText = '✅ Doctor has prescribed your treatment!';
                                
                                const doctorResp = pollData.consultation.doctor_response;
                                if (doctorResp && doctorResp.extracted_bengali_summary) {
                                    const advice = doctorResp.extracted_bengali_summary.replace(/\n/g, '<br>');
                                    let dHtml = `<p>${advice}</p>`;
                                    
                                    if (doctorResp.medicines_json && doctorResp.medicines_json.length > 0) {
                                        dHtml += `<p style="margin-top:1rem;"><strong>💊 ঔষধসমূহ:</strong></p>`;
                                        dHtml += `<ul class="medicine-list" style="margin-top: 0.5rem;">`;
                                        doctorResp.medicines_json.forEach(m => {
                                            dHtml += `<li class="medicine-item">
                                                <span class="med-name">${m.name}</span>
                                                <span class="med-dosage">${m.dosage}</span>
                                            </li>`;
                                        });
                                        dHtml += `</ul>`;
                                    }
                                    doctorContentDiv.innerHTML = dHtml;
                                    
                                    if (doctorResp.tts_audio_path) {
                                        doctorAudio.src = `/storage/${doctorResp.tts_audio_path}`;
                                        doctorAudio.style.display = 'block';
                                    }
                                    
                                    doctorCard.style.display = 'block';
                                    // Smoothly show it
                                    setTimeout(() => doctorCard.classList.add('visible'), 50);
                                }
                            }
                        } catch (e) {
                            console.error('Polling error:', e);
                        }
                    }, 5000);

                } else {
                    statusText.innerText = '✅ Advice sent to patient!';
                    if (data.data && data.data.bengali_summary) {
                        const s = JSON.parse(data.data.bengali_summary);
                        const contentDiv = summaryCard.querySelector('.summary-content');
                        if (contentDiv) {
                            let html = '';
                            if (s.disease) {
                                html += `<p><strong>🏥 Diagnosed Condition:</strong> ${s.disease}</p>`;
                            }
                            const advice = (s.bengali_summary || '').replace(/\n/g, '<br>');
                            html += `<p style="margin-top:0.75rem;">${advice}</p>`;
                            if (s.medicines && s.medicines.length > 0) {
                                html += `<p style="margin-top:1rem;"><strong>💊 Prescribed Medicines:</strong></p>`;
                                html += `<ul style="margin:0.5rem 0 0 1rem; line-height:2.2;">`;
                                s.medicines.forEach(m => {
                                    html += `<li><strong>${m.name}</strong> — ${m.dosage}`;
                                    if (m.duration) html += ` <em>(${m.duration})</em>`;
                                    if (m.note)     html += `<br><small style="color:#90caf9;">📌 ${m.note}</small>`;
                                    html += `</li>`;
                                });
                                html += `</ul>`;
                            }
                            contentDiv.innerHTML = html;
                        }
                    }
                }

            } catch (error) {
                loadingDiv.classList.remove('visible');
                submitBtn.style.display = 'block';
                statusText.innerText = '❌ Error: ' + error.message;
            }
        });
    }
});
