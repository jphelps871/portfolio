<?php

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Person.php");
	include_once("../Response.php");

	$data = [];

	try {

		$result = Person::getPersonByID( $_GET["id"] );

		while ($row = mysqli_fetch_assoc($result)) {

			array_push($data, $row);
	
		}

	} catch (Exception $err) {

		$error = new Response(500, "unsuccesful query", "cannot get person");
		$error -> send();

	}
   
	$success = new Response();
	$success -> setData( $data );
	$success -> send();

?>