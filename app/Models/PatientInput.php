<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientInput extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'raw_audio_path',
        'translated_english_summary',
        'symptoms_json',
    ];

    protected $casts = [
        'symptoms_json' => 'array',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
