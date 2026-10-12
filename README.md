# Telemedicine Platform with AI Voice Transcription

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![Python](https://img.shields.io/badge/Python-3.10+-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://www.python.org/)
[![OpenAI Whisper](https://img.shields.io/badge/OpenAI_Whisper-Speech_to_Text-412991?style=for-the-badge&logo=openai&logoColor=white)](https://openai.com/research/whisper)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

An innovative AI-augmented Telemedicine consultation platform. Integrates a robust Laravel backend with OpenAI Whisper AI speech recognition to transcribe patient-doctor voice sessions directly into structured medical notes.

---

## Key Highlights

- Real-Time Voice-to-Text Transcription: Integrated Python Whisper microservice converts recorded patient audio symptoms directly into high-accuracy text.
- Medical AI Consultation Service: Automated parsing and summarization of patient inputs through MedicalAiService.
- Doctor Response Workflow: Doctors can review transcriptions, adjust medical observations, and issue digital prescriptions.
- Consultation Tracking: Complete record history with ConsultationController, PatientInput, and DoctorResponse models.
- Dual-Engine Architecture: Independent Python Whisper server connected seamlessly to Laravel REST API endpoints.

---

## Project Architecture

```
TELE MEDICINE/
├── whisper_server.py           # Python Whisper AI Transcription Microservice
├── START_WHISPER_SERVER.bat    # Quick Launcher for AI Service
├── START_LARAVEL_SERVER.bat    # Quick Launcher for Laravel Backend
├── app/
│   ├── Http/Controllers/ConsultationController.php
│   ├── Models/Consultation.php, PatientInput.php, DoctorResponse.php
│   └── Services/MedicalAiService.php
└── tele-med-app/               # Core Laravel Platform and Views
```

---

## Getting Started

### 1. Launch Whisper AI Speech Engine
```bash
# Setup Python environment
pip install -r requirements.txt
python whisper_server.py
# Or run: START_WHISPER_SERVER.bat
```

### 2. Launch Laravel Telemedicine Server
```bash
composer install
php artisan migrate
php artisan serve
# Or run: START_LARAVEL_SERVER.bat
```

Visit http://localhost:8000 to start medical consultations.

---

## Author

Developed by Mubassher Alam Tanzim
- GitHub: https://github.com/tanzim557
- Email: mubassheralam0@gmail.com

---

## License
This project is licensed under the MIT License.
