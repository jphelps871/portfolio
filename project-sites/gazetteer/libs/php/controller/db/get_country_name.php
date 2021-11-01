<?php

    $json_data = json_decode(file_get_contents('../../../../vendors/json/countryBorders.geo.json'));
    $json_data = $json_data->{'features'};

    $user_country_input = json_decode(file_get_contents('php://input'));

    if(!isset($json_data)) {

        $output;
        $output['status']['name'] = false;
        $output['status']['code'] = 404;
        $output['status']['description'] = "Cannot locate recourse";
        $output['data'] = '';

        echo json_encode($output);

    } else {
     
        $counter = 0;
        foreach ($json_data as $country_data) {
            if ($user_country_input->{'iso_a3'} === $country_data->{'properties'}->{'iso_a3'}) {
                echo json_encode($country_data);
                break;
            }
            $counter ++;
        }    
    
        // 175 number of countries in db
        if ( $counter == 175 ) {
            $err = array('status' => '404', 'message' => 'cannot find country in db');
            echo json_encode($err);
        } 

    }

?>