<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $code;
    public $purpose;
    public $lang;

    /**
     * Create a new message instance.
     */
    public function __construct($user, string $code, string $purpose = 'register', string $lang = 'en')
    {
        $this->user = $user;
        $this->code = $code;
        $this->purpose = $purpose;
        $this->lang = $lang;
    }


    public function build()
    {

        App::setLocale($this->lang);


        $purposeLabel = __('emails.purposes.' . $this->purpose);
        if ($purposeLabel === 'emails.purposes.' . $this->purpose) {

            $purposeLabel = $this->purpose;
        }

        return $this
            ->subject(__('emails.otp_subject'))
            ->view('emails.otp')
            ->with([
                'user' => $this->user,
                'code' => $this->code,
                'purpose' => $this->purpose,
                'purposeLabel' => $purposeLabel,
            ]);
    }



    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Otp Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
