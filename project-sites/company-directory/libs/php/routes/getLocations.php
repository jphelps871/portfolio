<?php

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Location.php");
	include_once("../Response.php");

	try {

		$result = Location::getAll();

		$data = [];

		while ($row = mysqli_fetch_assoc($result)) {

			array_push($data, $row);
	
		}

	} catch (Exception $err) {

		$error = new Response(500, "unsuccessful query", "cannot get all departments");
		$error -> send();

		exit;

	}
   
	$success = new Response();
	$success -> setData( $data );
	$success -> send();

?>