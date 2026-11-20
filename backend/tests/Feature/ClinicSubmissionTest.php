<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClinicSubmissionTest extends TestCase
{
    public function test_clinic_submission_rejects_an_unsupported_preferred_language(): void
    {
        $this->postJson('/api/clinic-submissions', [
            'clinic_name' => 'Care Clinic',
            'contact_person' => 'Alex Smith',
            'email' => 'alex@example.test',
            'phone' => '+34123456789',
            'country' => 'Spain',
            'city' => 'Madrid',
            'clinic_type' => 'Private clinic',
            'preferred_contact_method' => 'Email',
            'preferred_language' => 'fr',
            'consent' => true,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['preferred_language']);
    }
}
