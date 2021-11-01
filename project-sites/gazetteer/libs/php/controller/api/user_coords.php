<?php

    include '../../config.php';

    $coords = json_decode(file_get_contents('php://input'));

    $json_data = curl_data( 'https://api.opencagedata.com/geocode/v1/json?q=' . $coords->{'lat'} . '+' . $coords->{'lon'} . '&key=b551052cc90d45839f63d3209f2d6f4b');

    if ($json_data['status'] === '404') {

        echo json_encode($json_data);

    } else {

        // close curl 
        curl_close($ch);

        // edit data in php
        $output_php = json_decode($json_data);

        $country_data;
        $country_data['iso_a3'] = $output_php->{'results'}[0]->{'components'}->{'ISO_3166-1_alpha-3'};
        $country_data['iso_a2'] = $output_php->{'results'}[0]->{'components'}->{'ISO_3166-1_alpha-2'};

        // return data 
        echo json_encode($country_data);

    }

?>