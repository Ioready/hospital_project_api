<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        // print_r($this->otp);die;
        return $this->view('email.otp')
                    ->subject('Your OTP Code')
                    ->with([
                        'otp' => $this->otp,
                    ]);

        // return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
        //     ->subject('Forget Password')
        //     ->view('email.otp')
        //     ->with([
        //         'otp' => $this->otp,
        //     ]);

    }
}
