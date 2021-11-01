<?php

    header('Content-Type: application/json; charset=UTF-8');

    include_once("../Location.php");
    include_once("../Response.php");

	// Create new location
    $newLocation = new Location( $_POST['name'] );

	// Check validation
    $newLocation -> validateInputs();

    // Cannot be duplicate location names in database
    $newLocation -> validateNameIsUnique();

    // If there are any errors display them bellow the correct HTML form inputs
	if ( $newLocation -> hasValidationErrors() ) {

		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');

		$validationError = new Response( Response::HTTP_BAD_REQUEST, "validation error", "there are issues with the form inputs", $newLocation -> getValidationErrors() );
		$validationError -> send();

		exit;

    }
    
    // Insert location
	try {

		$newLocation -> insert();

	} catch (Exception $err) {

		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

		$prepareError = new Response( HTTP_INTERNAL_SERVER_ERROR, "prepare statement error", "Cannot add location to database" );
		$prepareError -> send();

		exit;
    }
    
    // Create final success response message
	$success = new Response( Response::HTTP_OK, "success", "added location to database" );
	$success -> send();

?>