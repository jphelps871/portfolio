<?php 
    function error($msg) {
        $err = array('status' => '404', 'message' => $msg);
        return $err;
    }

    function curl_data($url) {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $output_json = curl_exec($ch);

        if (curl_errno($ch)) {

            // close curl 
            curl_close($ch);

            $error = error('No data available');
            
            return $error;

        } else {

            return $output_json;

        }

    }

?>