<?php


class Contact
{
    private $last_name;
    private $first_name;
    private $zip;
    private $city;
    private $regNum;
    private $dob;
    private $nationality;
    private $idNumber;
    private $arrivalDate;
    private $departureDate;
    private $exemption;
    private $exemptionProofType;
    private $exemptionProofNum;
    private $consent;
    private $hash;
    private $reservationId;
    private $id;
    private $room;
    private $deleted;

    public function __construct(
        string $last_name,
        string $first_name,
        string $zip,
        string $city,
        string $regNum,
        string $dob,
        string $nationality,
        string $idNumber,
        string $room,
        string $arrivalDate,
        string $departureDate,
        string $exemption,
        string $exemptionProofType,
        string $exemptionProofNum,
        int $reservationId = null,
        int $id = null,
        string $deleted = null
        )
    {
        $this->last_name = $last_name;
        $this->first_name = $first_name;
        $this->zip = $zip;
        $this->city = $city;
        $this->regNum = $regNum;
        $this->dob = $dob;
        $this->nationality = $nationality;
        $this->idNumber = $idNumber;
        $this->arrivalDate = $arrivalDate;
        $this->departureDate = $departureDate;
        $this->exemption = $exemption;
        $this->exemptionProofType = $exemptionProofType;
        $this->exemptionProofNum = $exemptionProofNum;
        $this->consent = 'Hozzájárulok, hogy az adataimat a LadaClubHungary kezelje és továbbadja a Nemzeti Turisztikai Adatszolgáltató Központ (NTAK) felé';
        $this->hash = spl_object_hash($this);
        $this->id = $id;
        $this->reservationId = $reservationId;
        $this->room = $room;
        $this->deleted = $deleted;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getRegNum(): string
    {
        return $this->regNum;
    }

    public function getDob(): string
    {
        return $this->dob;
    }

    public function getNationality(): string
    {
        return $this->nationality;
    }


    public function getIdNumber(): string
    {
        return $this->idNumber;
    }

    public function getRoom(): string
    {
        return $this->room;
    }

    public function getArrivalDate(): string
    {
        return $this->arrivalDate;
    }

    public function getDepartureDate(): string
    {
        return $this->departureDate;
    }

    public function getExemption(): string
    {
        return $this->exemption;
    }

    public function getExemptionProofType(): string
    {
        return $this->exemptionProofType;
    }

    public function getExemptionProofNum(): string
    {
        return $this->exemptionProofNum;
    }

    public function getConsent(): string
    {
        return $this->consent;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getReservationId()
    {
        return $this->reservationId;
    }

    public function isDeleted(): bool
    {
        return !is_null($this->deleted);
    }

    public function get($key) {
        return $this->$key;
    }

    public static function createFrom(array $data)
    {
        return new Contact(
            $data['last_name'] ?? '',
            $data['first_name'] ?? '',
            $data['zip'] ?? '',
            $data['city'] ?? '',
            $data['reg_num'] ?? '',
            $data['dob'] ?? '',
            $data['nationality'] ?? '',
            $data['id_number'] ?? '',
            $data['room'] ?? '',
            $data['arrival_date'] ?? '',
            $data['departure_date'] ?? '',
            $data['exemption'] ?? '',
            $data['exemption_proof_type'] ?? '',
            $data['exemption_proof_num'] ?? '',
            $data['reservation_id'] ?? null,
            $data['id'] ?? null,
            $data['deleted'] ?? null
        );
    }
}
