<?php


class ReservationRepository
{
    /**
     * @var mysqli
     */
    private $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function getById(int $id)
    {
        if (!($statement = $this->mysqli->prepare(
            "
            SELECT
                id, reservation_hash, debit_hash, room_hash, status
            FROM 
                ifa_reservation
            WHERE 
                id = ?"))) {
            throw new Exception("SQL Statement error");
        }

        $statement->bind_param('d', $id);

        if (!$statement->execute()) {
            throw new Exception("Execute failed");
        }

        if (!($result = $statement->get_result())) {
            throw new Exception("Getting result set failed");
        }

        return $result->fetch_assoc();
    }

    public function getAll()
    {
        $result = $this->mysqli->query("
            SELECT
                id, reservation_hash, debit_hash, room_hash, status
            FROM 
                ifa_reservation
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
