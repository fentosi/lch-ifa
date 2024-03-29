<?php
die();
ini_set('display_errors', 'on');
ini_set('max_execution_time', 0);

require_once('../../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::create('../../');
$dotenv->load();

require_once('../../includes/dbConnection.php');
require_once('../../includes/entities/Contact.php');
require_once('../../includes/ContactRepository.php');
require_once('../../includes/entities/Reservation.php');
require_once('../../includes/ReservationStatuses.php');
require_once('../../includes/FelhoMatracClient.php');

$contactRepository = new ContactRepository($mysqli);
$felhoMatracClient = new FelhoMatracClient($_ENV['FELHOMATRAC_CUSTOMER'], $_ENV['FELHOMATRAC_TOKEN']);

$errors = [];

try {
    $contacts = $contactRepository->getAllWithoutReservation();

    foreach ($contacts as $contact) {
        $contact = Contact::createFrom($contact);
        $reservation = new Reservation($mysqli);
        $reservation->save();
        $reservation = $felhoMatracClient->makeReservation($reservation, [$contact]);

        //update contact
        $contactRepository->updateContactReservation($contact->getId(), $reservation->getId());
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

print_r($errors);
