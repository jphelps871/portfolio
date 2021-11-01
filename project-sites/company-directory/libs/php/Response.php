<?php

    class Response {

        private $code;
        private $name;
        private $description;
        private $data;

        public const HTTP_OK = 200;
        public const HTTP_BAD_REQUEST = 400;
        public const HTTP_UNAUTHORIZED = 401;
        public const HTTP_FORBIDDEN = 403;
        public const HTTP_REQUEST_TIMEOUT = 408;
        public const HTTP_CONFLICT = 409;
        public const HTTP_INTERNAL_SERVER_ERROR = 500;                         

        function __construct( $code = 200, $name = "Success", $description = "", $data = [] ) {

            $this -> setCode( $code );
            $this -> setName( $name );
            $this -> setDescription( $description );
            $this -> setData( $data );

        }

        public function setData( $data ) {
            
            if ( !is_array( $data ) ) {

                throw new Exception( "Data must be an array." );

            }

            $this -> data = $data;

        }

        private function setCode( $code ) {

            if ( !is_int($code) ) {

                throw new Exception( $code . " Is not a valid code" );

            }

            $this -> code = $code;

        }

        private function setName( $name ) {

            if ( !is_string( $name ) ) {

                throw new Exception( $name . " Must be a string" );

            }

            $this -> name = $name;

        }

        private function setDescription( $desc ) {

            $this -> description = $desc;

        }

        public function send() {

            $output;
            $output['status']['code'] = $this->code;
            $output['status']['name'] = $this->name;
            $output['status']['description'] = $this->description;	
            $output['data'] = $this->data;

            echo json_encode($output);

        }

    }

?>