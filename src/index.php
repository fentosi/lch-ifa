<?php
    require_once ('includes/recaptcha.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['token'])) {
        $recaptcha = new reCaptcha($_POST['token']);

        if ($recaptcha->isValid()) {
            echo 'valid';
        } else {
            echo 'error';
        }
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
                <h3>Idegenforgalmi adó - Bejelentő lap</h3>
                <p>
                    Kérünk, hogy az alábbi bejelntőlapot töltsd ki és hozd magaddal, ennek hiányában sajnos nem tudunk beengedni a rendezvénzre. Természetesen a rendezvény helyszínén is biztositunk bejelentőlapot.
                    <br /><br />
                    Kitöltés után le tudod tölteni a bejelentőlapot, melyet kerünk, hogy nyomtass ki és hozz magaddal.
                </p>
                <form method="POST" id="ifa_form" action="index.php">
                    <input type="hidden" name="token" id="token">
                    <div class="row">
                        <div class="six columns">
                            <label>Név</label>
                            <input type="text" class="u-full-width" id="name" name="name">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="two columns">
                            <label>Irsz</label>
                            <input type="text" class="u-full-width" id="zip" name="zip">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="four columns">
                            <label>Gépjármű Rendszám</label>
                            <input type="text" class="u-full-width" id="reg_num" name="reg_num">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="four columns">
                            <label>Születési idő</label>
                            <input type="text" class="u-full-width" id="dob" name="dob">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="four columns">
                            <label>Állampolgárság</label>
                            <input type="text" class="u-full-width" id="nationality" name="nationality">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="four columns">
                            <label>Szemelyi igazolvany szám</label>
                            <input type="text" class="u-full-width" id="id_number" name="id_number">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="six columns">
                            <label>Érkezés napja</label>
                            <input type="text" class="u-full-width" id="arrival_date" name="arrival_date">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="six columns">
                            <label>Távozás napja</label>
                            <input type="text" class="u-full-width" id="departure_date" name="departure_date">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="four columns">
                            <label>IFA mentesség jogcíme</label>
                            <select class="u-full-width" id="exemption" name="exemption">
                                <option value="Nincs">Nincs</option>
                                <option value="Kiskoru">18. életévét be nem töltött magánszemély </option>
                                <option value="Soltvadkerti">A településen lakóhellyel, tartózkodási hellyel rendelkező vendég </option>
                                <option value="70ev">70. életévet betöltött személy </option>
                            </select>
                        </div>
                        <div class="five columns">
                            <label>Mentességet igazoló dokumentum neve</label>
                            <input type="text" class="u-full-width" id="exemption_proof_type" name="exemption_proof_type">
                            <div class="error">A mező kitöltése kötelező</div>
                        </div>
                        <div class="three columns">
                            <label>száma</label>
                            <input type="text" class="u-full-width" id="exemption_proof_num" name="exemption_proof_num">
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
    <script src="https://www.google.com/recaptcha/api.js?render=6LeXebIUAAAAAIAFTC5xZXMGelSaDHjMukahuMQk"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('6LeXebIUAAAAAIAFTC5xZXMGelSaDHjMukahuMQk', {action: 'homepage'}).then(function(token) {
                $('#token').val(token);
            });
        });
    </script>
    <script src="scripts/scripts.js"></script>
    </body>
</html>
