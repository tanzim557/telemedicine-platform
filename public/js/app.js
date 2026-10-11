document.addEventListener('DOMContentLoaded', () => {
    let mediaRecorder;
    let audioChunks = [];
    let audioBlob;

    const recordBtn = document.getElementById('recordBtn');
    const statusText = document.getElementById('statusText');
    const audioPreview = document.getElementById('audioPreview');
    const submitBtn = document.getElementById('submitBtn');
    const loadingDiv = document.getElementById('loading');
    const summaryCard = document.getElementById('summaryCard');

    if (recordBtn) {
        let isRecording = false;

        recordBtn.addEventListener('click', async () => {
            if (!isRecording) {
                // Start Recording
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];

                    mediaRecorder.ondataavailable = (event) => {
                        if (event.data.size > 0) {
                            audioChunks.push(event.data);
                        }
                    };

                    mediaRecorder.onstop = () => {
                        audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const audioUrl = URL.createObjectURL(audioBlob);
                        audioPreview.src = audioUrl;
                        audioPreview.classList.add('visible');
                        submitBtn.classList.add('visible');
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    recordBtn.classList.add('recording');
                    statusText.innerText = "Recording... Click to stop";
                } catch (err) {
                    console.error("Error accessing microphone:", err);
                    alert("Please allow microphone access to use this feature.");
                }
            } else {
                // Stop Recording
                mediaRecorder.stop();
                isRecording = false;
                recordBtn.classList.remove('recording');
                statusText.innerText = "Recording saved. You can listen or submit.";
                
                // Stop all tracks to release microphone
                mediaRecorder.stream.getTracks().forEach(track => track.stop());
            }
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', async () => {
            if (!audioBlob) return;

            // Determine if it's patient or doctor context from data attribute
            const context = submitBtn.dataset.context; // 'patient' or 'doctor'
            const userId = submitBtn.dataset.userId || 1; 
            const consultationId = submitBtn.dataset.consultationId || 1;

            const formData = new FormData();
            formData.append('audio', audioBlob, 'recording.webm');
            
            let url = '';
            if (context === 'patient') {
                formData.append('patient_id', userId);
                url = '/api/consultation/patient';
            } else {
                formData.append('doctor_id', userId);
                url = `/api/consultation/doctor/${consultationId}`;
            }

            // Show loading
            submitBtn.style.display = 'none';
            loadingDiv.classList.add('visible');
            statusText.innerText = "Processing AI translation...";

            try {
                // In a real app, you would fetch this API endpoint. 
                // For demonstration, we simulate the API call and response.
                
                /*
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                */
                
                // Simulated delay
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                loadingDiv.classList.remove('visible');
                summaryCard.classList.add('visible');
                
                if (context === 'patient') {
                    statusText.innerText = "Summary generated successfully!";
                } else {
                    statusText.innerText = "Advice sent to patient successfully!";
                }

            } catch (error) {
                console.error("Error submitting audio:", error);
                loadingDiv.classList.remove('visible');
                submitBtn.style.display = 'block';
                statusText.innerText = "Error processing audio. Please try again.";
            }
        });
    }
});
