<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'raw_audio_path',
        'extracted_bengali_summary',
        'medicines_json',
        'tts_audio_path',
    ];

    protected $casts = [
        'medicines_json' => 'array',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
