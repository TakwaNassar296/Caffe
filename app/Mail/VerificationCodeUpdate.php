<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $verification_code;

    /**
     * Create a new message instance.
     */
    public function __construct($verification_code)
    {
        $this->verification_code = $verification_code;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('كود التحقق الخاص بك ')
                    ->view('emails.verification_web')
                    ->with('verificationCode', $this->verification_code);
    }
}
