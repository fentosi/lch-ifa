<?php
    ini_set('display_errors', 'on');
    require_once ('../vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::create('../');
    $dotenv->load();

    require_once ('../includes/dbConnection.php');
    require_once('../includes/entities/Contact.php');
    require_once ('../includes/ContactRepository.php');
    require_once ('../includes/ReservationStatuses.php');

    $contactRepository = new ContactRepository($mysqli);

    $contacts = $contactRepository->getAllWithReservationData();

    $statusText = array_flip(ReservationStatuses::STATUS_CODES);
?>
<!DOCTYPE html>
<html>
    <head>
	    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">

        <title>NTAK</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
              integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <table class="table">
                <thead>
                <tr>
                    <th>Rendszam</th>
                    <th>Vendeg</th>
                    <th>IRSZ</th>
                    <th>Erkezes</th>
                    <th>Tavozas</th>
                    <th>Foglalasi statusz</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($contacts as $contact) {
                    echo '
                    <tr> 
                        <td width="100">' . $contact['reg_num'] . '</td>
                        <td width="200">' . $contact['last_name'] . ' ' . $contact['first_name'] . '</td>
                        <td width="50" >' . $contact['zip'] . '</td>
                        <td width="150">' . $contact['arrival_date'] . '</td>
                        <td width="150">' . $contact['departure_date'] . '</td>
                        <td width="50">' . (isset($contact['status']) ? $statusText[$contact['status']] : '' ) . '</td>
                     </tr>';
                }

                echo '</table>';
                ?>
                </tbody>
            </table>
        </div>
    </body>
</html>
