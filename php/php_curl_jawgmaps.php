<?php

// JawMaps API
$JAWG_API_KEY = getenv('JAWG_API_KEY');
$JAWG_API_URL = "https://api.jawg.io/routing/route/v1/car";

// TollGuru API
$TOLLGURU_API_KEY = getenv('TOLLGURU_API_KEY');
$TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2";
$POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service";

// Philadelphia, PA
$source_longitude = '-75.1652';
$source_latitude = '39.9526';

// New York, NY
$destination_longitude = '-74.0060';
$destination_latitude = '40.7128';

// Explore https://tollguru.com/toll-api-docs to get the best of all the parameters that Tollguru has to offer
$request_parameters = array(
  "vehicle" => array(
      "type" => "2AxlesAuto"
  ),
  // Visit https://en.wikipedia.org/wiki/Unix_time to know the time format
  "departure_time" => "2021-01-05T09:46:08Z"
);

$url = $JAWG_API_URL . '/' . $source_longitude . ',' . $source_latitude . ';' . $destination_longitude . ',' . $destination_latitude . '?overview=full&access-token=' . $JAWG_API_KEY;

// Initialize cURL for JawMaps API request
$jawmaps = curl_init();

// Set cURL options for JawMaps API request
curl_setopt($jawmaps, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($jawmaps, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($jawmaps, CURLOPT_URL, $url);
curl_setopt($jawmaps, CURLOPT_RETURNTRANSFER, true);

// Execute JawMaps API request
$response = curl_exec($jawmaps);
$err = curl_error($jawmaps);

// Close cURL connection for JawMaps API request
curl_close($jawmaps);

// Check for cURL errors for JawMaps API request
if ($err) {
    echo "cURL Error (JawMaps) #: " . $err;
} else {
    echo "JawMaps API Request Successful\n";
}

// Extract polyline from the JSON response
$data_jawmaps = json_decode($response, true);
echo $data_jawmaps;
$polyline_jawmaps = $data_jawmaps['routes']['0']['geometry'];

$postdata = array(
  "source" => "jawgmaps",
  "polyline" => $polyline_jawmaps
);
$postdata = array_merge($postdata, $request_parameters);

// Initialize cURL for TollGuru API request
$curl = curl_init();

// Set cURL options for TollGuru API request
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt_array($curl, array(
    CURLOPT_URL => $TOLLGURU_API_URL . "/" . $POLYLINE_ENDPOINT,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($postdata),
    CURLOPT_HTTPHEADER => array(
        "content-type: application/json",
        "x-api-key: " . $TOLLGURU_API_KEY
    ),
));

// Execute TollGuru API request
$response = curl_exec($curl);
$err = curl_error($curl);

// Close cURL connection for TollGuru API request
curl_close($curl);

// Check for cURL errors for TollGuru API request
if ($err) {
    echo "cURL Error (TollGuru) #: " . $err;
} else {
    echo "TollGuru API Request Successful\n";
}

// Display response from TollGuru
$data = var_dump(json_decode($response, true));
print_r($data);
?>
