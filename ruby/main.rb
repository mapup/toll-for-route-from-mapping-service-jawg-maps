require 'HTTParty'
require 'json'

KEY = ENV['JAWG_KEY']

def get_coordinates_hash(location)
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
JAWG_URL = "https://api.jawg.io/routing/route/v1/car/#{SOURCE["longitude"]},#{SOURCE["latitude"]};#{DESTINATION["longitude"]},#{DESTINATION["latitude"]}?overview=full&access-token=#{KEY}"
RESPONSE = HTTParty.get(JAWG_URL).body
json_parsed = JSON.parse(RESPONSE)

# Extracting jawg polyline from JSON.
jawg_polyline = json_parsed['routes'].map { |x| x['geometry'] }.pop

# Sending POST request to TollGuru
TOLLGURU_URL = 'https://dev.tollguru.com/v1/calc/route'
TOLLGURU_KEY = ENV['TOLLGURU_KEY']
headers = {'content-type' => 'application/json', 'x-api-key' => TOLLGURU_KEY}
body = {'source' => "jawg", 'polyline' => jawg_polyline, 'vehicleType' => "2AxlesAuto", 'departure_time' => "2021-01-05T09:46:08Z"}
tollguru_response = HTTParty.post(TOLLGURU_URL,:body => body.to_json, :headers => headers)
