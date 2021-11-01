<?php

    header('Content-Type: application/json; charset=UTF-8');

    include_once("../Location.php");
    include_once("../Response.php");

	// Create new location
    $updateLocation = new Location( $_POST['name'], $_POST['locationID'] );

	// Check validation
    $updateLocation -> validateInputs();

    // Cannot be duplicate location names in database
    $updateLocation -> validateNameIsUnique();

    // If there are any errors display them bellow the correct HTML form inputs
	if ( $updateLocation -> hasValidationErrors() ) {

		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');

		$validationError = new Response( Response::HTTP_BAD_REQUEST, "validation error", "there are issues with the form inputs", $updateLocation -> getValidationErrors() );
		$validationError -> send();

		exit;

    }
    
    // Insert location
	try {

		$updateLocation -> update();

	} catch (Exception $err) {

		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

		$prepareError = new Response( HTTP_INTERNAL_SERVER_ERROR, "prepare statement error", "Cannot update location to database" );
		$prepareError -> send();

		exit;
    }
    
    // Create final success response message
	$success = new Response( Response::HTTP_OK, "success", "updated location to database" );
	$success -> send();

?>