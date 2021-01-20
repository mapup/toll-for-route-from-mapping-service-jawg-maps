require 'HTTParty'
require 'json'
require 'cgi'

$key = ENV['JAWG_KEY']

def get_toll_rate(source,destination)

    def get_coordinates_hash(location)
        geocoding_url = "https://api.jawg.io/places/v1/search?text=#{CGI::escape(location)}&access-token=#{$key}&size=1"
        geocoding_resp = HTTParty.get(geocoding_url)
        begin
            coord_parsed = (JSON.parse(geocoding_resp.body)['features'].pop)["geometry"]["coordinates"]
            return {"longitude" => coord_parsed[0], "latitude" => coord_parsed[1]}
        rescue
            raise "#{geocoding_resp.response.code} #{geocoding_resp.response.message}, #{geocoding_resp.body}"
        end

    end
    sleep 2
    # Source Details using JAWG geocoding API 
    source = get_coordinates_hash(source)
    # Destination Details using JAWG geocoding API 
    sleep 2
    destination = get_coordinates_hash(destination)
    sleep 2
    # GET Request to Jawg for Polyline
    jawg_url = "https://api.jawg.io/routing/route/v1/car/#{source["longitude"]},#{source["latitude"]};#{destination["longitude"]},#{destination["latitude"]}?overview=full&access-token=#{$key}"
    response = HTTParty.get(jawg_url)
    begin
        if response.response.code == '200'
            json_parsed = JSON.parse(response.body)
            # Extracting jawg polyline from JSON.
            jawg_polyline = json_parsed['routes'].map { |x| x['geometry'] }.pop
        else
            raise "error"
        end
    rescue Exception => e
        raise "#{response.response.code} #{response.response.message}"
    end

    # Sending POST request to TollGuru
    tollguru_url = 'https://dev.tollguru.com/v1/calc/route'
    tollguru_key = ENV['TOLLGURU_KEY']
    headers = {'content-type' => 'application/json', 'x-api-key' => tollguru_key}
    body = {'source' => "jawg", 'polyline' => jawg_polyline, 'vehicleType' => "2AxlesAuto", 'departure_time' => "2021-01-05T09:46:08Z"}
    tollguru_response = HTTParty.post(tollguru_url,:body => body.to_json, :headers => headers, :timeout => 200)
    begin
        toll_body = JSON.parse(tollguru_response.body)    
        if toll_body["route"]["hasTolls"] == true
            return jawg_polyline,toll_body["route"]["costs"]["tag"], toll_body["route"]["costs"]["cash"] 
        else
            raise "No tolls encountered in this route"
        end
    rescue Exception => e
        puts e.message 
    end
end
