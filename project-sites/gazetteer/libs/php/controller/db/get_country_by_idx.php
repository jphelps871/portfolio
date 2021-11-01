<?php

    $json_data = json_decode(file_get_contents('../../../../vendors/json/countryBorders.geo.json'));
    $json_data = $json_data->{'features'};


    if (!isset($json_data)) {

        $output;
        $output['status']['name'] = false;
        $output['status']['code'] = 404;
        $output['status']['description'] = "Cannot locate recourse";
        $output['data'] = '';

        echo json_encode($output);
        
    } else {

        $user_index = file_get_contents('php://input');
    
        echo json_encode($json_data[$user_index]);
        
    }

?>