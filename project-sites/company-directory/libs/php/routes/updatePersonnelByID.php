<?php

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Person.php");
	include_once("../Response.php");

	// Create new person
	$updatePerson = new Person($_POST["firstName"], $_POST["lastName"], $_POST["jobTitle"], $_POST["email"], $_POST["departmentID"], $_POST["personnelID"]);

	// Check validation
	$updatePerson -> validateInputs();

	/**
	 * 
	 * Check email for duplicates
	 * 
	 * 				Only check email for duplicates if email has changed, otherwise it
	 * 				will throw an error because the email matches itself in the database.
	 * 
	 */

	$result = Person::getPersonByID( $_POST["personnelID"] );
	$result = $result -> fetch_assoc();

	$previousEmail = $result["email"];
	$newEmail = $_POST["email"];

	if ( $newEmail !== $previousEmail ) $updatePerson -> validateEmailIsUnique();

	// If there are any errors display them bellow the correct form inputs
	if ( $updatePerson -> hasValidationErrors() ) {

		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');

		$validationError = new Response( Response::HTTP_BAD_REQUEST, "validation error", "there are issues with the form inputs", $updatePerson -> getValidationErrors() );
		$validationError -> send();

		exit;

	}

	// Insert personnel
	try {

		$updatePerson -> update();

	} catch (Exception $err) {

		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

		$prepareError = new Response(500, "prepare statement error", "Cannot add person to database");
		$prepareError -> send();

		exit;
	}

	// Create final success response message
	$success = new Response( Response::HTTP_OK, "success", "added person to database" );
	$success -> send();

	
?>