<?php
//using jawmaps API

$JAWG_API_KEY = getenv('JAWG_API_KEY');
$JAWG_API_URL = "https://api.jawg.io/routing/route/v1/car";

$TOLLGURU_API_KEY = getenv('TOLLGURU_API_KEY');
$TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2";
$POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service";

//Source and Destination Coordinates..
function getPolyline($source_longitude,$source_latitude,$destination_longitude,$destination_latitude){
  global $JAWG_API_KEY, $JAWG_API_URL;

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
  $p_jawmaps = $data_jawmaps['routes']['0']['geometry'];

  return $p_jawmaps;
}

require_once(__DIR__.'/test_location.php');
require_once(__DIR__.'/get_lat_long.php');
foreach ($locdata as $item) {
$source = getCord($item['from']);
$source_longitude = $source['y'];
$source_latitude = $source['x'];
$destination = getCord($item['to']);
$destination_longitude = $destination['y'];
$destination_latitude = $destination['x'];

$polyline_jawmaps = getPolyline($source_longitude,$source_latitude,$destination_longitude,$destination_latitude);

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
$data = json_decode($response, true);

$tag = $data['route']['costs']['tag'];
$cash = $data['route']['costs']['cash'];

$dumpFile = fopen("dump.txt", "a") or die("unable to open file!");
fwrite($dumpFile, "from =>");
fwrite($dumpFile, $item['from'].PHP_EOL);
fwrite($dumpFile, "to =>");
fwrite($dumpFile, $item['to'].PHP_EOL);
fwrite($dumpFile, "polyline =>".PHP_EOL);
fwrite($dumpFile, $polyline_jawmaps.PHP_EOL);
fwrite($dumpFile, "tag =>");
fwrite($dumpFile, $tag.PHP_EOL);
fwrite($dumpFile, "cash =>");
fwrite($dumpFile, $cash.PHP_EOL);
fwrite($dumpFile, "*************************************************************************".PHP_EOL);

echo "tag = ";
print_r($data['route']['costs']['tag']);
echo "\ncash = ";
print_r($data['route']['costs']['cash']);
echo "\n";
echo "**************************************************************************\n";
}
?>
