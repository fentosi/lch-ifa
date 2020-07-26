<?php

class reCaptcha {
    const URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var string
     */
    private $token;
    /**
     * @var string
     */
    private $secret;

    public function __construct(string $token, string $secret)
    {
        $this->token = $token;
        $this->secret = $secret;
    }

    public function isValid()
    {
        $recaptcha = file_get_contents(self::URL . '?secret=' . $this->secret . '&response=' . $this->token);
        $recaptcha = json_decode($recaptcha);

        return $recaptcha->success;
    }

}
