"""
Local Whisper Server for TeleMedicine Project
============================================
Runs OpenAI Whisper Large model locally on your laptop.
Exposes a simple HTTP API that Laravel calls instead of OpenAI's cloud.

Usage:
    python whisper_server.py

Server runs on: http://127.0.0.1:5001
"""

from flask import Flask, request, jsonify
import whisper
import tempfile
import os

app = Flask(__name__)

print("Loading Whisper base model... (this takes a few seconds)")
model = whisper.load_model("base")
print("Whisper large model loaded and ready!")


@app.route("/transcribe", methods=["POST"])
def transcribe():
    """
    Accepts an audio file upload and returns the transcription.
    POST /transcribe
    Form fields:
        - file: audio file (webm, wav, mp3, m4a, etc.)
        - language: 'bn' for Bengali, 'en' for English (optional)
    """
    if "file" not in request.files:
        return jsonify({"error": "No audio file uploaded"}), 400

    audio_file = request.files["file"]
    language   = request.form.get("language", "bn")

    # Save to a temp file
    suffix = os.path.splitext(audio_file.filename)[1] or ".webm"
    with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp:
        audio_file.save(tmp.name)
        tmp_path = tmp.name

    try:
        # Transcribe using Whisper large
        options = {}
        if language and language != "auto":
            options["language"] = language

        result = model.transcribe(tmp_path, **options)
        text = result.get("text", "").strip()

        return jsonify({
            "text": text,
            "language": result.get("language"),
            "model": "whisper-large"
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500

    finally:
        # Clean up temp file
        if os.path.exists(tmp_path):
            os.remove(tmp_path)


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "model": "whisper-large"})


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5001, debug=False)
