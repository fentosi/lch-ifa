<?php

class ReservationStatuses {
    const CANCELLED = 'Lemondva';
    const CLAIMED = 'Igeny';
    const OPTIONAL = 'Opcios';
    const CONFIRMED = 'Visszaigazolt';
    const NO_SHOW = 'NoShow';
    const ARRIVED = 'Erkezett';
    const DEPARTED = 'Tavozott';

    const STATUS_CODES = [
        'Lemondva' => -1,
        'Igeny' => 0,
        'Opcios' => 10,
        'Visszaigazolt' => 50,
        'NoShow' => 75,
        'Erkezett' => 88,
        'Tavozott' => 95,
    ];
}
