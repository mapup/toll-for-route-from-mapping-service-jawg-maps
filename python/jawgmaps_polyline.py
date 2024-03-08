# Importing modules
import json
import requests
import os
import polyline as poly

JAWG_API_KEY = os.environ.get("JAWG_API_KEY")
JAWG_API_URL = "https://api.jawg.io/routing/route/v1/car"
JAWG_GEOCODE_API_URL = "https://api.jawg.io/places/v1/search"

TOLLGURU_API_KEY = os.environ.get("TOLLGURU_API_KEY")
TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2"
POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service"

source = "Philadelphia, PA"
destination = "New York, NY"

# Explore https://tollguru.com/toll-api-docs to get best of all the parameter that tollguru has to offer
request_parameters = {
    "vehicle": {
        "type": "2AxlesAuto",
    },
    # Visit https://en.wikipedia.org/wiki/Unix_time to know the time format
    "departure_time": "2021-01-05T09:46:08Z",
}

def get_geocodes_from_jawgmaps(address):
    try:
        url = f"{JAWG_GEOCODE_API_URL}?text={address}&access-token={JAWG_API_KEY}&size=1"
        response = requests.get(url)
        response.raise_for_status()  # Raise an error for bad response status

        data = response.json()
        # Check if the response contains 'features' key and it's not empty
        if "features" in data and data["features"]:
            longitude, latitude = data["features"][0]["geometry"]["coordinates"]
            return longitude, latitude
        else:
            print("No geocodes found for the given address.")
            return None
    except requests.exceptions.RequestException as e:
        print(f"An error occurred while fetching geocodes: {e}")
        return None
    except (KeyError, IndexError) as e:
        print(f"Error processing API response: {e}")
        return None

# Extracting polyline from Jawgmap
def get_polyline_from_jawgmap(
    source_longitude, source_latitude, destination_longitude, destination_latitude
):
    # Query jawgmaps with Key and Source-Destination coordinates
    url = "{a}/{b},{c};{d},{e}?overview=full&access-token={f}".format(
        a=JAWG_API_URL,
        b=source_longitude,
        c=source_latitude,
        d=destination_longitude,
        e=destination_latitude,
        f=JAWG_API_KEY,
    )
    # converting the response to json
    response = requests.get(url).json()
    # Extracting polyline
    polyline_from_jawgmaps = response["routes"][0]["geometry"]
    # print(polyline)
    return polyline_from_jawgmaps


# Calling Tollguru API
def get_rates_from_tollguru(polyline):
    # Tollguru querry url
    Tolls_URL = f"{TOLLGURU_API_URL}/{POLYLINE_ENDPOINT}"
    # Tollguru resquest parameters
    headers = {"Content-type": "application/json", "x-api-key": TOLLGURU_API_KEY}
    params = {
        # Explore https://tollguru.com/developers/docs/ to get best of all the parameter that tollguru has to offer
        "source": "jawgmaps",
        "polyline": polyline,  # this is polyline that we fetched from the mapping service
        **request_parameters,
    }
    # Requesting Tollguru with parameters
    response_tollguru = requests.post(Tolls_URL, json=params, headers=headers).json()
    # checking for errors or printing rates
    if str(response_tollguru).find("message") == -1:
        return response_tollguru["route"]["costs"]
    else:
        raise Exception(response_tollguru["message"])


"""Program Start"""
# Step 1 :Get geocodes for source and destination from Jawgmaps
source_longitude, source_latitude = get_geocodes_from_jawgmaps(source)
destination_longitude, destination_latitude = get_geocodes_from_jawgmaps(destination)

# Step 2 : Get polyline from Jawgmasp
polyline_from_jawgmaps = get_polyline_from_jawgmap(
    source_longitude, source_latitude, destination_longitude, destination_latitude
)
# Step 3 : Get toll rates from tollguru
rates_from_tollguru = get_rates_from_tollguru(polyline_from_jawgmaps)

# Print the rates of all the available modes of payment
if rates_from_tollguru == {}:
    print("The route doesn't have tolls")
else:
    print(f"The rates are \n {rates_from_tollguru}")

"""Program Ends"""
