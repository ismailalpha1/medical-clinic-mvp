<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicSubmission extends Model
{
    protected $fillable = [
        'clinic_name', 'contact_person', 'email', 'phone', 'website',
        'country', 'city', 'address', 'clinic_type', 'specialties',
        'services', 'number_of_doctors', 'number_of_locations',
        'year_established', 'description', 'goals',
        'preferred_contact_method', 'consent',
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'goals' => 'array',
            'consent' => 'boolean',
            'year_established' => 'integer',
        ];
    }
}
