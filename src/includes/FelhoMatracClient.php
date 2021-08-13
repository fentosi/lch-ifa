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

    public function makeRoom(Reservation $reservation): Reservation {
        $reservation->setRoomHash(Uuid::uuid4()->toString());

        $response = $this->sendPostRequest('/room', $this->getRoomBody($reservation));
        $this->isResponseOK($response);

        return $reservation;
    }

    /**
     * @param Reservation $reservation
     * @param Contact[] $contacts
     * @return Reservation
     * @throws Exception
     */
    public function makeReservation(Reservation $reservation, array $contacts): Reservation {
        if ($reservation->getStatus() !== "") {
            throw new Exception('Wrong status for make a reservation!');
        }

        $reservation->setStatus(ReservationStatuses::STATUS_CODES[ReservationStatuses::CLAIMED]);
        $reservation->setReservationHash(Uuid::uuid4()->toString());

        $room = $contacts[0]->getRoom();
        if (strtolower($room) === $_ENV['UNIT_ROOM']) {
            $this->makeRoom($reservation);
        } else {
            $reservation->setRoomHash($room);
        }

        $response = $this->sendPostRequest(
            '/reservation',
            $this->getReservationBody($contacts, $reservation));

        $this->isResponseOK($response);

        $reservation->save();

        return $reservation;
    }

    public function setArrivalForReservation(Reservation $reservation, array $contacts): Reservation {
        if ($reservation->getStatus() != ReservationStatuses::STATUS_CODES[ReservationStatuses::CLAIMED]) {
            throw new Exception('Rossz status az erkezes beallitasahoz' . $reservation->getStatus());
        }

        $reservation->setStatus(ReservationStatuses::STATUS_CODES[ReservationStatuses::ARRIVED]);

        $response = $this->sendPostRequest(
            '/reservation',
            $this->getReservationBody($contacts, $reservation));

        $this->isResponseOK($response);

        $reservation->save();
        return $reservation;
    }

    public function setDepartureForReservation(Reservation $reservation, array $contacts): Reservation {
        if ($reservation->getStatus() != ReservationStatuses::STATUS_CODES[ReservationStatuses::ARRIVED]) {
            throw new Exception('Rossz status a tavozas beallitasahoz');
        }

        $reservation->setStatus(ReservationStatuses::STATUS_CODES[ReservationStatuses::DEPARTED]);

        $response = $this->sendPostRequest(
            '/reservation',
            $this->getReservationBody($contacts, $reservation));

        $this->isResponseOK($response);

        $reservation->save();
        return $reservation;
    }

    public function addDebitToReservation(array $contacts, Reservation $reservation): Reservation {
        $reservation->setDebitHash(Uuid::uuid4()->toString());

        $response = $this->sendPostRequest('/debit', $this->getDebitBody($reservation, $contacts));

        $this->isResponseOK($response);

        return $reservation;
    }


    /**
     * @param Contact[]
     * @param Reservation $reservation
     * @return array
     */
    private function getReservationBody(array $contacts, Reservation $reservation): array {
        return [
            'resNr' => 'FOGL' . $reservation->getId(),
            'resId' => $reservation->getReservationHash(),
            'status' => $reservation->getStatus(),
            'checkin' => self::getMinArrivalDate($contacts),
            'checkout' => self::getMaxDepartureDate($contacts),
            'channelId' => 'CH1',
            'channelName' => 'Channel 1',
            'channelNTAK' => 'KOZVETITO_ONLINE',
            'custRes' => $contacts[0]->getNationality(),
            'roomId' => $reservation->getRoomHash(),
            'guests' => $this->getReservationGuests($contacts)
        ];
    }

    /**
     * @param Contact[]
     * @return string
     */
    public static function getMinArrivalDate(array $contacts): string
    {
        return min(array_map(function($contact) {
            return $contact->getArrivalDate();
        }, $contacts));
    }

    /**
     * @param Contact[]
     * @return string
     */
    public static function getMaxDepartureDate(array $contacts): string
    {
        return max(array_map(function($contact) {
            return $contact->getDepartureDate();
        }, $contacts));
    }

    private function getReservationGuests(array $contacts): array {
        return array_map(function($contact) {
            $dob = DateTime::createFromFormat('Y.m.d.', $contact->getDob());

            return [
                'guestId' => $contact->getId(),
                'firstName' => $contact->getFirstName(),
                'lastName' => $contact->getLastName(),
                'gender' => 'EGYEB_VAGY_NEM_ISMERT',
                'zip' => $contact->getZip(),
                'city' => 'Budapest',
                'countryOfRes' => $contact->getNationality(),
                'countryOfNat' => $contact->getNationality(),
                'dob' => $dob->format('Y-m-d'),
                'ntakttax' => $this->getTaxExemption($contact->getExemption())
            ];
        }, $contacts);
    }

    private function getRequestHeaders(): array {
        return [
            'Accept'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ];
    }

    private function getRoomBody(Reservation $reservation)
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
            'buildingId' => 428,
            'type' => 'room',
            'roomName' => 'Satorhely' . $reservation->getId(),
            'roomEbeds' => 10,
            'roomId' => $reservation->getRoomHash()
        ];
    }

    private function getDebitBody(Reservation $reservation, array $contacts)
    {
        $now = new DateTime();
        return [
            'debitId' => $reservation->getDebitHash(),
            'resId' => $reservation->getReservationHash(),
            'productId' => 'Satorhely' . $reservation->getId(),
            'productName' => 'Satorhely ' . $reservation->getId(),
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
            throw new Exception('Error');
        }
    }

    private function getDaysBetweenDates($departureDate, $arrivalDate): int
    {
        $earlier = new DateTime($arrivalDate);
        $later = new DateTime($departureDate);

        return intval($later->diff($earlier)->format("%a"));
    }

    private function getTaxExemption($exemption)
    {
        $ntakException = 'KOTELES';

        switch ($exemption) {
            case 'Kiskoru':
                $ntakException = 'im1';
                break;
            case 'Helyi':
                $ntakException = 'im10';
                break;
        }

        return $ntakException;
    }
}
