<?php	

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Department.php");
	include_once("../Response.php");

	// Delete personnel
	try {

		Department::delete( $_POST["id"], $_POST["deleteWithDependecy"] );

	} catch (Exception $err) {

        // Send validation error if code is 400
        if ( $err -> getCode() === 400 ) {

            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');

            $validationError = new Response( Response::HTTP_BAD_REQUEST, "validation error", $err -> getMessage());
            $validationError -> send();

        // Send server error if query or database issue
        } else {

            header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

            $prepareError = new Response( Response::HTTP_INTERNAL_SERVER_ERROR, "prepare statement error", "Cannot remove department from database" );
            $prepareError -> send();

        }

		exit;
	}

	// Create final success response message
	$success = new Response( Response::HTTP_OK, "success", "department has been deleted from the database" );
	$success -> send();

?>