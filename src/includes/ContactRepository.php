<?php


class ContactRepository
{
    const FIELDS = 'ifa.id, last_name, first_name, zip, city, reg_num, dob, nationality, id_number, room, arrival_date, departure_date, exemption, exemption_proof_type, exemption_proof_num, reservation_id, deleted';
    const DATE_PREFIX = "2021-08-";

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
                " . self::FIELDS . "
            FROM 
                ifa
            WHERE 
                hash = ?"))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('s', $hash);

        $result = $this->executeStatement($statement);

        return $result->fetch_assoc();
    }

    public function getById(int $id)
    {
        if (!($statement = $this->mysqli->prepare(
            "
            SELECT
                " . self::FIELDS . "
            FROM 
                ifa
            WHERE 
                id = ?"))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('d', $id);

        $result = $this->executeStatement($statement);

        return $result->fetch_assoc();
    }

    /**
     * @param string $room
     * @return Contact[]
     * @throws Exception
     */
    public function getByRoom(string $room): array
    {
        if (!($statement = $this->mysqli->prepare(
            "
            SELECT
                " . self::FIELDS . "
            FROM 
                ifa
            WHERE 
                room = ?
            AND
                reservation_id IS NULL
                "))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('s', $room);

        $result = $this->executeStatement($statement);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * @param int $reservationId
     * @return Contact[]
     * @throws Exception
     */
    public function getByReservationId(int $reservationId): array
    {
        if (!($statement = $this->mysqli->prepare(
            "
            SELECT
                " . self::FIELDS . "
            FROM 
                ifa
            WHERE 
                reservation_id = ?
            AND
                room IS NOT NULL
                "))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('d', $reservationId);

        $result = $this->executeStatement($statement);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll()
    {
        $query = "
            SELECT
                " . self::FIELDS . "
            FROM 
                ifa
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
                " . self::FIELDS . ", status
            FROM 
                ifa
            LEFT JOIN 
                ifa_reservation
            ON
                ifa_reservation.id = ifa.reservation_id
            WHERE 
                deleted IS NULL
            AND
                status = ?"))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('d', $status);

        $result = $this->executeStatement($statement);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllWithoutReservation()
    {
        $query = "
            SELECT
                " . self::FIELDS . "
            FROM 
                ifa
            LEFT JOIN 
                ifa_reservation
            ON
                ifa_reservation.id = ifa.reservation_id
            WHERE 
                reservation_id IS NULL
            AND
                deleted IS NULL
            ORDER BY reg_num";

        if ($result = $this->mysqli->query($query)) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Execute failed");
        }
    }

    public function updateContactReservation(int $contactId, int $reservationId) {
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

        $statement->bind_param('dd', $reservationId, $contactId);

        if (!$statement->execute()) {
            $statement->close();
            throw new Exception("Databases insert error");
        }

        $statement->close();
    }

    public function deleteContact(int $contactId) {
        if (!($statement = $this->mysqli->prepare(
            "
                UPDATE
                    ifa
                SET
                    deleted = NOW() 
                WHERE
                    id = ?
            "))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('d', $contactId);

        if (!$statement->execute()) {
            $statement->close();
            throw new Exception("Databases update error");
        }

        $statement->close();
    }

    public function restoreContact(int $contactId) {
        if (!($statement = $this->mysqli->prepare(
            "
                UPDATE
                    ifa
                SET
                    deleted = NULL
                WHERE
                    id = ?
            "))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('d', $contactId);

        if (!$statement->execute()) {
            $statement->close();
            throw new Exception("Databases update error");
        }

        $statement->close();
    }

    public function saveContact(Contact $contact)
    {
        if (!($statement = $this->mysqli->prepare(
            "INSERT INTO 
                    ifa
                        (id, last_name, first_name, zip, city, reg_num, dob, nationality, id_number, room, arrival_date, departure_date, exemption, exemption_proof_type, exemption_proof_num, consent, hash, created)
                    VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now() )
                    ON DUPLICATE KEY UPDATE
                    last_name = ?,
                    first_name = ?,
                    zip = ?,
                    city = ?,
                    reg_num = ?,
                    dob = ?,
                    nationality = ?,
                    id_number = ?,
                    room = ?,
                    arrival_date = ?,
                    departure_date = ?
                    "))) {
            throw new Exception("SQL Statement error");
        }

        $id = $contact->getId();
        $last_name = $contact->getLastName();
        $first_name = $contact->getFirstName();
        $zip = $contact->getZip();
        $city = $contact->getCity();
        $regNum = $contact->getRegNum();
        $dob = $contact->getDob();
        $nationality = $contact->getNationality();
        $idNumber = $contact->getIdNumber();
        $arrivalDate = self::DATE_PREFIX. $contact->getArrivalDate();
        $departureDate = self::DATE_PREFIX . $contact->getDepartureDate();
        $exemption = $contact->getExemption();
        $exemptionProofType = $contact->getExemptionProofType();
        $exemptionProofNum = $contact->getExemptionProofNum();
        $consent = $contact->getConsent();
        $hash = $contact->getHash();
        $room = $contact->getRoom();

        $statement->bind_param('dsssssssssssssssssssssssssss', $id, $last_name, $first_name, $zip, $city, $regNum, $dob, $nationality, $idNumber, $room,
            $arrivalDate, $departureDate, $exemption, $exemptionProofType, $exemptionProofNum, $consent, $hash,
            $last_name, $first_name, $zip, $city, $regNum, $dob, $nationality, $idNumber, $room, $arrivalDate, $departureDate);

        if (!$statement->execute()) {

            $statement->close();
            throw new Exception("Databases insert error");
        }

        $statement->close();
    }

    private function executeStatement(mysqli_stmt $statement): mysqli_result
    {
        if (!$statement->execute()) {
            throw new Exception("Execute failed");
        }

        if (!($result = $statement->get_result())) {
            throw new Exception("Getting result set failed");
        }

        return $result;
    }
}
