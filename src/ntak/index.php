<?php
ini_set('display_errors', 'on');
require_once ('../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::create('../');
$dotenv->load();

$errors = [];

require_once ('../includes/dbConnection.php');
require_once('../includes/entities/Contact.php');
require_once('../includes/entities/Reservation.php');
require_once ('../includes/ContactRepository.php');
require_once ('../includes/ReservationRepository.php');
require_once ('../includes/ReservationStatuses.php');
require_once ('../includes/FelhoMatracClient.php');


$contactRepository = new ContactRepository($mysqli);
$reservationRepository = new ReservationRepository($mysqli);
$felhoMatracClient = new FelhoMatracClient($_ENV['FELHOMATRAC_CUSTOMER'], $_ENV['FELHOMATRAC_TOKEN']);

function validateDate(string $date, string $type) {
    $today = new DateTimeImmutable();
    $prevDay = $today->modify('-1 day');

    if ($date !== $prevDay->format('Y-m-d') && $date !== $today->format('Y-m-d')) {
        throw new Exception($type . ' napja mai vagy tegnapi lehet');
    }
}

if (isset($_GET['action'])) {
    try {
        switch($_GET['action']) {
            case 'claim':
                if (!empty($_GET['room'])) {
                    $contactsArray = $contactRepository->getByRoom($_GET['room']);
                    $contacts = array_map('Contact::createFrom', $contactsArray);

                    $reservation = new Reservation($mysqli);
                    $reservation->save();
                    $reservation = $felhoMatracClient->makeReservation($reservation, $contacts);

                    //update contact
                    foreach ($contacts as $contact) {
                        $contactRepository->updateContactReservation($contact->getId(), $reservation->getId());
                    }

                    header("Location: /ntak/index.php");
                }

                break;
            case 'arrival':
                if (!empty($_GET['reservationId'])) {
                    $contactsArray = $contactRepository->getByReservationId($_GET['reservationId']);
                    $contacts = array_map('Contact::createFrom', $contactsArray);

                    validateDate(FelhoMatracClient::getMinArrivalDate($contacts), 'Erkezes');

                    $reservation = Reservation::createFrom($mysqli, $reservationRepository->getById($_GET['reservationId']));
                    $reservation = $felhoMatracClient->setArrivalForReservation($reservation, $contacts);

                    header("Location: /ntak/index.php");
                }

                break;
            case 'departure':
                if (!empty($_GET['reservationId'])) {
                    $contactsArray = $contactRepository->getByReservationId($_GET['reservationId']);
                    $contacts = array_map('Contact::createFrom', $contactsArray);

                    validateDate(FelhoMatracClient::getMaxDepartureDate($contacts), 'Tavozas');

                    $reservation = Reservation::createFrom($mysqli, $reservationRepository->getById($_GET['reservationId']));
                    $reservation = $felhoMatracClient->setDepartureForReservation($reservation, $contacts);

                    header("Location: /ntak/index.php");
                }

                break;
            case 'delete':
                if (!empty($_GET['contactId'])) {
                    $contactRepository->deleteContact($_GET['contactId']);
                }

                break;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$reservationArray = $reservationRepository->getAll();
$reservations = [];
foreach($reservationArray as $res) {
    $reservation =  Reservation::createFrom($mysqli, $res);
    $reservations[$reservation->getId()] = $reservation;
}

$contacts = $contactRepository->getAll();
$groupedContacts = [];
foreach ($contacts as $contact) {
    $groupedContacts[$contact['room']][] = Contact::createFrom($contact);
}
$statusText = array_flip(ReservationStatuses::STATUS_CODES);
?>
<!DOCTYPE html>
<html>
    <head>
	    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">

        <title>NTAK</title>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <?php
            foreach($errors as $error) {
                echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
            }
            ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Szoba</th>
                    <th>Rendszam</th>
                    <th>Vendeg</th>
                    <th>IRSZ</th>
                    <th>Erkezes</th>
                    <th>Tavozas</th>
                    <th>Foglalasi statusz</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php

                /** @var Contact[] $contacts */
                foreach ($groupedContacts as $room => $contacts) {
                    $roomTd = '<td rowspan="' . count($contacts) . '" width="100"> '. $room .' </td>';
                    $reservationId = $contacts[0]->getReservationId();
                    $reservation = null;

                    if (is_null($reservationId)) {
                        $actionLink = './index.php?action=claim&room=' . urlencode($room);
                        $actionText = 'Igenyles';
                    } else {
                        $reservation = $reservations[$reservationId];
                        switch($reservation->getStatus()) {
                            case null:
                                break;
                            case ReservationStatuses::STATUS_CODES[ReservationStatuses::CLAIMED]:
                                $actionLink = './index.php?action=arrival&reservationId=' . $reservation->getId();
                                $actionText = 'Erkezes';
                                break;
                            case ReservationStatuses::STATUS_CODES[ReservationStatuses::ARRIVED]:
                                $actionLink = './index.php?action=departure&reservationId=' . $reservation->getId();
                                $actionText = 'Tavozas';
                                break;
                        }
                    }

                    foreach ($contacts as $contact) {
                        $editButton = '<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-id="' . $contact->getId() . '" data-bs-target="#editContactModal"><i class="bi-pencil"></i></button> ';

                        $actionButton = '';
                        if (!empty($actionLink) && !$contact->isDeleted()) {
                            $actionButton .= '<a href="' . $actionLink .'" class="btn btn-primary" role="button">' . $actionText . '</a>';
                        }

                        $deleteButton = '';
                        if (!$contact->isDeleted()) {
                            $deleteLink = './index.php?action=delete&contactId=' . $contact->getId();
                            $deleteButton = '<a href="' . $deleteLink .'" class="btn btn-danger" role="button" style="float: right;">Torles</a>';
                        }

                        echo '
                    <tr ' . ($contact->isDeleted() ? 'class="table-secondary"' : '') . '>
                    ';

                        echo $roomTd . '
                        <td width="100">' . $contact->getRegNum() . '</td>
                        <td width="200">' . $contact->getLastName() . ' ' . $contact->getFirstName() . '</td>
                        <td width="50" >' . $contact->getZip() . '</td>
                        <td width="120">' . $contact->getArrivalDate() . '</td>
                        <td width="120">' . $contact->getDepartureDate() . '</td>
                        <td width="50">' . (isset($reservation) && !empty($reservation->getStatus()) ? $statusText[$reservation->getStatus()] : '' ) . '</td>
                        <td width="200"> ' . (!$contact->isDeleted() ? $editButton . '&nbsp;' : '') . (!empty($roomTd) ? $actionButton . '&nbsp;' : '') . $deleteButton . '</td>
                     </tr>';

                        if (!empty($roomTd)) {
                            $roomTd = '';
                        }

                    }
                }
                echo '</table>';
                ?>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="editContactModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Szerkesztes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <iframe id="iframeModal" width="100%" height="400"></iframe>
              </div>
            </div>
          </div>
        </div>
    </body>
    <script src="../scripts/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
    <script>
        $('.modal').on('show.bs.modal', (event) => {
          const button = event.relatedTarget;
          const contactId = button.getAttribute('data-bs-id');
          $('#iframeModal').attr('src',`edit.php?contactId=${contactId}`);
        });

        $('.modal').on('hidden.bs.modal', () => {
          document.location.reload()
        });
    </script>
</html>
