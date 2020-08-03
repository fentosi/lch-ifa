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
            <div class="container" style="text-align: center">
                <p class="<?=(count($error) ? 'has-error' : '')?>">
                    <?=implode('<br /><br />', $error)?>
                </p>
                <br />
                <p>
                    Kérünk, hogy a mielőbbi bejutás érdekében a bejelentőlapot kinyomtatva és alaírva hozd magaddal.
                    <br /><br />
                    A kitöltött bejelentőlapot <a href="ifa.php?hash=<?=$_GET['hash']?>">INNEN</a> tudod letölteni!
                    <br /><br />
                    Várunk 2020. augusztus 28-30 között Soltvadkerten, a 19. országos Ladatalálkozón!
                    <br /><br />
                    Az LCH csapata
                </p>
            </div>
    </div>
    <iframe src="ifa.php?hash=<?=$_GET['hash']?>" width="0" height="0" frameborder="0"></iframe>
    </body>
</html>
