<?php


class Contact
{
    private $name;
    private $zip;
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

    public function __construct(
        string $name,
        string $zip,
        string $regNum,
        string $dob,
        string $nationality,
        string $idNumber,
        string $arrivalDate,
        string $departureDate,
        string $exemption,
        string $exemptionProofType,
        string $exemptionProofNum
        )
    {
        $this->name = $name;
        $this->zip = $zip;
        $this->regNum = $regNum;
        $this->dob = $dob;
        $this->nationality = $nationality;
        $this->idNumber = $idNumber;
        $this->arrivalDate = $arrivalDate;
        $this->departureDate = $departureDate;
        $this->exemption = $exemption;
        $this->exemptionProofType = $exemptionProofType;
        $this->exemptionProofNum = $exemptionProofNum;
        $this->consent = 'Hozzájárulok, hogy az adataimat a LadaClubHungary kezelje és továbbadja Soltvadkert önkormányzatának';
        $this->hash = spl_object_hash($this);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getZip(): string
    {
        return $this->zip;
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

    public function get($key) {
        return $this->$key;
    }

    public static function createFrom(array $data)
    {
        return new Contact(
            $data['name'] ?? '',
            $data['zip'] ?? '',
            $data['reg_num'] ?? '',
            $data['dob'] ?? '',
            $data['nationality'] ?? '',
            $data['id_number'] ?? '',
            $data['arrival_date'] ?? '',
            $data['departure_date'] ?? '',
            $data['exemption'] ?? '',
            $data['exemption_proof_type'] ?? '',
            $data['exemption_proof_num'] ?? ''
        );
    }
}
