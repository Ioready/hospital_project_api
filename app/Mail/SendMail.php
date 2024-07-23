<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mcessage;

    public function __construct($mcessage)
    {
        $this->mcessage = $mcessage;
    }

    public function build()
    {
        // print_r($this->otp);die;
        return $this->view('email.sendmailUser')
                    ->subject('Your Mail')
                    ->with([
                        'otp' => $this->mcessage,
                    ]);

        // return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
        //     ->subject('Forget Password')
        //     ->view('email.otp')
        //     ->with([
        //         'otp' => $this->otp,
        //     ]);

    }
}
