<?php
    ini_set('display_errors', 'on');
    require_once ('vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();

    require_once ('includes/dbConnection.php');
    require_once ('includes/recaptcha.php');
    require_once ('includes/Contact.php');
    require_once ('includes/IFAMaker.php');
    require_once ('includes/ContactRepository.php');

    $error = [];

    if (!empty($_GET['hash'])) {
        $contactRepository = new ContactRepository($mysqli);

        try {
            $contact = Contact::createFrom($contactRepository->getByHash($_GET['hash']));

            (new IFAMaker($contact))->make()->download();
        } catch (Exception $exception) {
            $error[] = $exception->getMessage();
        }
    }
