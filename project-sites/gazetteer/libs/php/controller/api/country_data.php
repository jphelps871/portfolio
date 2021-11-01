<?php 

    include '../../config.php';

    // Set data for weather 
    date_default_timezone_set('UTC');

    function day($add_day) {
        $date_stamp = mktime(0, 0, 0, date("m")  , date("d") + $add_day, date("Y"));
        return gmdate('l', $date_stamp);
    }

    function options($url) {
        return array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_FAILONERROR => true,
        );
    }

    /* Authorization */
    $cities_auth = array(
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: referential.p.rapidapi.com",
            "x-rapidapi-key: f635a22cd8mshebb0747835c3ed6p19da8ajsn17bbcec92feb"
        ]
    );

    $weather_auth = array(
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: weatherapi-com.p.rapidapi.com",
            "x-rapidapi-key: f635a22cd8mshebb0747835c3ed6p19da8ajsn17bbcec92feb"
        ],
    );

    $news_auth = array(
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: newscatcher.p.rapidapi.com",
            "x-rapidapi-key: f635a22cd8mshebb0747835c3ed6p19da8ajsn17bbcec92feb"      
        ],
    );

    $country = json_decode(file_get_contents('php://input'));

    // * URL's
    $rest_url = 'https://restcountries.com/v2/alpha/' . $country->{'iso_a2'};
    $wiki_url = 'http://api.geonames.org/wikipediaSearchJSON?q='. urlencode($country->{'name'} .' '. $country->{'iso_a2'}) .'&maxRows=1&username=07jphel56';
    $exchange_url = 'https://openexchangerates.org/api/latest.json?app_id=be065cb6b48546efadf69d433dfb1069';
    $cities_url = 'https://referential.p.rapidapi.com/v1/city?iso_a2='. $country->{'iso_a2'} .'&lang=en&sort=population&order=desc&limit=10';
    $economy_url = 'http://api.worldbank.org/v2/country/'. $country->{'iso_a2'} .'/indicator/NY.GDP.MKTP.CD;NY.GDP.MKTP.KD.ZG;NE.IMP.GNFS.ZS;NE.EXP.GNFS.ZS;FI.RES.TOTL.CD;SL.UEM.TOTL.ZS;SL.TLF.TOTL.IN;FP.CPI.TOTL.ZG;NV.AGR.TOTL.ZS;NV.IND.TOTL.ZS;CM.MKT.TRAD.GD.ZS?date=2020&format=json&source=2';
    $news_url = 'https://newscatcher.p.rapidapi.com/v1/latest_headlines?topic=news&lang=en&country='. $country->{'iso_a2'} .'&media=True';

    // * Activate curl
    $curl_rest_countries = curl_init();
    $curl_wiki = curl_init();
    $curl_exchange = curl_init();
    $curl_cities = curl_init();
    $curl_economy = curl_init();
    $curl_news = curl_init();

    // * Create curl options array
    curl_setopt_array($curl_rest_countries, [
        CURLOPT_URL => $rest_url,
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false  
    ]);
    curl_setopt_array($curl_wiki, options( $wiki_url ));
    curl_setopt_array($curl_exchange, options( $exchange_url ));
    curl_setopt_array($curl_cities , options( $cities_url) + $cities_auth); 
    curl_setopt_array($curl_economy, options( $economy_url ));
    curl_setopt_array($curl_news, options( $news_url ) + $news_auth );

    // * Initialize all curl's
    $mh = curl_multi_init();

    // * Add curl's to multi handle
    curl_multi_add_handle($mh, $curl_rest_countries);
    curl_multi_add_handle($mh, $curl_wiki);
    curl_multi_add_handle($mh, $curl_exchange);
    curl_multi_add_handle($mh, $curl_cities);
    curl_multi_add_handle($mh, $curl_economy);
    curl_multi_add_handle($mh, $curl_news);

    // * Execute
    $running = null;
    do {

      curl_multi_exec($mh, $running);

    } while ($running);

    // * Return data
    $rest_countries = json_decode( curl_multi_getcontent($curl_rest_countries) );
    $wiki = json_decode( curl_multi_getcontent($curl_wiki) );
    $exchange_rates = json_decode( curl_multi_getcontent($curl_exchange) );
    $cities = json_decode(curl_multi_getcontent($curl_cities));
    $economical = json_decode( curl_multi_getContent($curl_economy) );
    $news = json_decode( curl_multi_getcontent($curl_news) );

    // * Error handling
    $err = curl_error($rest_countries);
    $http_code = curl_getinfo($rest_countries, CURLINFO_HTTP_CODE);

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
        $country_data;

        // * General Info
        $country_data['general'] = [
            'Country' => $country->{'name'},
            'Capital' => $rest_countries->{'capital'},
            'Language' => $rest_countries->{'languages'}[0]->{'name'},
            'Region' => $rest_countries->{'subregion'},
            'Flag' => $rest_countries->{'flag'},
            'Population' => number_format($rest_countries->{'population'}),
            'Currency' => $rest_countries->{'currencies'}[0]->{'name'},
            'Borders' => $rest_countries->{'borders'},
            'Information' => $wiki->{'geonames'}[0]->{'summary'},
            'Exchange_rate' => $rest_countries->{'currencies'}[0]->{'symbol'} . $exchange_rates->{'rates'}->{$rest_countries->{'currencies'}[0]->{'code'}},
        ];

        // * Economic data
        $country_data['economic_data'] = [
            'GDP' => number_format($economical[1][0]->{'value'}),
            'Growth' => $economical[1][1]->{'value'},
            'Imports' => $economical[1][2]->{'value'},
            'Exports' => $economical[1][3]->{'value'},
            'Gold_reserves' => number_format($economical[1][4]->{'value'}),
            'Unemployment' => $economical[1][5]->{'value'},
            'Workforce' => number_format($economical[1][6]->{'value'}),
            'Inflation' => $economical[1][7]->{'value'},
            'Agriculture' => $economical[1][8]->{'value'},
            'Industry' => $economical[1][9]->{'value'},
            'Stocks' => $economical[1][10]->{'value'},
        ];

        // * News
        $country_data['news'] = [
            'Title' => $news->{'articles'}[0]->{'title'},
            'Summary' => $news->{'articles'}[0]->{'summary'},
            'Link' => $news->{'articles'}[0]->{'link'},
        ];

        // * Weather_forcast
        $curl_weather_forcast = curl_init();

        $weather_forcast_url = 'https://api.openweathermap.org/data/2.5/onecall?lat=' . $rest_countries->{'latlng'}[0] . '&lon=' . $rest_countries->{'latlng'}[1] . '&units=metric&exclude=current,minutely,hourly&appid=7d81279cea35807dc0a48b3616025a81';
        curl_setopt_array($curl_weather_forcast, options( $weather_forcast_url ));

        $weather_forcast = json_decode(curl_exec($curl_weather_forcast));

        curl_close($curl_weather_forcast);

        $country_data['weather_forcast'];

        for ($i = 0; $i <= 5; $i++) {

            $country_data['weather_forcast'][$i] = [
                'Week_day' => day($i),
                'weather_icon' => $weather_forcast->{'daily'}[$i]->{'weather'}[0]->{'main'},
                'Description' => $weather_forcast->{'daily'}[$i]->{'weather'}[0]->{'description'},
                'Temp' => $weather_forcast->{'daily'}[$i]->{'temp'}->{'day'},
                'Wind' => $weather_forcast->{'daily'}[$i]->{'wind_speed'}
            ];

        }

        // * Weather for cities
        $country_data['cities'] = [];

        foreach ($cities as $city) {
            $city_data;
            $city_data['name'] = $city->{'value'};


            /* Get cities weather data */
            $curl = curl_init();

            $weather_url = 'https://weatherapi-com.p.rapidapi.com/current.json?q='. urlencode($city_data['name']) . ',' . $country->{'iso_a2'};
            curl_setopt_array($curl, options($weather_url) + $weather_auth);
        
            $current_weather = json_decode(curl_exec($curl));

            curl_close($current_weather);
        
            $city_data['weather'];
            $city_data['weather']['temp'] = $current_weather->{'current'}->{'temp_c'};
            $city_data['weather']['feels_like'] = $current_weather->{'current'}->{'feelslike_c'};
            $city_data['weather']['wind_speed'] = $current_weather->{'current'}->{'wind_mph'};
            $city_data['weather']['text'] = $current_weather->{'current'}->{'condition'}->{'text'};
            $city_data['location'];
            $city_data['location']['lat'] = $current_weather->{'location'}->{'lat'};
            $city_data['location']['lon'] = $current_weather->{'location'}->{'lon'};
        
            array_push($country_data['cities'], $city_data);

        }


        // * Filter Data
        foreach($country_data['general'] as $key => &$value) {

            $flag_data = array_search('Flag', $country_data['general']);

            if (!$value) {
                if ($key === 'Flag') {
                    $value = './img/earth/earth.svg';
                } else {
                    $value = 'N/A';
                }
            }
        }

        foreach($country_data['economic_data'] as &$value) {
            if (!$value) $value = 'N/A';

            if (!is_string($value)) {
                $value = number_format($value, 3, '.', '') . '%';
            }
        }

        foreach($country_data['news'] as $key => &$value) {
            if (!$value) {
                if ($key === 'Title') {
                    $value = "Sorry, we cannot find any news from " . $country->{'name'};
                } else {
                    $value = '';
                }
            }
        }

        echo json_encode($country_data);
    }

?>