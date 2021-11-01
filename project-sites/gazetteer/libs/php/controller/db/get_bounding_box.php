<?php

    $countries_bounding_box = json_decode(file_get_contents("../../../../vendors/json/countryBoundingBox.json"));
    $user_country_code = file_get_contents('php://input');

    if(!isset($countries_bounding_box)) {

        $output;
        $output['status']['name'] = false;
        $output['status']['code'] = 404;
        $output['status']['description'] = "Cannot locate recourse";
        $output['data'] = '';

        echo json_encode($output);
        
    } else {

        $output;
        $bounding_box;
    
        $bounding_box['lng_min'] = $countries_bounding_box->{$user_country_code}[1][0];
        $bounding_box['lng_max'] = $countries_bounding_box->{$user_country_code}[1][2];
        $bounding_box['lat_min'] = $countries_bounding_box->{$user_country_code}[1][1];
        $bounding_box['lat_max'] = $countries_bounding_box->{$user_country_code}[1][3];
    
        if (!$bounding_box['lng_min']) {
    
            $output['status']['code'] = '404';
            $output['status']['name'] = false;
            $output['status']['description'] = 'cannot find rescource';
            $output['data'] = null;
    
        } else {
    
            $output['status']['code'] = '200';
            $output['status']['name'] = true;
            $output['status']['description'] = 'success';
            $output['data'] = $bounding_box;      
    
        }
    
        echo json_encode($output);    
    }
    
?>
