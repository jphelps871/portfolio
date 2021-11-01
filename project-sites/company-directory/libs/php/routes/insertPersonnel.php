<?php	

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Person.php");
	include_once("../Response.php");

	// Create new person
	$insertPerson = new Person($_POST["firstName"], $_POST["lastName"], $_POST["jobTitle"], $_POST["email"], $_POST["departmentID"]);

	// Check validation
	$insertPerson -> validateInputs();

	// Cannot be duplicate email's in database
	$insertPerson -> validateEmailIsUnique();

	// If there are any errors display them bellow the correct form inputs
	if ( $insertPerson -> hasValidationErrors() ) {

		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');

		$validationError = new Response( Response::HTTP_BAD_REQUEST, "validation error", "there are issues with the form inputs", $insertPerson -> getValidationErrors() );
		$validationError -> send();

		exit;

	}

	// Insert personnel
	try {

		$insertPerson -> insert();

	} catch (Exception $err) {

		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

		$prepareError = new Response( HTTP_INTERNAL_SERVER_ERROR, "prepare statement error", "Cannot add person to database" );
		$prepareError -> send();

		exit;
	}

	// Create final success response message
	$success = new Response( Response::HTTP_OK, "success", "added person to database" );
	$success -> send();

?>