require 'HTTParty'
require 'json'

JAWG_API_KEY = ENV["JAWG_API_KEY"]
JAWG_API_URL = "https://api.jawg.io/routing/route/v1/car"
JAWG_GEOCODE_API_URL = "https://api.jawg.io/places/v1/search"

TOLLGURU_API_KEY = ENV["TOLLGURU_API_KEY"]
TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2"
POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service"

source = get_coordinates_hash("Dallax ,TX")
destination = get_coordinates_hash("New York, NY")

def get_coordinates_hash(location)
    geocoding_url = "#{JAWG_GEOCODE_API_URL}?text=#{location}&access-token=#{JAWG_API_KEY}&size=1"
    geocoding_resp = JSON.parse(HTTParty.get(geocoding_url).body)
    coord_parsed = (geocoding_resp['features'].pop)["geometry"]["coordinates"]
    return {"longitude" => coord_parsed[0], "latitude" => coord_parsed[1]}
end


# GET Request to Jawg for Polyline
JAWG_URL = "#{JAWG_API_URL}/#{source["longitude"]},#{source["latitude"]};#{destination["longitude"]},#{destination["latitude"]}?overview=full&access-token=#{JAWG_API_KEY}"
RESPONSE = HTTParty.get(JAWG_URL).body
json_parsed = JSON.parse(RESPONSE)

# Extracting jawg polyline from JSON.
jawg_polyline = json_parsed['routes'].map { |x| x['geometry'] }.pop

# Sending POST request to TollGuru
TOLLGURU_URL = "#{TOLLGURU_API_URL}/#{POLYLINE_ENDPOINT}"
headers = {'content-type' => 'application/json', 'x-api-key' => TOLLGURU_API_KEY}
body = {'source' => "jawg", 'polyline' => jawg_polyline, 'vehicleType' => "2AxlesAuto", 'departure_time' => "2021-01-05T09:46:08Z"}
tollguru_response = HTTParty.post(TOLLGURU_URL,:body => body.to_json, :headers => headers)
