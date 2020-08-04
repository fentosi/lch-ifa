<?php
    ini_set('display_errors', 'on');
    require_once ('../vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::create('../');
    $dotenv->load();

    require_once ('../includes/dbConnection.php');
    require_once ('../includes/Contact.php');
    require_once ('../includes/ContactRepository.php');
    require_once ('../includes/FelhoMatracClient.php');

    $contactRepository = new ContactRepository($mysqli);

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $felhoMatracClient = new FelhoMatracClient($_ENV['FELHOMATRAC_CUSTOMER'], $_ENV['FELHOMATRAC_TOKEN']);

        if (isset($_POST['id'])) {
            $contacts = [];
            foreach($_POST['id'] as $id) {
                $contacts[] = Contact::createFrom($contactRepository->getById($id));
            }

            try {
                $reservationHash = $felhoMatracClient->makeReservation($contacts);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    }

    $contacts = $contactRepository->getAllWithoutReservation();

    $groupedContacts = [];
    foreach($contacts as $contact) {
        if (!isset($groupedContacts[$contact['reg_num']])) {
           $groupedContacts[$contact['reg_num']] = [];
        }

        $groupedContacts[$contact['reg_num']][] = $contact;
    }
?>
<!DOCTYPE html>
<html>
    <head>
	    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">

        <title>NTAK - LadaClubHungary</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
              integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <style>
            .btn.fixed {
                position: fixed;
                top: 5px;
                right: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <form method="post" action="index.php">
                <input type="submit" class="btn btn-primary fixed" value="Bekuld">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Rendszam</th>
                        <th>Vendegek</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($groupedContacts as $regNum => $guests) {
                        echo '
                        <tr> <td><input type="checkbox" name="regnum"></td> <td width="100">' . $regNum . '</td> <td><table>';
                        foreach ($guests as $guest) {
                            echo '
                            <tr>
                                <td><input type="checkbox" name="id[]" value="' . $guest['id'] . '" class="guest-id"></td>
                                <td width="200">' . $guest['name'] . '</td>
                                <td width="50" >' . $guest['zip'] . '</td>
                                <td width="150">' . $guest['arrival_date'] . '</td>
                                <td width="150">' . $guest['departure_date'] . '</td>
                            </tr>';
                        }
                        echo '</table></td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </form>
        </div>
    </body>

    <script src="../scripts/jquery-3.4.1.slim.min.js"></script>
    <script>
        $(document).ready(() => {
            $('input[name="regnum"]').click(function() {
                const me = $(this);
                me.parent().next().next().find('input').each(function(key, cbox) {
                    $(cbox).prop('checked', me.is(':checked'));
                });
            });
        });
    </script>
</html>
