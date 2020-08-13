<?php


class ContactRepository
{
    /**
     * @var mysqli
     */
    private $mysqli;
    /**
     * @var string
     */
    private $datePrefix;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
        $this->datePrefix = "2020-08-";
    }

    public function getByHash(string $hash)
    {
        if (!($statement = $this->mysqli->prepare(
            "
            SELECT
                id, last_name, first_name, zip, city, reg_num, dob, nationality, id_number, arrival_date, departure_date, exemption, exemption_proof_type, exemption_proof_num, reservation_id
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

    public function getById(int $id)
    {
        if (!($statement = $this->mysqli->prepare(
            "
            SELECT
                id, last_name, first_name, zip, city, reg_num, dob, nationality, id_number, arrival_date, departure_date, exemption, exemption_proof_type, exemption_proof_num, reservation_id
            FROM 
                ifa
            WHERE 
                id = ?"))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('d', $id);

        if (!$statement->execute()) {
            throw new Exception("Execute failed");
        }

        if (!($result = $statement->get_result())) {
            throw new Error("Getting result set failed");
        }

        return $result->fetch_assoc();
    }

    public function getAllWithReservationData()
    {
        $query = "
            SELECT
                ifa.id, last_name, first_name, zip, city, reg_num, dob, nationality, id_number, arrival_date, departure_date, reservation_id,  status
            FROM 
                ifa
            LEFT JOIN 
                ifa_reservation
            ON
                ifa_reservation.id = ifa.reservation_id
            ORDER BY last_name";

        if ($result = $this->mysqli->query($query)) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Execute failed");
        }
    }

    public function getAllWithReservationDataByStatus(int $status)
    {
        if (!($statement = $this->mysqli->prepare(
            "
            SELECT
                ifa.id, last_name, first_name, zip, city, reg_num, dob, nationality, id_number, arrival_date, departure_date, reservation_id, status
            FROM 
                ifa
            LEFT JOIN 
                ifa_reservation
            ON
                ifa_reservation.id = ifa.reservation_id
            WHERE 
                status = ?"))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('d', $status);

        if (!$statement->execute()) {
            throw new Exception("Execute failed");
        }

        if (!($result = $statement->get_result())) {
            throw new Error("Getting result set failed");
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllWithoutReservation()
    {
        $query = "
            SELECT
                ifa.id, last_name, first_name, zip, city, reg_num, dob, nationality, id_number, arrival_date, departure_date, reservation_id, status
            FROM 
                ifa
            LEFT JOIN 
                ifa_reservation
            ON
                ifa_reservation.id = ifa.reservation_id
            WHERE 
                reservation_id IS NULL
            ORDER BY reg_num";

        if ($result = $this->mysqli->query($query)) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Execute failed");
        }
    }

    public function updateContactReservation(Contact $contact, int $reservationId) {
        if (!($statement = $this->mysqli->prepare(
            "
                    UPDATE
                        ifa
                    SET
                        reservation_id = ? 
                    WHERE
                        id = ?
                        "))) {
            throw new Exception("SQL Statement error");
        }

        $contactId = $contact->getId();

        $statement->bind_param('dd', $reservationId, $contactId);

        if (!$statement->execute()) {
            $statement->close();
            throw new Exception("Databases insert error");
        }

        $statement->close();

    }

    public function saveContact(Contact $contact)
    {
        if (!($statement = $this->mysqli->prepare(
            "INSERT INTO 
                    ifa
                        (last_name, first_name, zip, city, reg_num, dob, nationality, id_number, arrival_date, departure_date, exemption, exemption_proof_type, exemption_proof_num, consent, hash, created)
                    VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now() )"))) {
            throw new Exception("SQL Statement error");
        }

        $last_name = $contact->getLastName();
        $first_name = $contact->getFirstName();
        $zip = $contact->getZip();
        $city = $contact->getCity();
        $regNum = $contact->getRegNum();
        $dob = $contact->getDob();
        $nationality = $contact->getNationality();
        $idNumber = $contact->getIdNumber();
        $arrivalDate = $this->datePrefix . $contact->getArrivalDate();
        $departureDate = $this->datePrefix . $contact->getDepartureDate();
        $exemption = $contact->getExemption();
        $exemptionProofType = $contact->getExemptionProofType();
        $exemptionProofNum = $contact->getExemptionProofNum();
        $consent = $contact->getConsent();
        $hash = $contact->getHash();

        $statement->bind_param('sssssssssssssss', $last_name, $first_name, $zip, $city, $regNum, $dob, $nationality, $idNumber, $arrivalDate, $departureDate, $exemption, $exemptionProofType, $exemptionProofNum, $consent, $hash);

        if (!$statement->execute()) {

            $statement->close();
            throw new Exception("Databases insert error");
        }

        $statement->close();
    }
}
