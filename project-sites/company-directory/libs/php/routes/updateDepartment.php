<?php	

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Department.php");
	include_once("../Response.php");

	// Create new department
	$updateDepartment = new Department( $_POST["name"], $_POST["locationID"], $_POST["departmentID"]);

	// Check validation
	$updateDepartment -> validateInputs();

	// There cannot be two departments of the same name in the same location
	$updateDepartment -> nameUniqueInLocation();

	// If there are any errors display them bellow the correct HTML form inputs
	if ( $updateDepartment -> hasValidationErrors() ) {

		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');

		$validationError = new Response( Response::HTTP_BAD_REQUEST, "validation error", "there are issues with the form inputs", $updateDepartment -> getValidationErrors() );
		$validationError -> send();

		exit;

	}

	// Insert department
	try {

		$updateDepartment -> update();

	} catch (Exception $err) {

		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

		$prepareError = new Response( HTTP_INTERNAL_SERVER_ERROR, "prepare statement error", "Cannot update department to database" );
		$prepareError -> send();

		exit;
	}

	// Create final success response message
	$success = new Response( Response::HTTP_OK, "success", "department updated" );
	$success -> send();

?>