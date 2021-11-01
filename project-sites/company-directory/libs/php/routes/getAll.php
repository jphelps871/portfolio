<?php

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Person.php");
	include_once("../Response.php");

	try {

		$result = Person::getAll();

		$data = [];

		while ($row = mysqli_fetch_assoc($result)) {
	
			array_push($data, $row);
	
		}

	} catch (Exception $err) {

		$error = new Response(500, "Database error", $err -> getMessage());
		$error -> send();

		exit;

	}

	$success = new Response();
	$success -> setData( $data );
	$success -> send();

?>