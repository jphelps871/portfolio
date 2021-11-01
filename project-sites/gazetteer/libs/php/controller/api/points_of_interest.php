<?php

    include '../../config.php';

    $data = json_decode(file_get_contents('php://input'));

    $country_name = $data->{'country'};
    $iso_a2 = $data->{'iso_a2'};
    $lng_min = $data->{'coords'}->{'lng_min'};
    $lng_max = $data->{'coords'}->{'lng_max'};
    $lat_min = $data->{'coords'}->{'lat_min'};
    $lat_max = $data->{'coords'}->{'lat_max'};

    function options($url) {
        return array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        );
    }

    $attractions_auth = array(
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: travel-advisor.p.rapidapi.com",
            "x-rapidapi-key: f635a22cd8mshebb0747835c3ed6p19da8ajsn17bbcec92feb"
        ],
    );

    $webcam_auth = array(
        CURLOPT_HTTPHEADER => [
            "x-windy-key: OLGc9jHzy7p7uFtH4s62ckL0K44dbENt"
        ]
    );

    $airport_auth = array(
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: airportix.p.rapidapi.com",
            "x-rapidapi-key: f635a22cd8mshebb0747835c3ed6p19da8ajsn17bbcec92feb"
        ],
    );

    // * URL's
    $wiki_url = 'https://api.opentripmap.com/0.1/en/places/bbox?lon_min='. $lng_min .'&lon_max='. $lng_max  .'&lat_min='. $lat_min .'&lat_max='. $lat_max .'&kinds=historic,natural%2Ccultural%2Cnatural&rate=3h&format=json&limit=15&apikey=5ae2e3f221c38a28845f05b62c9ac4229a3037341d03d12cd5335217';
    $attractions_url = 'https://travel-advisor.p.rapidapi.com/attractions/list-in-boundary?tr_longitude='.$lng_min.'&tr_latitude='.$lat_min.'&bl_longitude='.$lng_max.'&bl_latitude='.$lat_max.'&min_rating=3&currency=USD&limit=5&lunit=km&lang=en_US&subcategory=62';
    $webcam_url = 'https://api.windy.com/api/webcams/v2/list/category=landscape/property=hd/orderby=popularity,desc/limit=10/bbox='. $lat_max .','. $lng_max .','. $lat_min  .','. $lng_min .'?show=webcams:image,location';
    $airports_url = 'https://airportix.p.rapidapi.com/airport/country_code/'. $iso_a2 .'/2';

    // * Activate curl
    $wiki_curl = curl_init();
    $attractions_curl = curl_init();
    $webcam_curl = curl_init();
    $airport_curl = curl_init();

    // * Create curl options array
    curl_setopt_array($wiki_curl, options($wiki_url));
    curl_setopt_array($attractions_curl, options($attractions_url) + $attractions_auth);
    curl_setopt_array($webcam_curl, options($webcam_url) + $webcam_auth);
    curl_setopt_array($airport_curl, options($airports_url) + $airport_auth);

    // * Initialize all curl's
    $mh = curl_multi_init();

    // * Add curl's to multi handle
    curl_multi_add_handle($mh, $wiki_curl);
    curl_multi_add_handle($mh, $attractions_curl);
    curl_multi_add_handle($mh, $webcam_curl);
    curl_multi_add_handle($mh, $airport_curl);

    // * Execute
    $runnung = null;
    do {

        curl_multi_exec($mh, $running);

    } while ($running);

    // * Return data
    $wiki_city_codes = json_decode(curl_multi_getcontent($wiki_curl));
    $attractions_data = json_decode(curl_multi_getcontent($attractions_curl));
    $webcams_data = json_decode(curl_multi_getcontent($webcam_curl));
    $airports_data = json_decode(curl_multi_getcontent($airport_curl));

    // * Deactivate curls
    curl_multi_close($mh);

    if ($err) {

        $output;
        $output['status']['name'] = false;
        $output['status']['code'] = $http_code;
        $output['status']['description'] = $err;
        $output['data'] = '';

        echo json_encode($output);


    } else {

        // * All data
        $city_data = [];
        $attractions = [];
        $webcams = [];
        $airports = [];

        // * Wikipedia
        foreach($wiki_city_codes as $place_info) {
            $xid = $place_info->{'xid'};

            $url = 'https://api.opentripmap.com/0.1/en/places/xid/'. $xid .'?apikey=5ae2e3f221c38a28845f05b62c9ac4229a3037341d03d12cd5335217';
            $wiki_data = json_decode(curl_data($url));

            if (strtoupper($wiki_data->{'address'}->{'country_code'}) !== $iso_a2) {
                continue;
            };

            /* Adding to php array */
            $country_data;
            $country_data['name'] = $wiki_data->{'name'};
            $country_data['img'] = $wiki_data->{'preview'}->{'source'};
            $country_data['intro'] = $wiki_data->{'wikipedia_extracts'}->{'text'};
            $country_data['location'] = array(
                'lon' => $wiki_data->{'point'}->{'lon'},
                'lat' => $wiki_data->{'point'}->{'lat'},
            );
            array_push($city_data, $country_data);
        }

        // * Attractions
        foreach($attractions_data->{'data'} as $attraction) {
            if ($attraction->{'address_obj'}->{'country'} !== $country_name) {
                continue;
            }

            $attraction_data;
            $attraction_data['name'] = $attraction->{'name'};
            $attraction_data['img'] = $attraction->{'photo'}->{'images'}->{'medium'}->{'url'};
            $attraction_data['rating'] = $attraction->{'rating'};
            $attraction_data['link'] = $attraction->{'web_url'};
            $attraction_data['description'] = $attraction->{'description'};
            $attraction_data['location'] = array(
                'lon' => $attraction->{'longitude'},
                'lat' => $attraction->{'latitude'},
            );
            array_push($attractions, $attraction_data);
        }

        // * Webcams
        foreach($webcams_data->{'result'}->{'webcams'} as $webcam) {
            if ($webcam->{'location'}->{'country_code'} !== $iso_a2) {
                continue;
            }

            $webcam_data;
            $webcam_data['name'] = $webcam->{'title'};
            $webcam_data['img'] = $webcam->{'image'}->{'current'}->{'preview'};
            $webcam_data['city'] = $webcam->{'location'}->{'city'};
            $webcam_data['location'] = array(
                'lon' => $webcam->{'location'}->{'longitude'},
                'lat' => $webcam->{'location'}->{'latitude'},
            );
            array_push($webcams, $webcam_data);
        }

        // * Airports
        foreach($airports_data->{'data'} as $airport) {
            $airport_data;
            $airport_data['name'] = $airport->{'name'}->{'original'};
            $airport_data['location'] = array(
                    'lon' => $airport->{'location'}->{'longitude'},
                    'lat' => $airport->{'location'}->{'latitude'},
            );

            array_push($airports, $airport_data);
        }

        // * Return all data
        $all_data = array(
            'attractions' => $attractions,
            'wikipedia' => $city_data,
            'webcam' => $webcams,
            'airports' => $airports
        );

        echo json_encode($all_data);
    }

?>