<?php

namespace mail;

class Message {

    protected $mailer;

    public function __construct($mailer) {

        $this->mailer = $mailer;
    }

    public function from($address, $name = null) {

        $this->mailer->SetFrom($address, $name ?? '');
    }

    public function to($address, $name = null) {

        $this->mailer->addAddress($address, $name ?? '');
    }

    public function subject($subject) {

        $this->mailer->Subject = $subject;
    }

    public function body($body) {

        $this->mailer->Body = $body;
    }
}
