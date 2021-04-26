<?php
ini_set('display_errors', 'on');
require_once ('../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::create('../');
$dotenv->load();

$errors = [];

require_once ('../includes/dbConnection.php');
require_once ('../includes/entities/Contact.php');
require_once ('../includes/ContactRepository.php');
require_once ('../includes/Countries.php');
require_once ('../includes/recaptcha.php');

$contactRepository = new ContactRepository($mysqli);
$contactId = (isset($_GET['contactId']) ? intval($_GET['contactId']) : 0);

if ($contactId === 0) die();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['token'])) {
    $recaptcha = new reCaptcha($_POST['token'], $_ENV['RECAPTCHA_SECRET']);

    if ($recaptcha->isValid()) {
        $contactRepository = new ContactRepository($mysqli);

        $contact = Contact::createFrom($_POST);
        try {
            $contactRepository->saveContact($contact);
            header('Location: edit.php?contactId=' . $contact->getId());
        } catch (Exception $exception) {
            $errors[] = $exception->getMessage();
        }
    } else {
        $errors[] = 'Captcha ellenőrzés hiba, a form újraküldéséhez kattints a küldés gombra!';
    }
}

$contact = $contactRepository->getById($contactId);
$contact = Contact::createFrom($contact);

function getEscapedValue(string $key, Contact $contact) {
    $value = $contact->get($key);
    return empty($value) ? '' : htmlspecialchars($value);
}


?>
<!DOCTYPE html>
<html>
    <head>
	    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">

        <title>NTAK Contact Edit</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    </head>
    <body>
            <?php
            foreach($errors as $error) {
                echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
            }
            ?>
            <form method="post">
              <input type="hidden" id="id" name="id" value="<?=$contact->getId()?>" />
              <div class="row gx-5 gy-5">
                <div class="col">
                  <label class="form-label">Családi név</label>
                  <input type="text" class="form-control" id="last_name" name="last_name"
                         value="<?= getEscapedValue('last_name', $contact) ?>">
                </div>
                <div class="col">
                  <label class="form-label">Utónév</label>
                  <input type="text" class="form-control" id="first_name" name="first_name"
                         value="<?= getEscapedValue('first_name', $contact) ?>">
                </div>
                <div class="col">
                  <label class="form-label">Irsz</label>
                  <input type="text" class="form-control" id="zip" name="zip"
                         value="<?= getEscapedValue('zip', $contact) ?>">
                </div>
                <div class="col">
                  <label class="form-label">Varos</label>
                  <input type="text" class="form-control" id="city" name="city"
                         value="<?= getEscapedValue('city', $contact) ?>">
                </div>
              </div>
              <div class="row gx-5 gy-5">
                <div class="col">
                  <label class="form-label">Születési idő
                  </label>
                  <input type="text" pattern="([12]\d{3}\.(0[1-9]|1[0-2])\.(0[1-9]|[12]\d|3[01]))\."
                         class="form-control" id="dob" name="dob" value="<?= getEscapedValue('dob', $contact) ?>"
                         data-toggle="datepicker" autocomplete="off">
                </div>
                <div class="col">
                  <label class="form-label">Állampolgárság</label>
                  <select class="form-select" id="nationality" name="nationality">
                      <?php
                      $selectedValue = getEscapedValue('nationality', $contact);
                      $selectedValue = empty($selectedValue) ? 'HU' : $selectedValue;
                      foreach (Countries::EU as $code => $country) {
                          echo '
                            <option value="' . $code . '" ' . ($code === $selectedValue ? 'selected' : '') . '>' . $country['name'] . '</option>
                            ';
                      }
                      ?>
                  </select>

                </div>
                <div class="col">
                  <label class="form-label">Személyi igazolvány</label>
                  <input type="text" class="form-control" id="id_number" name="id_number"
                         value="<?= getEscapedValue('idNumber', $contact) ?>">
                </div>
                <div class="col">
                  <label class="form-label">Gépjármű Rendszám</label>
                  <input type="text" class="form-control" id="reg_num" name="reg_num"
                         value="<?= getEscapedValue('regNum', $contact) ?>">
                </div>
              </div>
              <div class="row gx-5 gy-5">
                <div class="col">
                  <label class="form-label" >Szoba</label>
                  <input type="text" class="form-control" id="room" name="room"
                         value="<?= getEscapedValue('room', $contact) ?>">
                </div>
                <div class="col">
                  <label class="form-label" >Érkezés napja</label>
                  <select id="arrival_date" name="arrival_date" class="form-select">
                    <option value=""></option>
                    <option value="17" <?= (getEscapedValue('arrivalDate', $contact) == ContactRepository::DATE_PREFIX . '17' ? 'selected' : '') ?>>2021. június 17.
                      csütörtök
                    </option>
                    <option value="18" <?= (getEscapedValue('arrivalDate', $contact) == ContactRepository::DATE_PREFIX . '18' ? 'selected' : '') ?>>2021. június 18.
                      péntek
                    </option>
                    <option value="19" <?= (getEscapedValue('arrivalDate', $contact) == ContactRepository::DATE_PREFIX . '19' ? 'selected' : '') ?>>2021. június 19.
                      szombat
                    </option>
                  </select>
                </div>
                <div class="col">
                  <label class="form-label" >Távozás napja</label>
                  <select id="departure_date" name="departure_date" class="form-select">
                    <option value=""></option>
                    <option value="18" <?= (getEscapedValue('departureDate', $contact) == ContactRepository::DATE_PREFIX . '18' ? 'selected' : '') ?>>2021. június 18.
                      péntek
                    </option>
                    <option value="19" <?= (getEscapedValue('departureDate', $contact) == ContactRepository::DATE_PREFIX . '19' ? 'selected' : '') ?>>2021. június 19.
                      szombat
                    </option>
                    <option value="20" <?= (getEscapedValue('departureDate', $contact) == ContactRepository::DATE_PREFIX . '20' ? 'selected' : '') ?>>2021. június 20.
                      vasárnap
                    </option>
                  </select>
                </div>
              </div>
              <div class="row gx-5 gy-5">
                <div class="col">
                  <button type="submit" class="btn btn-primary mt-3" style="float: right">Save</button>
                </div>
              </div>
            </form>
    </body>
</html>

<script src="https://www.google.com/recaptcha/api.js?render=<?= $_ENV['RECAPTCHA_KEY'] ?>"></script>
<script>
  grecaptcha.ready(function () {
    grecaptcha.execute('<?=$_ENV['RECAPTCHA_KEY']?>', {action: 'homepage'}).then(function (token) {
      $('#token').val(token);
    });
  });
</script>
