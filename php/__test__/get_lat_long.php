<?php
function getCord($address){
$key = getenv('JAWG_API_KEY');

$url = 'https://api.jawg.io/places/v1/search?text='.urlencode($address).'&access-token='.$key.'&size=1';

$ch = curl_init();

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$responseJson = curl_exec($ch);
curl_close($ch);

$response = json_decode($responseJson, true);

$location = array(
	'x' => $response['features']['0']['geometry']['coordinates']['1'],
    'y' => $response['features']['0']['geometry']['coordinates']['0']
);

return $location;
 }
?>