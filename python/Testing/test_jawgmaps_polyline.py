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

# Explore https://tollguru.com/toll-api-docs to get best of all the parameter that tollguru has to offer
request_parameters = {
    "vehicle": {
        "type": "2AxlesAuto",
    },
    # Visit https://en.wikipedia.org/wiki/Unix_time to know the time format
    "departure_time": "2021-01-05T09:46:08Z",
}

# Fetching Geocodes from Jawgmaps
def get_geocodes_from_jawgmaps(address):
    url = f"{JAWG_GEOCODE_API_URL}?text={address}&access-token={JAWG_API_KEY}&size=1"
    longitude, latitude = requests.get(url).json()["features"][0]["geometry"][
        "coordinates"
    ]
    return (longitude, latitude)


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


"""Testing"""
# Importing Functions
from csv import reader, writer
import time

temp_list = []
with open("testCases.csv", "r") as f:
    csv_reader = reader(f)
    for count, i in enumerate(csv_reader):
        # if count>2:
        #   break
        if count == 0:
            i.extend(
                (
                    "Input_polyline",
                    "Tollguru_Tag_Cost",
                    "Tollguru_Cash_Cost",
                    "Tollguru_QueryTime_In_Sec",
                )
            )
        else:
            try:
                source_longitude, source_latitude = get_geocodes_from_jawgmaps(i[1])
                (
                    destination_longitude,
                    destination_latitude,
                ) = get_geocodes_from_jawgmaps(i[2])
                polyline = get_polyline_from_jawgmap(
                    source_longitude,
                    source_latitude,
                    destination_longitude,
                    destination_latitude,
                )
                i.append(polyline)
            except:
                i.append("Routing Error")

            start = time.time()
            try:
                rates = get_rates_from_tollguru(polyline)
            except:
                i.append(False)
            time_taken = time.time() - start
            if rates == {}:
                i.append((None, None))
            else:
                try:
                    tag = rates["tag"]
                except:
                    tag = None
                try:
                    cash = rates["cash"]
                except:
                    cash = None
                i.extend((tag, cash))
            i.append(time_taken)
        # print(f"{len(i)}   {i}\n")
        temp_list.append(i)
        # time.sleep(3)

with open("testCases_result.csv", "w") as f:
    writer(f).writerows(temp_list)

"""Testing Ends"""
