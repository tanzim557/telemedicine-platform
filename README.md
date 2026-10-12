# ðŸ©º Telemedicine Platform with AI Voice Transcription

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![Python](https://img.shields.io/badge/Python-3.10%2B-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://www.python.org/)
[![OpenAI Whisper](https://img.shields.io/badge/OpenAI_Whisper-Speech_to_Text-412991?style=for-the-badge&logo=openai&logoColor=white)](https://openai.com/research/whisper)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

> An innovative AI-augmented Telemedicine consultation platform. Integrates a robust Laravel backend with OpenAI Whisper AI speech recognition to transcribe patient-doctor voice sessions directly into structured medical notes.

---

## ðŸŒŸ Key Highlights

- **ðŸŽ™ï¸ Real-Time Voice-to-Text Transcription**: Integrated Python Whisper microservice converts recorded patient audio symptoms directly into high-accuracy text.
- **ðŸ¤– Medical AI Consultation Service**: Automated parsing and summarization of patient inputs through MedicalAiService.php.
- **ðŸ‘¨â€âš•ï¸ Doctor Response Workflow**: Doctors can review transcriptions, adjust medical observations, and issue digital prescriptions.
- **ðŸ“Š Consultation Tracking**: Complete record history with ConsultationController.php, PatientInput.php, and DoctorResponse.php.
- **âš¡ Dual-Engine Architecture**: Independent, ultra-fast Python Whisper server connected seamlessly to Laravel REST API endpoints.

---

## ðŸ—ï¸ Project Architecture

`
TELE MEDICINE/
â”œâ”€â”€ whisper_server.py           # Python Whisper AI Transcription Microservice
â”œâ”€â”€ START_WHISPER_SERVER.bat    # Quick Launcher for AI Service
â”œâ”€â”€ START_LARAVEL_SERVER.bat    # Quick Launcher for Laravel Backend
â”œâ”€â”€ app/
â”‚   â”œâ”€â”€ Http/Controllers/ConsultationController.php
â”‚   â”œâ”€â”€ Models/Consultation.php, PatientInput.php, DoctorResponse.php
â”‚   â””â”€â”€ Services/MedicalAiService.php
â””â”€â”€ tele-med-app/               # Core Laravel Platform & Blade/Vite Views
`

---

## ðŸš€ Getting Started

### 1. Launch Whisper AI Speech Engine
`ash
# Setup Python environment
pip install -r requirements.txt # (openai-whisper, torch, flask)
python whisper_server.py
# Or double-click: START_WHISPER_SERVER.bat
`

### 2. Launch Laravel Telemedicine Server
`ash
composer install
php artisan migrate
php artisan serve
# Or double-click: START_LARAVEL_SERVER.bat
`

Visit http://localhost:8000 to start medical consultations.

---

## ðŸ‘¤ Author

Developed by **[Mubassher Alam Tanzim](https://github.com/tanzim557)**  
GitHub: [@tanzim557](https://github.com/tanzim557) â€¢ Email: mubassheralam0@gmail.com

---

## ðŸ“„ License
This project is licensed under the MIT License.
