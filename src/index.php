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
                <form>
                    <div class="row">
                        <div class="six columns">
                            <label>Név</label>
                            <input type="text" class="u-full-width">                             
                        </div>
                        <div class="two columns">
                            <label>Irsz</label>
                            <input type="text" class="u-full-width">                            
                        </div>
                        <div class="four columns">
                            <label>Gépjármű Rendszám</label>
                            <input type="text" class="u-full-width">                                 
                        </div>
                    </div>
                    <div class="row">
                        <div class="four columns">
                            <label>Születési idő</label>
                            <input type="text" class="u-full-width">                                 
                        </div>
                        <div class="four columns">
                            <label>Állampolgárság</label>
                            <input type="text" class="u-full-width">                            
                        </div>
                        <div class="four columns">
                            <label>Szemelyi igazolvany szám</label>
                            <input type="text" class="u-full-width">                                 
                        </div>
                    </div>
                    <div class="row">
                        <div class="six columns">
                            <label>Érkezés napja</label>
                            <input type="text" class="u-full-width">                                 
                        </div>
                        <div class="six columns">
                            <label>Távozás napja</label>
                            <input type="text" class="u-full-width">                                 
                        </div>
                    </div>
                    <div class="row">
                        <div class="four columns">
                            <label>IFA mentesség jogcíme</label>
                            <select class="u-full-width">
                                <option value="Nincs">Nincs</option>
                                <option value="Kiskoru">18. életévét be nem töltött magánszemély </option>
                                <option value="Soltvadkerti">A településen lakóhellyel, tartózkodási hellyel rendelkező vendég </option>
                                <option value="70ev">70. életévet betöltött személy </option>
                            </select>
                        </div>
                        <div class="five columns">
                            <label>Mentességet igazoló dokumentum neve</label>
                            <input type="text" class="u-full-width">
                        </div>
                        <div class="three columns">
                            <label>száma</label>
                            <input type="text" class="u-full-width">
                        </div>                            
                    </div>
                    <div class="row">
                        <p>
                            <input type="checkbox" name="tcs" id="tcs" class="u-pull-left" style="margin-top: 0.5em; margin-right: 1em;">
                            <label for="tcs">Hozzájárulok, hogy az adataimat a LadaClubHungary kezelje és továbbadja Soltvadkert önkormányzatának</label>
                        </p>
                    </div>
                    <input type="button" value="Küldés" id="submit" class="submit button button-primary">
                </form>
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
    
    </body>
</html>
