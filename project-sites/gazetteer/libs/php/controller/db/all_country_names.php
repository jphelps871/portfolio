<?php 

    // GET countries from json
    $json_data = json_decode(file_get_contents('../../../../vendors/json/countryBorders.geo.json'));
    if (!isset($json_data)) {

        $output;
        $output['status']['name'] = false;
        $output['status']['code'] = 404;
        $output['status']['description'] = "Cannot locate recourse";
        $output['data'] = '';

        echo json_encode($output);

    } else {

        $countries_data = $json_data->{'features'};

        // all country names 
        $country_names = [];
    
        foreach ($countries_data as $country_data) {
            $country;
            $country['name'] = $country_data->{'properties'}->{'name'};
            $country['iso_a2'] = $country_data->{'properties'}->{'iso_a2'};
            $country['iso_a3'] = $country_data->{'properties'}->{'iso_a3'};
    
            array_push($country_names, $country);
        }
    
        usort($country_names, function($item1, $item2){
            return $item1['name'] <=> $item2['name'];
        });
    
        echo json_encode($country_names);

    }

?>