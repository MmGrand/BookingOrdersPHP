<?php

include '../app/functions.php';

// Пример данных для функции
$event_id = 5;
$event_date = '2024-25-10';
$ticket_adult_price = 1000;
$ticket_adult_quantity = 2;
$ticket_kid_price = 500;
$ticket_kid_quantity = 1;

$result = bookOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity);

echo $result;

?>