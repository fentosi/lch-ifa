<?php

ini_set('display_errors', 'on');
ini_set('max_execution_time', 0);

require_once('../../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::create('../');
$dotenv->load();

require_once('../../includes/dbConnection.php');
require_once('../../includes/entities/Contact.php');
require_once('../../includes/ContactRepository.php');
require_once('../../includes/ReservationRepository.php');
require_once('../../includes/entities/Reservation.php');
require_once('../../includes/ReservationStatuses.php');
require_once('../../includes/FelhoMatracClient.php');

$contactRepository = new ContactRepository($mysqli);
$reservationRepository = new ReservationRepository($mysqli);
$felhoMatracClient = new FelhoMatracClient($_ENV['FELHOMATRAC_CUSTOMER'], $_ENV['FELHOMATRAC_TOKEN']);

$errors = [];

try {
    $prevDay = (new DateTime())->modify('-1 day');
    $contacts = $contactRepository->getAllWithReservationDataByStatus(ReservationStatuses::STATUS_CODES[ReservationStatuses::CLAIMED]);
    $arrivedContacts = array_filter($contacts, function($contact) use ($prevDay) {
        return $contact['arrival_date'] === $prevDay->format('Y-m-d');
    });

    foreach ($arrivedContacts as $contact) {
        $contact = Contact::createFrom($contact);
        $reservation = Reservation::createFrom($mysqli, $reservationRepository->getById($contact->getReservationId()));
        $reservation = $felhoMatracClient->setArrivalForReservation($reservation, [$contact]);
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

print_r($errors);
