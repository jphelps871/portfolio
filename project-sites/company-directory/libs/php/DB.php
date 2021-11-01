<?php

    include_once("Response.php");
    
    class DB {

        public static function connect() {

            $connection;

            // Check is user is in localhost, if so set database to local
            if(!in_array( $_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'] )){

                $connection = new mysqli("localhost", "jamesjphelps", "Milkyway100", "companydirectory");
                
            } else {

                $connection = new mysqli("127.0.0.1", "root", "companydirectorypassword", "companydirectory", 3306, "");
            }


            if ( $connection -> connect_error ) {

                $connectionError = new Response(500, $connection -> connect_error);

                echo json_encode( $connectionError );

            } else {

                return $connection;

            }

        }

    }

?>