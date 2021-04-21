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

if (isset($_GET['action']) && isset($_GET['contactId'])) {
    try {
        $contact = $contactRepository->getById(intval($_GET['contactId']));
        $contact = Contact::createFrom($contact);

        switch($_GET['action']) {
            case 'claim':
                $reservation = new Reservation($mysqli);
                $reservation->save();
                $reservation = $felhoMatracClient->makeReservation($reservation, [$contact]);

                //update contact
                $contactRepository->updateContactReservation($contact->getId(), $reservation->getId());

                break;
            case 'arrival':
                validateDate($contact->getArrivalDate(), 'Erkezes');

                $reservation = Reservation::createFrom($mysqli, $reservationRepository->getById($contact->getReservationId()));
                $reservation = $felhoMatracClient->setArrivalForReservation($reservation, [$contact]);

                break;
            case 'departure':
                validateDate($contact->getDepartureDate(), 'Tavozas');

                $reservation = Reservation::createFrom($mysqli, $reservationRepository->getById($contact->getReservationId()));
                $reservation = $felhoMatracClient->setDepartureForReservation($reservation, [$contact]);

                break;
            case 'delete':
                $contactRepository->deleteContact($contact->getId());
                break;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$contacts = $contactRepository->getAllWithReservationData();
$groupedContacts = $unitCount = $roomCount = [];
foreach ($contacts as $contact) {
    if (isset($unitCount[$contact['unit']])) {
        $unitCount[$contact['unit']]++;
    } else {
        $unitCount[$contact['unit']] = 1;
    }

    if (isset($roomCount[$contact['room']])) {
        $roomCount[$contact['room']]++;
    } else {
        $roomCount[$contact['room']] = 1;
    }

    $groupedContacts[$contact['unit']][$contact['room']][] = $contact;
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
                    <th>Lakoegyseg</th>
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

                foreach ($groupedContacts as $unit => $rooms) {
                  $unitTd = '<td rowspan="' . $unitCount[$unit] . '" width="100"> '. $unit .' </td>';
                  foreach ($rooms as $room => $contacts) {
                    $roomTd = '<td rowspan="' . $roomCount[$room] . '" width="100"> '. $room .' </td>';
                    foreach ($contacts as $contact) {
                        switch($contact['status']) {
                            case null:
                                $actionLink = './index.php?action=claim&contactId=' . $contact['id'];
                                $actionText = 'Igenyles';
                                break;
                            case ReservationStatuses::STATUS_CODES[ReservationStatuses::CLAIMED]:
                                $actionLink = './index.php?action=arrival&contactId=' . $contact['id'];
                                $actionText = 'Erkezes';
                                break;
                            case ReservationStatuses::STATUS_CODES[ReservationStatuses::ARRIVED]:
                                $actionLink = './index.php?action=departure&contactId=' . $contact['id'];
                                $actionText = 'Tavozas';
                                break;
                        }

                        $buttons = '<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-id="' . $contact['id'] . '" data-bs-target="#editContactModal">Szerkeszt</button>';
                        if (!empty($actionLink) && is_null($contact['deleted'])) {
                            $buttons .= '<a href="' . $actionLink .'" class="btn btn-primary" role="button">' . $actionText . '</a>';
                        }

                        if (is_null($contact['deleted'])) {
                            $deleteLink = './index.php?action=delete&contactId=' . $contact['id'];
                            $buttons .= '&nbsp;<a href="' . $deleteLink .'" class="btn btn-danger" role="button">Torles</a>';
                        }

                        echo '
                    <tr ' . (!is_null($contact['deleted']) ? 'class="table-secondary"' : '') . '>
                    ';
                        if (!empty($unitTd)) {
                          echo $unitTd;
                          $unitTd = '';
                        }

                        if (!empty($roomTd)) {
                            echo $roomTd;
                            $roomTd = '';
                        }

                        echo '
                        <td width="100">' . $contact['reg_num'] . '</td>
                        <td width="200">' . $contact['last_name'] . ' ' . $contact['first_name'] . '</td>
                        <td width="50" >' . $contact['zip'] . '</td>
                        <td width="120">' . $contact['arrival_date'] . '</td>
                        <td width="120">' . $contact['departure_date'] . '</td>
                        <td width="50">' . (isset($contact['status']) ? $statusText[$contact['status']] : '' ) . '</td>
                        <td width="200"> ' . $buttons . '</td>
                     </tr>';
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
        })
    </script>
</html>
