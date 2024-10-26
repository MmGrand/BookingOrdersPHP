<?php

include 'db.php';

// Функция для генерации barcode
function generateBarcode() {
	return rand(10000000, 99999999);
}

// Функция для реализации бронирования заказа
function bookOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity) {
	// Подключаемся к бд и получаем barcode
	$db = getConnection();
	$barcode = generateBarcode();

	// Реализуем бронирование, избегая случаев, чтобы было 2 заказа с одинаковым barcode
	while (true) {
			$apiBookResponse = apiBook($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode);

			if (isset($apiBookResponse['error']) && $apiBookResponse['error'] == 'barcode already exists') {
					$barcode = generateBarcode();
					continue;
			}

			if (isset($apiBookResponse['message']) && $apiBookResponse['message'] == 'order successfully booked') {
					$apiApproveResponse = apiApprove($barcode);
					if (isset($apiApproveResponse['message']) && $apiApproveResponse['message'] == 'order successfully approved') {
							$equal_price = ($ticket_adult_price * $ticket_adult_quantity) + ($ticket_kid_price * $ticket_kid_quantity);

							try {
									$query = $db->prepare("INSERT INTO orders (event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode, equal_price, created)
																				 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
									$query->execute([$event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode, $equal_price]);

									return 'Order successfully saved in the database';
							} catch (\PDOException $e) {
									if ($e->getCode() == '23000') {
											$barcode = generateBarcode();
											continue;
									} else {
											throw $e;
									}
							}
					} else {
							return $apiApproveResponse['error'];
					}
			} else {
					return 'Booking failed: ' . $apiBookResponse['error'];
			}
	}
}

// Функция для ответа на бронь заказа
function apiBook($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode) {
	$responses = [
		['message' => 'order successfully booked'],
		['error' => 'barcode already exists']
	];

	return $responses[array_rand($responses)];
}

// Функция для ответа на подтверждения брони заказа
function apiApprove($barcode) {
	$responses = [
			['message' => 'order successfully approved'],
			['error' => 'event cancelled'],
			['error' => 'no tickets'],
			['error' => 'no seats'],
			['error' => 'fan removed']
	];
	return $responses[array_rand($responses)];
}

?>