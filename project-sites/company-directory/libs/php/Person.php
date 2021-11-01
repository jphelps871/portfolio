<?php

    include_once("Validation.php");
    include_once("DB.php");

    class Person {

        // User input values
        private $firstName;
        private $lastName;
        private $email;
        private $jobTitle;
        private $departmentID;
        private $personnelID;

        // validation data
        private $validation;

        /**
         *
         * Create a person object
         * 
         *              The constructor function creates a validation object which is used
         *              on each user input. If personnelID exists, then add that to the
         *              correct property
         * 
         */

        function __construct( $firstName = "", $lastName = "", $jobTitle = "", $email = "", $departmentID = 0, $personnelID = null ) {

            $this -> validation = new Validation();

            $this -> setFirstName( $firstName );
            $this -> setLastName( $lastName );
            $this -> setJobtitle( $jobTitle );
            $this -> setEmail( $email );
            $this -> setDepartment( $departmentID );

            // added to property only when updating personnel
            if ( $personnelID !== null ) $this -> personnelID = $personnelID;

        }

        /**
         * 
         * setFirstName, setLastName, setEmail, setJobtitle, setDepartment
         * 
         *              All methods executed on constructor function
         * 
         */

        private function setFirstName( $value ) {

            $this -> firstName = $value;

        }

        private function setLastName( $value ) {

            $this -> lastName = $value;

        }

        private function setEmail( $value ) {

            $this -> email = $value;

        }

        private function setJobtitle( $value ) {

            $this -> jobTitle = $value;

        }

        private function setDepartment( $value ) {

            $this -> departmentID = $value;

        }

        /**
         * 
         * Check validation
         * 
         *              Check for validation on inputs, and return 
         *              results if necesary. 
         * 
         *              Also includes ability to check for duplicates,
         *              such as to prevernt copies of emails
         * 
         */

        public function validateInputs() {

            $this -> validation -> name("firstName") -> value($this -> firstName) -> required();
            $this -> validation -> name("lastName") -> value($this -> lastName) -> required();
            $this -> validation -> name("jobTitle") -> value($this -> jobTitle);
            $this -> validation -> name("email") -> value($this -> email) -> is_email() -> required();
            $this -> validation -> name("departmentID") -> value($this -> departmentID) -> required();

        }

        public function validateEmailIsUnique() {

            $result = $this -> getEmail();

            if ( mysqli_num_rows( $result ) > 0 ) {

                $this -> validation -> errors['email'] = "This email already exists";

            }


        }

        public function hasValidationErrors() {

            return $this -> validation -> is_success() ? false : true;

        }

        public function getValidationErrors() {

            return $this -> validation -> get_errors();

        }

        /**
         * 
         * Create a prepared statement
         * 
         *              Both creates and deletes all connections
         *              and executes the data, no need to do seperatly
         * 
         */

        public function insert() {

            $conn = DB::connect();

            $stmt = $conn -> prepare("INSERT INTO personnel (firstName, lastName, jobTitle, email, departmentID) VALUES(?, ?, ?, ?, ?)");
            $stmt -> bind_param("ssssi", $this -> firstName, $this -> lastName, $this -> jobTitle, $this -> email, $this -> departmentID);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public function update() {

            $conn = DB::connect();

            $stmt = $conn -> prepare("UPDATE personnel SET firstName = ?, lastName = ?, jobTitle = ?, email = ?, departmentID = ? WHERE id = ?");
            $stmt -> bind_param("ssssii", $this -> firstName, $this -> lastName, $this -> jobTitle, $this -> email, $this -> departmentID, $this -> personnelID);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public static function delete( $personnelID = null ) {

            if ( $personnelID === null ) throw new Exception( "Must include personnel id as agrument" );

            $conn = DB::connect();

            $stmt = $conn -> prepare("DELETE FROM personnel WHERE id = ?");
            $stmt -> bind_param("i", $personnelID);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public static function search( $input = null ) {

            if ( empty($input) ) throw new Exception("Input is empty", 400);

            $input = "%" . $input . "%";

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT id, CONCAT(firstName, \" \", lastName) AS name FROM personnel WHERE CONCAT(firstName, \" \", lastName) LIKE ? LIMIT 5");
            $stmt -> bind_param("s", $input);
            $stmt -> execute();

            // Return data before closing connection
            $result = $stmt -> get_result();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;
        }

        /**
         * 
         * Get data
         * 
         * 
         */

        public function getEmail() {

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT email FROM personnel WHERE email = ?");
            $stmt -> bind_param("s", $this -> email);
            $stmt -> execute();

            // Return data before closing connection
            $result = $stmt -> get_result();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;

        }

        public static function getPersonByID( $personnelID ) {

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT id, firstName, lastName, jobTitle, email, departmentID FROM personnel WHERE id = ?");
            $stmt -> bind_param("i", $personnelID);
            $stmt -> execute();

            // Return data before closing connection
            $result = $stmt -> get_result();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;

        }

        public static function getAll() {

            $conn = DB::connect();

            $query = 'SELECT p.id, p.lastName, p.firstName, p.jobTitle, p.email, d.name as department, l.name as location FROM personnel p LEFT JOIN department d ON (d.id = p.departmentID) LEFT JOIN location l ON (l.id = d.locationID) ORDER BY p.lastName, p.firstName, d.name, l.name';

            $result = $conn->query($query);

            if (!$result) throw new Exception("issue with the query, cannot get all information");

            $conn -> close();

            return $result;

        }

    }

?>