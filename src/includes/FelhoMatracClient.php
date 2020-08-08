<?php

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;

class FelhoMatracClient
{
    private $token;
    private $client;

    public function __construct(string $customer, string $token)
    {
        $this->token = $token;
        $this->client = new Client([
            'base_uri' => "https://$customer.felhomatrac.hu/api"
        ]);
    }

    public function makeRooom() {
        $roomId = Uuid::uuid4();

        $response = $this->client->post('/room', [
            'body' => $this->getRoomBody($roomId),
            'headers' => $this->getRequestHeaders()
        ]);

        $code = $response->getStatusCode();

        if ($code !== 200) {
            throw new Error('Error');
        }

        return $roomId;
    }

    public function makeReservation(array $contacts) {
        $roomId = $this->makeRooom();
        $reservationHash = $this->getReservationHash($contacts);

        $response = $this->client->post('/reservation', [
            'body' => $this->getReservationBody($contacts, $reservationHash, $roomId),
            'headers' => $this->getRequestHeaders()
        ]);

        $code = $response->getStatusCode();

        if ($code !== 200) {
            throw new Error('Error');
        }

        $debitId = $this->addDebitToReservation($contacts, $reservationId);

        return [
            'reservationId' => $reservationId,
            'roomId' => $roomId,
            'debitId' => $debitId
        ];
    }

    public function addDebitToReservation(array $contacts, string $reservationHash) {
        $debitId = Uuid::uuid4()->toString();

        $response = $this->client->post('/debit', [
            'body' => $this->getDebitBody($debitId, $reservationHash, $contacts),
            'headers' => $this->getRequestHeaders()
        ]);

        $code = $response->getStatusCode();

        if ($code !== 200) {
            throw new Error('Error');
        }

        return $debitId;
    }


    private function getReservationBody(array $contacts, string $reservationId, string $roomId): array {
        return [
            'resNr' => 'FOGL001',
            'resId' => $reservationHash,
            'status' => 50,
            'checkin' => $contacts[0]->getArrivalDate(),
            'checkout' => $contacts[0]->getDepartureDate(),
            'channelId' => 'CH1',
            'channelName' => 'Channel 1',
            'channelNTAK' => 'KOZVETITO_ONLINE',
            'custRes' => 'HU',
            'roomId' => '',
            'guests' => $this->getReservationGuests($contacts)
        ];
    }

    private function getReservationGuests(array $contacts): array {
        return array_map(function($contact) {
            return [
                'guestId' => $contact->getId(),
                'firstName' => $contact->getName(),
                'lastName' => $contact->getName(),
                'gender' => 'EGYEB_VAGY_NEM_ISMERT',
                'zip' => $contact->getZip(),
                'city' => 'Budapest',
                'countryOfRes' => 'HU',
                'countryOfNat' => 'DE',
                'dob' => $contact->getDob(),
                'ntakttax' => 'KOTELES'
            ];
        }, $contacts);
    }

    private function getReservationHash(array $contacts): string {
        $concatenatedHash = '';

        array_walk($contacts, function($contact, $key, &$hash) {
            $hash .= $contact->getHash();
        }, $concatenatedHash);

        return md5($concatenatedHash);
    }

    private function getRequestHeaders(): array {
        return [
            'Accept'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ];
    }

    private function getRoomBody($roomId)
    {
        return [
            'unitName' => $_ENV['FELHOMATRAC_UNIT_NAME'],
            'unitCode' => $_ENV['FELHOMATRAC_UNIT_CODE'],
            'unitId' => $_ENV['FELHOMATRAC_UNIT_ID'],
            'catName' => 'Satorhely',
            'catNtak' => 'SATORHELY_KEMPINGHELY',
            'catDbeds' => 10,
            'catSbeds' => 0,
            'catId' => 1,
            'buildingName' => 'Satorhely',
            'buildingId' => 1,
            'type' => 'bed',
            'roomName' => 'Satorhely',
            'roomEbeds' => 10,
            'roomId' => $roomId,
            'bedId' => 1,
            'bedName' => 'Satorhely'
        ];
    }

    private function getDebitBody(string $debitId, string $reservationId, array $contacts)
    {
        $now = new DateTime();
        return [
            'debitId' => $debitId,
            'resId' => $reservationId,
            'productId' => 'Satorhely 1',
            'productName' => 'Satorhely',
            'productVat' => 0.27,
            'productNtak' => 'SZALLASDIJ',
            'consTime' => $now->format('Y-m-d H:i:s'),
            'currency' => 'HUF',
            'amount' => $this->getDebitAmount($contacts),
        ];
    }

    private function getDebitAmount(array $contacts)
    {
        $amount = 0;
        $dailyPrice = $_ENV['ACCOMODATION_DAILY_PRICE'];

        foreach ($contacts as $contact) {
            $amount += ($this->getDaysBettwenDates($contact->getDepartureDate(), $contact->getArrivalDate()) + 1) * $dailyPrice;
        }

        return $amount;
    }

    private function getDaysBettwenDates($departureDate, $arrivalDate)
    {
        $earlier = new DateTime($arrivalDate);
        $later = new DateTime($departureDate);

        return $later->diff($earlier)->format("%a");
    }
}
