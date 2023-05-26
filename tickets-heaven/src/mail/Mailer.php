<?php

namespace mail;

class Mailer {

    protected $mailer;

    protected $view;

    public function __construct($mailer, $view) {

        $this->mailer = $mailer;

        $this->view = $view;
    }

    public function send($template, $data, $callback) {

        $message = new Message($this->mailer);

        $message->body($this->parseView($template, $data));

        call_user_func($callback, $message);

        if ($_ENV['MAIL_DISABLE']) {

			return true;
		}

        $this->mailer->send();
    }

    protected function parseView($view, $viewData) {

		return $this->view->fetch($view, $viewData);
	}
}
