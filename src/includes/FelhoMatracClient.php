<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

class FelhoMatracClient
{
    private $token;
    private $client;
    private $customer;

    public function __construct(string $customer, string $token)
    {
        $this->token = $token;
        $this->customer = $customer;
        $this->client = new Client();
    }

    public function makeRooom() {
        $roomId = Uuid::uuid4()->toString();

        $response = $this->sendPostRequest('/room', $this->getRoomBody($roomId));

        $this->isResponseOK($response);

        return $roomId;
    }

    public function makeReservation(array $contacts) {
        $roomId = $this->makeRooom();
        $reservationId = Uuid::uuid4()->toString();

        $response = $this->sendPostRequest(
            '/reservation',
            $this->getReservationBody($contacts, $reservationId, $roomId));

        $this->isResponseOK($response);

        $debitId = $this->addDebitToReservation($contacts, $reservationId);

        return [
            'reservationId' => $reservationId,
            'roomId' => $roomId,
            'debitId' => $debitId
        ];
    }

    public function addDebitToReservation(array $contacts, string $reservationHash) {
        $debitId = Uuid::uuid4()->toString();

        $response = $this->sendPostRequest('/debit', $this->getDebitBody($debitId, $reservationHash, $contacts));

        $this->isResponseOK($response);

        return $debitId;
    }


    private function getReservationBody(array $contacts, string $reservationId, string $roomId): array {
        return [
            'resNr' => 'FOGL001',
            'resId' => $reservationId,
            'status' => 10,
            'checkin' => $contacts[0]->getArrivalDate(),
            'checkout' => $contacts[0]->getDepartureDate(),
            'channelId' => 'CH1',
            'channelName' => 'Channel 1',
            'channelNTAK' => 'KOZVETITO_ONLINE',
            'custRes' => 'HU',
            'roomId' => $roomId,
            'guests' => $this->getReservationGuests($contacts)
        ];
    }

    private function getReservationGuests(array $contacts): array {
        return array_map(function($contact) {
            return [
                'guestId' => $contact->getId(),
                'firstName' => $contact->getFirstName(),
                'lastName' => $contact->getLastName(),
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

    private function getRequestHeaders(): array {
        return [
            'Accept'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ];
    }

    private function getRoomBody(string $roomId)
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
            'buildingName' => 'Kemping',
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
            $amount += ($this->getDaysBetweenDates($contact->getDepartureDate(), $contact->getArrivalDate()) + 1) * $dailyPrice;
        }

        return $amount;
    }

    private function sendPostRequest(string $uri, array $body): ResponseInterface
    {
        $request = new Request('POST', "https://{$this->customer}.felhomatrac.com/api" . $uri, $this->getRequestHeaders(), json_encode($body));

        $response = $this->client->send($request);

        return $response;
    }

    private function isResponseOK(ResponseInterface $response)
    {
        $code = $response->getStatusCode();

        if ($code !== 200) {
            throw new Error('Error');
        }
    }

    private function getDaysBetweenDates($departureDate, $arrivalDate): int
    {
        $earlier = new DateTime($arrivalDate);
        $later = new DateTime($departureDate);

        return intval($later->diff($earlier)->format("%a"));
    }
}
