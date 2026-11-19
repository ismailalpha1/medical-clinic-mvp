<?php

namespace App\Mail;

use App\Models\ClinicSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClinicLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ClinicSubmission $submission, public string $emailLocale = 'en') {}

    public function build(): self
    {
        $subject = $this->emailLocale === 'es'
            ? 'Nueva inscripción de clínica: '
            : 'New clinic registration: ';

        return $this->subject($subject.$this->submission->clinic_name)
            ->replyTo($this->submission->email, $this->submission->contact_person)
            ->view('emails.clinic-lead', ['locale' => $this->emailLocale]);
    }
}
