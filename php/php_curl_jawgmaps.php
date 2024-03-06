<?php
//using jawmaps API

$JAWG_API_KEY = getenv('JAWG_API_KEY');
$JAWG_API_URL = "https://api.jawg.io/routing/route/v1/car";

$TOLLGURU_API_KEY = getenv('TOLLGURU_API_KEY');
$TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2";
$POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service";

// Dallas, TX - coordinates
$source_longitude='-96.79448';
$source_latitude='32.78165';

// New York, NY - coordinates
$destination_longitude='-74.0060';
$destination_latitude='40.7128';

$url = $JAWG_API_URL.'/'.$source_longitude.','.$source_latitude.';'.$destination_longitude.','.$destination_latitude.'?overview=full&access-token='.$JAWG_API_KEY.'';

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

//polyline..
$polyline_jawmaps = $data_jawmaps['routes']['0']['geometry'];


//using tollguru API..
$curl = curl_init();

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);


$postdata = array(
	"source" => "jawgmaps",
	"polyline" => $polyline_jawmaps
);

//json encoding source and polyline to send as postfields..
$encode_postData = json_encode($postdata);

curl_setopt_array($curl, array(
  CURLOPT_URL => $TOLLGURU_API_URL . "/" . $POLYLINE_ENDPOINT,
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
    "x-api-key: " . $TOLLGURU_API_KEY),
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
$data = var_dump(json_decode($response, true));
print_r($data);
?>
