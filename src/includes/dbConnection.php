<?php

    $mysqli = new mysqli(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        $_ENV['DB_DATABASE'],
    );

    if ($mysqli->connect_errno) {
        die("Couldn't connect to database");
    }
