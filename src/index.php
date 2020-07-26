<?php
    ini_set('display_errors', 'off');
    require_once ('vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();

    require_once ('includes/dbConnection.php');
    require_once ('includes/recaptcha.php');
    require_once ('includes/Contact.php');
    require_once ('includes/ContactRepository.php');

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
	    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">

        <title>IFA - LadaClubHungary</title>
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
                <h3 style="text-align: center">Idegenforgalmi adó - Bejelentő lap</h3>
                <p>
                    Kérünk, hogy az alábbi bejelntőlapot töltsd ki és hozd magaddal, ennek hiányában sajnos nem tudunk beengedni a rendezvényre. Természetesen a rendezvény helyszínén is biztositunk bejelentőlapot.
                    <br /><br />
                    Kitöltés után le tudod tölteni a bejelentőlapot, melyet kerünk, hogy nyomtass ki és hozz magaddal.
                </p>
                <p class="<?=(count($error) ? 'has-error' : '')?>">
                    <?=implode('<br /><br />', $error)?>
                </p>
                <form method="POST" id="ifa_form" action="index.php">
                    <input type="hidden" name="token" id="token">
                    <div class="row">
                        <div class="six columns">
                            <label>Név</label>
                            <input type="text" class="u-full-width" id="name" name="name" value="<?=getEscapedValue('name', $contact)?>">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="two columns">
                            <label>Irsz</label>
                            <input type="text" class="u-full-width" id="zip" name="zip" value="<?=getEscapedValue('zip', $contact)?>">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="four columns">
                            <label>Gépjármű Rendszám</label>
                            <input type="text" class="u-full-width" id="reg_num" name="reg_num" value="<?=getEscapedValue('regNum', $contact)?>">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="four columns">
                            <label>Születési idő</label>
                            <input type="text" class="u-full-width" id="dob" name="dob" value="<?=getEscapedValue('dob', $contact)?>" data-toggle="datepicker" autocomplete="off">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="four columns">
                            <label>Állampolgárság</label>
                            <input type="text" class="u-full-width" id="nationality" name="nationality" value="<?=getEscapedValue('nationality', $contact)?>">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="four columns">
                            <label>Szemelyi igazolvany szám</label>
                            <input type="text" class="u-full-width" id="id_number" name="id_number" value="<?=getEscapedValue('idNumber', $contact)?>">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="six columns">
                            <label>Érkezés napja</label>
                            <input type="text" class="u-full-width" id="arrival_date" name="arrival_date" value="<?=getEscapedValue('arrivalDate', $contact)?>" data-toggle="datepicker" autocomplete="off">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="six columns">
                            <label>Távozás napja</label>
                            <input type="text" class="u-full-width" id="departure_date" name="departure_date" value="<?=getEscapedValue('departureDate', $contact)?>" data-toggle="datepicker" autocomplete="off">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="four columns">
                            <label>IFA mentesség jogcíme</label>
                            <select class="u-full-width" id="exemption" name="exemption">
                                <option value="Nincs" <?=($contact->getExemption() == 'Nincs' || empty($contact->getExemption()) ? 'selected' : '')?>>Nincs</option>
                                <option value="Kiskoru" <?=($contact->getExemption() == 'Kiskoru' ? 'selected' : '')?>>18. életévét be nem töltött magánszemély </option>
                                <option value="Soltvadkerti" <?=($contact->getExemption() == 'Soltvadkerti' ? 'selected' : '')?>>A településen lakóhellyel, tartózkodási hellyel rendelkező vendég </option>
                                <option value="70ev" <?=($contact->getExemption() == '70ev' ? 'selected' : '')?>>70. életévet betöltött személy </option>
                            </select>
                        </div>
                        <div class="five columns">
                            <label>Mentességet igazoló dokumentum neve</label>
                            <input type="text" class="u-full-width" id="exemption_proof_type" name="exemption_proof_type" value="<?=getEscapedValue('exemptionProofType', $contact)?>">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="three columns">
                            <label>száma</label>
                            <input type="text" class="u-full-width" id="exemption_proof_num" name="exemption_proof_num" value="<?=getEscapedValue('exemptionProofNum', $contact)?>">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="twelve columns">
                            <input type="checkbox" name="tcs" id="tcs" class="u-pull-left" style="margin-top: 0.5em; margin-right: 1em;">
                            <label for="tcs">Hozzájárulok, hogy az adataimat a LadaClubHungary kezelje és továbbadja Soltvadkert önkormányzatának</label>
                            <div class="error">Hozzájárulásod szükséges a bejelentőlap kitöltéséhez</div>
                        </div>
                    </div>
                    <input type="button" value="Küldés" id="form_submit_button" class="submit button button-primary">
                </form>
                <br />
                <div>
                    <p class="small">
                        SOLTVADKERT VÁROS ÖNKORMÁNYZATÁNAK 16/2016. (XII.1.) önkormányzati rendelete
                        alapján adókötelezettség terheli azt a magánszemélyt, aki nem állandó lakosként az önkormányzat illetékességi
                        területén legalább egy vendégéjszakát eltölt. Az adó alapja a megkezdett vendégéjszakák száma. Az
                        idegenforgalmi adó mértéke megkezdett vendégéjszakánként 300 Ft/fő.
                    </p>
                    <p class="small">
                        LadaClubHungary kijelenti, hogy az alábbi bejelentőlapon feltüntetett adatokat kizárólag a Soltvadkert Város
                        Önkormányzatának előírása alapján, a Turistavadász (www.turistavadasz.hu) felületre történő bevalláshoz
                        használja fel, utána azt megsemmisíti. Az adatok kezelője Soltvadkert Város Önkormányzata.
                    </p>
                </div>
            </div>
    </div>
    <script src="scripts/jquery-3.4.1.slim.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?=$_ENV['RECAPTCHA_KEY']?>"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('<?=$_ENV['RECAPTCHA_KEY']?>', {action: 'homepage'}).then(function(token) {
                $('#token').val(token);
            });
        });
    </script>
    <link  href="/ifa/libs/datepicker/datepicker.min.css" rel="stylesheet">
    <script src="/ifa/libs/datepicker/datepicker.min.js"></script>
    <script src="/ifa/libs/datepicker/datepicker.hu-HU.js"></script>
    <script src="scripts/scripts.js"></script>
    </body>
</html>
