<?php


class Reservation
{
    private $id;
    private $reservationHash;
    private $debitHash;
    private $roomHash;
    private $status;

    private $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function setId($id): Reservation
    {
        $this->id = $id;
        return $this;
    }

    public function setReservationHash(string $reservationHash): Reservation
    {
        $this->reservationHash = $reservationHash;
        return $this;
    }

    public function setDebitHash(string $debitHash): Reservation
    {
        $this->debitHash = $debitHash;
        return $this;
    }

    public function setRoomHash(string $roomHash): Reservation
    {
        $this->roomHash = $roomHash;
        return $this;
    }

    public function setStatus(string $status): Reservation
    {
        $this->status = $status;
        return $this;
    }

    public function getId()
    {
        return $this->id ?? 0;
    }

    public function getReservationHash(): string
    {
        return $this->reservationHash ?? '';
    }

    public function getDebitHash(): string
    {
        return $this->debitHash ?? '';
    }

    public function getRoomHash(): string
    {
        return $this->roomHash ?? '';
    }

    public function getStatus(): string
    {
        return $this->status ?? '';
    }

    public function save()
    {
        if (!($statement = $this->mysqli->prepare(
            "INSERT INTO 
                    ifa_reservation
                        (id, reservation_hash, debit_hash, room_hash, status)
                    VALUES
                        (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                       reservation_hash = ?,
                       debit_hash = ?,
                       room_hash = ?,
                       status = ?
                        "))) {
            throw new Exception("SQL Statement error");
        }


        $reservationId = $this->getId();
        $reservationHash = $this->getReservationHash();
        $debitHash = $this->getDebitHash();
        $roomHash = $this->getRoomHash();
        $status = $this->getStatus();

        $statement->bind_param(
            'dsssdsssd',
            $reservationId,
            $reservationHash,
            $debitHash,
            $roomHash,
            $status,
            $reservationHash,
            $debitHash,
            $roomHash,
            $status
        );

        if (!$statement->execute()) {
            $statement->close();
            throw new Exception("Databases insert error");
        }

        $statement->close();

        $this->setId($this->mysqli->insert_id);
    }

    public static function createFrom(mysqli $mysqli, array $data): Reservation
    {
        $reservation = new Reservation($mysqli);

        return $reservation
            ->setId($data['id'])
            ->setReservationHash($data['reservation_hash'])
            ->setRoomHash($data['room_hash'])
            ->setDebitHash($data['debit_hash'])
            ->setStatus($data['status']);
    }


}
