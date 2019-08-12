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

    public function get($key) {
        return $this->$key;
    }

    public static function createFromPost(array $post)
    {
        return new Contact(
            $post['name'] ?? '',
            $post['zip'] ?? '',
            $post['reg_num'] ?? '',
            $post['dob'] ?? '',
            $post['nationality'] ?? '',
            $post['id_number'] ?? '',
            $post['arrival_date'] ?? '',
            $post['departure_date'] ?? '',
            $post['exemption'] ?? '',
            $post['exemption_proof_type'] ?? '',
            $post['exemption_proof_num'] ?? ''
        );
    }
}
