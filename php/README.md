# [Jawg Maps](https://www.jawg.io/lab/)

### Get key to access JawgLab (if you have an API key skip this)
#### Step 1: Signup/Login
* Create an account to access [JawgLab](https://www.jawg.io/lab/)
* go to signup/login link https://www.jawg.io/lab/
* you will need to agree to MapQuest's Terms of Service https://www.jawg.io/en/terms/

#### Step 2: Getting api token
* Once logged in you should see you token at https://www.jawg.io/lab/


With this in place, make a GET request: https://api.jawg.io/routing/route/v1/car/#{SOURCE[:longitude]},#{SOURCE[:latitude]};#{DESTINATION[:longitude]},#{DESTINATION[:latitude]}?overview=full&access-token=#{KEY}

### Note:
* we will be sending `overview` as `full`.
* Setting overview as full sends us complete route. Default value for `overview` is `simplified`, which is an approximate (smoothed) path of the resulting directions.
* Jawg accepts source and destination, as semicolon seperated
  `{:longitude,:latitude}`


```php

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

```

Note:

We extracted the polyline for a route from Jawgmaps

We need to send this route polyline to TollGuru API to receive toll information

## [TollGuru API](https://tollguru.com/developers/docs/)

### Get key to access TollGuru polyline API
* create a dev account to receive a free key from TollGuru https://tollguru.com/developers/get-api-key
* suggest adding `vehicleType` parameter. Tolls for cars are different than trucks and therefore if `vehicleType` is not specified, may not receive accurate tolls. For example, tolls are generally higher for trucks than cars. If `vehicleType` is not specified, by default tolls are returned for 2-axle cars. 
* Similarly, `departure_time` is important for locations where tolls change based on time-of-the-day and can be passed through `$postdata`.

the last line can be changed to following

```php

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

```

The working code can be found in main.rb file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
