<?php	

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Person.php");
	include_once("../Response.php");

	// Insert personnel
	try {

		Person::delete( $_POST["id"] );

	} catch (Exception $err) {

		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

		$prepareError = new Response( HTTP_INTERNAL_SERVER_ERROR, "prepare statement error", "Cannot add person to database" );
		$prepareError -> send();

		exit;
	}

	// Create final success response message
	$success = new Response( Response::HTTP_OK, "success", "personnel has been deleted from the database" );
	$success -> send();

?>