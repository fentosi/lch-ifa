<?php
    ini_set('display_errors', 'off');
    require_once('vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();

    require_once('includes/dbConnection.php');
    require_once('includes/recaptcha.php');
    require_once('includes/entities/Contact.php');
    require_once('includes/ContactRepository.php');
    require_once('includes/Countries.php');

    $error = [];

    $contact = Contact::createFrom($_POST);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['token'])) {
        $recaptcha = new reCaptcha($_POST['token'], $_ENV['RECAPTCHA_SECRET']);

        if ($recaptcha->isValid()) {
            $contactRepository = new ContactRepository($mysqli);

            try {
                $contactRepository->saveContact($contact);
                header('Location: thankyou.php?hash=' . $contact->getHash());
            } catch (Exception $exception) {
                $error[] = $exception->getMessage();
            }
        } else {
            $error[] = 'Captcha ellenőrzés hiba, a form újraküldéséhez kattints a küldés gombra!';
        }
    }

    function getEscapedValue(string $key, Contact $contact) {
        $value = $contact->get($key);
        return empty($value) ? '' : htmlspecialchars($value);
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">

    <title>Regisztracio - LadaClubHungary</title>
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/skeleton.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <img src="images/lchlogo.png" alt="LadaClubHungary Logo">
    </div>
</header>

<div class="content">
    <div class="container">
        <h3 style="text-align: center">NTAK - Bejelentő lap</h3>
        <p>
            Kérünk, hogy az alábbi bejelntőlapot töltsd ki és hozd magaddal, ennek hiányában sajnos nem tudunk beengedni
            a rendezvényre. Természetesen a rendezvény helyszínén is biztositunk bejelentőlapot.
            <br/><br/>
            Kitöltés után le tudod tölteni a bejelentőlapot, melyet kerünk, hogy nyomtass ki és hozz magaddal.
        </p>
        <p class="<?= (count($error) ? 'has-error' : '') ?>">
            <?= implode('<br /><br />', $error) ?>
        </p>
        <form method="POST" id="reg_form" action="index.php">
            <input type="hidden" name="token" id="token">
            <div class="row">
                <div class="four columns">
                    <label>Családi név</label>
                    <input type="text" class="u-full-width" id="last_name" name="last_name"
                           value="<?= getEscapedValue('last_name', $contact) ?>">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
                <div class="four columns">
                    <label>Utónév</label>
                    <input type="text" class="u-full-width" id="first_name" name="first_name"
                           value="<?= getEscapedValue('first_name', $contact) ?>">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>

                <div class="two columns">
                    <label>Irsz</label>
                    <input type="text" class="u-full-width" id="zip" name="zip"
                           value="<?= getEscapedValue('zip', $contact) ?>">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
                <div class="two columns">
                    <label>Város</label>
                    <input type="text" class="u-full-width" id="city" name="city"
                           value="<?= getEscapedValue('city', $contact) ?>">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
            </div>
            <div class="row">
                <div class="three columns">
                    <label>Születési idő
                        <small>(ÉÉÉÉ.HH.NN.)</small>
                    </label>
                    <input type="text" pattern="([12]\d{3}\.(0[1-9]|1[0-2])\.(0[1-9]|[12]\d|3[01]))\."
                           class="u-full-width" id="dob" name="dob" value="<?= getEscapedValue('dob', $contact) ?>"
                           data-toggle="datepicker" autocomplete="off">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
                <div class="three columns">
                    <label>Állampolgárság</label>
                    <select class="u-full-width" id="nationality" name="nationality">
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
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
                <div class="three columns">
                    <label>Szemelyi igazolvany</label>
                    <input type="text" class="u-full-width" id="id_number" name="id_number"
                           value="<?= getEscapedValue('idNumber', $contact) ?>">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
                <div class="three columns">
                    <label>Gépjármű Rendszám</label>
                    <input type="text" class="u-full-width" id="reg_num" name="reg_num"
                           value="<?= getEscapedValue('regNum', $contact) ?>">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
            </div>
            <div class="row">
                <div class="six columns">
                    <label>Érkezés napja</label>
                    2020. augusztus
                    <select id="arrival_date" name="arrival_date">
                        <option value=""></option>
                        <option value="27" <?= (getEscapedValue('arrivalDate', $contact) == 27 ? 'selected' : '') ?>>27.
                            csütörtök
                        </option>
                        <option value="28" <?= (getEscapedValue('arrivalDate', $contact) == 28 ? 'selected' : '') ?>>28.
                            péntek
                        </option>
                        <option value="29" <?= (getEscapedValue('arrivalDate', $contact) == 29 ? 'selected' : '') ?>>29.
                            szombat
                        </option>
                    </select>
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
                <div class="six columns">
                    <label>Távozás napja</label>
                    2020. augusztus
                    <select id="departure_date" name="departure_date">
                        <option value=""></option>
                        <option value="28" <?= (getEscapedValue('departureDate', $contact) == 28 ? 'selected' : '') ?>>
                            28. péntek
                        </option>
                        <option value="29" <?= (getEscapedValue('departureDate', $contact) == 29 ? 'selected' : '') ?>>
                            29. szombat
                        </option>
                        <option value="30" <?= (getEscapedValue('departureDate', $contact) == 30 ? 'selected' : '') ?>>
                            30. vasárnap
                        </option>
                    </select>
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
            </div>
            <div class="row">
                <div class="four columns">
                    <label>IFA mentesség jogcíme</label>
                    <select class="u-full-width" id="exemption" name="exemption">
                        <option value="Nincs" <?= ($contact->getExemption() == 'Nincs' || empty($contact->getExemption()) ? 'selected' : '') ?>>
                            Nincs
                        </option>
                        <option value="Kiskoru" <?= ($contact->getExemption() == 'Kiskoru' ? 'selected' : '') ?>>18.
                            életévét be nem töltött magánszemély
                        </option>
                        <option value="Soltvadkerti" <?= ($contact->getExemption() == 'Soltvadkerti' ? 'selected' : '') ?>>
                            A településen lakóhellyel, tartózkodási hellyel rendelkező vendég
                        </option>
                        <option value="70ev" <?= ($contact->getExemption() == '70ev' ? 'selected' : '') ?>>70. életévet
                            betöltött személy
                        </option>
                    </select>
                </div>
                <div class="five columns">
                    <label>Mentességet igazoló dokumentum neve</label>
                    <input type="text" class="u-full-width" id="exemption_proof_type" name="exemption_proof_type"
                           value="<?= getEscapedValue('exemptionProofType', $contact) ?>">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
                <div class="three columns">
                    <label>száma</label>
                    <input type="text" class="u-full-width" id="exemption_proof_num" name="exemption_proof_num"
                           value="<?= getEscapedValue('exemptionProofNum', $contact) ?>">
                    <div class="error">A mező kitöltése kötelező</div>
                </div>
            </div>
            <div class="row">
                <div class="twelve columns">
                    <input type="checkbox" name="tcs" id="tcs" class="u-pull-left"
                           style="margin-top: 0.5em; margin-right: 1em;">
                    <label for="tcs">Hozzájárulok, hogy az adataimat a LadaClubHungary kezelje és továbbadja a Nemzeti
                        Turisztikai Adatszolgáltató Központ (NTAK) felé</label>
                    <div class="error">Hozzájárulásod szükséges a bejelentőlap kitöltéséhez</div>
                </div>
            </div>
            <input type="button" value="Küldés" id="form_submit_button" class="submit button button-primary">
        </form>
        <br/>
        <div>
            <p class="small">
                A Magyar Kormány 235/2019. (X. 15.) valamint 239/2009. (X. 20.) rendelete alapján minden szálláshely
                szolgáltató adatszolgáltatási kötelezettséggel rendelkezik a Nemzeti Turisztikai Adatszolgáltató Központ
                (NTAK) felé.
            </p>
            <p class="small">
                LadaClubHungary kijelenti, hogy az alábbi bejelentőlapon feltüntetett adatokat a jogszabályi
                előírásoknak megfelelően kizárólag az NTAK felé történő adatszolgáltatásra használja fel, utána azt
                megsemmisíti.
            </p>
        </div>
    </div>
</div>
<script src="scripts/jquery-3.4.1.slim.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=<?= $_ENV['RECAPTCHA_KEY'] ?>"></script>
<script>
    grecaptcha.ready(function () {
        grecaptcha.execute('<?=$_ENV['RECAPTCHA_KEY']?>', {action: 'homepage'}).then(function (token) {
            $('#token').val(token);
        });
    });
</script>
<script src="scripts/scripts.js?202008050756"></script>
</body>
</html>
