<?php

namespace App\Mail;

use App\Models\ContactInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactInquiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactInquiry $inquiry, public string $emailLocale = 'en') {}

    public function build(): self
    {
        $subject = $this->emailLocale === 'es'
            ? 'Nueva consulta desde el sitio web: '
            : 'New website inquiry: ';

        $mail = $this->subject($subject.$this->inquiry->subject)
            ->view('emails.contact-inquiry', ['locale' => $this->emailLocale]);

        if ($this->inquiry->email) {
            $mail->replyTo($this->inquiry->email, $this->inquiry->name);
        }

        return $mail;
    }
}
