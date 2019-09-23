<?php

use Dotenv\Dotenv;

die('NYE-NYE');

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

require_once ('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

require_once ('includes/dbConnection.php');


$countries = [
    'magyar' => 20,
    'finn' => 10,
    'osztrák' => 2,
    'szlovák' => 28,
];

$exempt = [
    'Nincs' => 0,
    'Kiskoru' => 1
];

function curlHeaderCallback($curl, $header) {
    $len = strlen($header);
    $header = explode(':', $header, 2);
    if (count($header) < 2) // ignore invalid headers
        return $len;

    $headers[strtolower(trim($header[0]))][] = trim($header[1]);

    return $len;
}

function makeFormDataString($ch, $data) {
    $data_string = '';
    foreach($data as $key=>$value) {
        $data_string .= $key . '=' . curl_escape($ch, $value) . '&';
    }
    rtrim($data_string, '&');

    return $data_string;
}

$phpSessionID = 'l8jot3790gjgs7bub0rmj0vun4';
$securityCode = 'QAnnIUJweOjI2j8fkxpn86s6w0kwoM0emnC5hlBw';
$settlementCode = 'rwvJuerjshakPLhgsZ726edfnsdiehgZT6wSu638';
$cookieString = "haver_telepules=soltvadkert; haver_telepules_biztonsagi_kod=$settlementCode; haver_szallasado_id=71; haver_biztonsagi_kod=$securityCode; haver_szallashely_id=72; PHPSESSID=$phpSessionID";

$ch = curl_init();
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADERFUNCTION,
    function($curl, $header) use (&$headers)
    {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) // ignore invalid headers
            return $len;

        $headers[strtolower(trim($header[0]))][] = trim($header[1]);

        return $len;
    }
);

$sql = "
    SELECT *, DATEDIFF(STR_TO_DATE(departure_date, '%Y-%m-%d'), STR_TO_DATE(arrival_date, '%Y-%m-%d')) as days
    FROM ifa
    LIMIT 300,100
";

if ($result = $mysqli->query($sql)) {
    while($row = $result->fetch_assoc()){
        $headers = [];

        $data = [
            'vendeg_neve' => iconv('UTF-8', 'ISO-8859-2', $row['name']),
            'e_mail' => '',
            'szem_ig_szam' => $row['id_number'],
            'utlevel_szam' => '',
            'submit' => 'Ment%E9s'
        ];
        $data_string = makeFormDataString($ch, $data);

        $url = 'https://www.turistavadasz.hu/tvadasz/szallasado_uj_vendeg.php';
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($data));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch,CURLOPT_COOKIE, $cookieString);

        $response = curl_exec($ch);

        if (isset($headers['location'])) {
            $url = $headers['location'][0];
            echo $url . "<br />";
        } else {
            die('location error phase1');
        }

        $dob = explode('-', $row['dob']);
        $headers = [];
        $data = array_merge($data, [
            'szul_ido_ev' => $dob[0],
            'szul_ido_ho' => $dob[1],
            'szul_ido_nap' => $dob[2],
            'lakohely' => $row['zip'],
            'allampolgarsag' => $countries[$row['nationality']],
            'allampolgarsag2' => '',
            'allampolgarsag_megjegyzes' => '',
            'vendeg_szuletesi_neve' => '',
            'vendeg_elozo_neve' => '',
            'vendeg_anyja_neve' => '',
            'vendeg_szul_hely_orszag' => '',
            'vendeg_szul_hely_varos' => '',
            'vendeg_tel' => '',
            'profil_id' => '1',
            'ertesules_id' => '',
            'ertesules_megjegyzes' => '',
            'aktiv' => 'N',
            'submit2' => 'Ment%E9s+%E9s+%FAj+bejelentkez%E9s',
        ]);
        $data_string = makeFormDataString($ch, $data);

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($data));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch,CURLOPT_COOKIE, $cookieString);

        $response = curl_exec($ch);

        if (isset($headers['location'])) {
            $url = $headers['location'][0];
            echo $url . "<br />";
        } else {
            die('location error phase2');
        }

        $headers = [];
        curl_setopt($ch,CURLOPT_URL, $url);
        $response = curl_exec($ch);

        if (isset($headers['location'])) {
            $url = $headers['location'][0];
            echo $url . "<br />";
        } else {
            die('location error phase3');
        }

        $data = [
            'ejszakak_szama' => $row['days'],
            'erkezes_datuma' => $row['arrival_date'],
            'tavozas_datuma' => $row['departure_date'],
            'szobaszam' => 'sator',
            'mentesseg_id_k[]' => $exempt[$row['exemption']],
            'mentesseg_megjegyzes' => '',
            'rendszam' => $row['reg_num'],
            'submit2' => 'Ment%E9s+%E9s+lez%E1r%E1s'
        ];

        $data_string = makeFormDataString($ch, $data);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($data));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch,CURLOPT_COOKIE, $cookieString);

        $response = curl_exec($ch);

        sleep(2);
    }
}

$result->close();

echo '<br /> ---- END ----- ';



