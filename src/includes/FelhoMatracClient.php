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

        $response = $this->client->post('/reservation', [
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

        return [
            'reservationHash' => $reservationHash,
            'roomId' => $roomId
        ];
    }


    private function getReservationBody(array $contacts, string $reservationHash): array {
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
}
