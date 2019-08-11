<?php

class reCaptcha {
    private const URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const SECRET = 'KEY';
    /**
     * @var string
     */
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function isValid()
    {
        $recaptcha = file_get_contents(self::URL . '?secret=' . self::SECRET . '&response=' . $this->token);
        $recaptcha = json_decode($recaptcha);

        return $recaptcha->success;
    }

}
