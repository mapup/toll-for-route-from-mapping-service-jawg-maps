<?php
//using jawmaps API

//Source and Destination Coordinates..
// Dallas, TX - coordinates
$source_longitude='-96.79448';
$source_latitude='32.78165';
//Addison, TX - coordinates
$destination_longitude='-96.818';
$destination_latitude='32.95399';
$key = 'jawmaps_api_key';

$url = 'https://api.jawg.io/routing/route/v1/car/'.$source_longitude.','.$source_latitude.';'.$destination_longitude.','.$destination_latitude.'?overview=full&access-token='.$key.'';

//connection..
$jawmaps = curl_init();

curl_setopt($jawmaps, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($jawmaps, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($jawmaps, CURLOPT_URL, $url);
curl_setopt($jawmaps, CURLOPT_RETURNTRANSFER, true);

//getting response from jawmapsapi..
$response = curl_exec($jawmaps);
$err = curl_error($jawmaps);

curl_close($jawmaps);

if ($err) {
	  echo "cURL Error #:" . $err;
} else {
	  echo "200 : OK\n";
}

//extracting polyline from the JSON response..
$data_jawmaps = json_decode($response, true);
$data_new = $data_jawmaps['routes'];
$new_data = $data_new['0'];
$pol_data = $new_data['geometry'];

//polyline..
$polyline_jawmaps = $pol_data;


//using tollguru API..
$curl = curl_init();

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);


$postdata = array(
	"source" => "gmaps",
	"polyline" => $polyline_jawmaps
);

//json encoding source and polyline to send as postfields..
$encode_postData = json_encode($postdata);

curl_setopt_array($curl, array(
CURLOPT_URL => "https://dev.tollguru.com/v1/calc/route",
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 30,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "POST",


//sending jawmaps polyline to tollguru
CURLOPT_POSTFIELDS => $encode_postData,
CURLOPT_HTTPHEADER => array(
				      "content-type: application/json",
				      "x-api-key: tollguru_api_key"),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	  echo "cURL Error #:" . $err;
} else {
	  echo "200 : OK\n";
}

//response from tollguru..
var_dump(json_decode($response, true));
// $data = var_dump(json_decode($response, true));
//print_r($data);
?>