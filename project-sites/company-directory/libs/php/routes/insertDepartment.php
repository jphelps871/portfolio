<?php	

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Department.php");
	include_once("../Response.php");

	// Create new department
	$insertDepartment = new Department( $_POST["name"], $_POST["locationID"] );

	// Check validation
	$insertDepartment -> validateInputs();

	// Cannot be duplicate department names in database
	$insertDepartment -> nameUniqueInLocation();

	// If there are any errors display them bellow the correct HTML form inputs
	if ( $insertDepartment -> hasValidationErrors() ) {

		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');

		$validationError = new Response( Response::HTTP_BAD_REQUEST, "validation error", "there are issues with the form inputs", $insertDepartment -> getValidationErrors() );
		$validationError -> send();

		exit;

	}

	// Insert department
	try {

		$insertDepartment -> insert();

	} catch (Exception $err) {

		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

		$prepareError = new Response( HTTP_INTERNAL_SERVER_ERROR, "prepare statement error", "Cannot add department to database" );
		$prepareError -> send();

		exit;
	}

	// Create final success response message
	$success = new Response( Response::HTTP_OK, "success", "added department to database" );
	$success -> send();

?>