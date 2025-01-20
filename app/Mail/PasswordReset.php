<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public $encryptedEmail;

    public $record;

    /**
     * Create a new message instance.
     */
    public function __construct($record, $encryptedEmail)
    {
        $this->record = $record;
        $this->encryptedEmail = $encryptedEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset',
        );
    }

    public function build()
    {
        return $this->subject('Password Reset')
            ->view('email.password-reset');
    }
}
