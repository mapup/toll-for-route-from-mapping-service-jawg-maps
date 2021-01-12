require 'HTTParty'
require 'json'

# Source Details in latitude-longitude pair (Dallas, TX - coordinates)
SOURCE = {longitude: '-96.7970', latitude: '32.7767'}
# Destination Details in latitude-longitude pair (New York, NY - coordinates)
DESTINATION = {longitude: '-96.924', latitude: '32.9756' }

# GET Request to Jawg for Polyline
KEY = ENV['JAWG_KEY']
JAWG_URL = "https://api.jawg.io/routing/route/v1/car/#{SOURCE[:longitude]},#{SOURCE[:latitude]};#{DESTINATION[:longitude]},#{DESTINATION[:latitude]}?overview=full&access-token=#{KEY}"
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
