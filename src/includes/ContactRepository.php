<?php


class ContactRepository
{
    /**
     * @var mysqli
     */
    private $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function getByHash(string $hash)
    {
        if (!($statement = $this->mysqli->prepare(
            "
            SELECT
                name, zip, reg_num, dob, nationality, id_number, arrival_date, departure_date, exemption, exemption_proof_type, exemption_proof_num
            FROM 
                ifa
            WHERE 
                hash = ?"))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('s', $hash);

        if (!$statement->execute()) {
            throw new Exception("Execute failed");
        }

        if (!($result = $statement->get_result())) {
            throw new Error("Getting result set failed");
        }

        return $result->fetch_assoc();
    }

    public function saveContact(Contact $contact)
    {
        if (!($statement = $this->mysqli->prepare(
            "INSERT INTO 
                    ifa
                        (name, zip, reg_num, dob, nationality, id_number, arrival_date, departure_date, exemption, exemption_proof_type, exemption_proof_num, consent, hash, created)
                    VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now() )"))) {
            throw new Exception("SQL Statement error");
        }

        $name = $contact->getName();
        $zip = $contact->getZip();
        $regNum = $contact->getRegNum();
        $dob = $contact->getDob();
        $nationality = $contact->getNationality();
        $idNumber = $contact->getIdNumber();
        $arrivalDate = $contact->getArrivalDate();
        $departureDate = $contact->getDepartureDate();
        $exemption = $contact->getExemption();
        $exemptionProofType = $contact->getExemptionProofType();
        $exemptionProofNum = $contact->getExemptionProofNum();
        $consent = $contact->getConsent();
        $hash = $contact->getHash();

        $statement->bind_param('sssssssssssss', $name, $zip, $regNum, $dob, $nationality, $idNumber, $arrivalDate, $departureDate, $exemption, $exemptionProofType, $exemptionProofNum, $consent, $hash);

        if (!$statement->execute()) {

            $statement->close();
            throw new Exception("Databases insert error");
        }

        $statement->close();
    }
}
