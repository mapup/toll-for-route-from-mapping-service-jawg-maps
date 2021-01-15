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
* To convert source string to lat-long pair we make GET request to JAWG places API. We set size = 1 to limit our search to first result


```ruby
require 'HTTParty'
require 'json'

KEY = ENV['JAWG_KEY']

def get_coordinates_hash(location)
    #GET Request to JAWG places API
    geocoding_url = "https://api.jawg.io/places/v1/search?text=#{location}&access-token=#{KEY}&size=1"
    geocoding_resp = JSON.parse(HTTParty.get(geocoding_url).body)
    coord_parsed = (geocoding_resp['features'].pop)["geometry"]["coordinates"]
    return {"longitude" => coord_parsed[0], "latitude" => coord_parsed[1]}
end

# Source Details using JAWG geocoding API 
SOURCE = get_coordinates_hash("Dallax ,TX")
# Destination Details using JAWG geocoding API 
DESTINATION = get_coordinates_hash("New York, NY")

# GET Request to Jawg for Polyline
KEY = "g1BcsOQSe115CeqL9z7x3SOsf1zXZli44lyYgXZj0zP6zuYDf4IgB3enzXXEfIB4"
JAWG_URL = "https://api.jawg.io/routing/route/v1/car/#{SOURCE[:longitude]},#{SOURCE[:latitude]};#{DESTINATION[:longitude]},#{DESTINATION[:latitude]}?overview=full&access-token=#{KEY}"
RESPONSE = HTTParty.get(JAWG_URL).body
json_parsed = JSON.parse(RESPONSE)

# Extracting jawg polyline from JSON.
jawg_polyline = json_parsed['routes'].map { |x| x['geometry'] }.pop
```

Note:

We extracted the polyline for a route from Jawgmaps

We need to send this route polyline to TollGuru API to receive toll information

## [TollGuru API](https://tollguru.com/developers/docs/)

### Get key to access TollGuru polyline API
* create a dev account to receive a free key from TollGuru https://tollguru.com/developers/get-api-key
* suggest adding `vehicleType` parameter. Tolls for cars are different than trucks and therefore if `vehicleType` is not specified, may not receive accurate tolls. For example, tolls are generally higher for trucks than cars. If `vehicleType` is not specified, by default tolls are returned for 2-axle cars. 
* Similarly, `departure_time` is important for locations where tolls change based on time-of-the-day.

the last line can be changed to following

```ruby
# Sending POST request to TollGuru
TOLLGURU_URL = 'https://dev.tollguru.com/v1/calc/route'
TOLLGURU_KEY = ENV['TOLLGURU_KEY']
headers = {'content-type' => 'application/json', 'x-api-key' => TOLLGURU_KEY}
body = {'source' => "jawg", 'polyline' => jawg_polyline, 'vehicleType' => "2AxlesAuto", 'departure_time' => "2021-01-05T09:46:08Z"}
tollguru_response = HTTParty.post(TOLLGURU_URL,:body => body.to_json, :headers => headers)
```

The working code can be found in main.rb file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
