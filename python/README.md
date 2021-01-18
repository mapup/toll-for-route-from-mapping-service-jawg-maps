# [Jawg Maps](https://www.jawg.io/lab/)

### Get key to access JawgLab (if you have an API key skip this)
#### Step 1: Signup/Login
* Create an account to access [JawgLab](https://www.jawg.io/lab/)
* go to signup/login link https://www.jawg.io/lab/
* you will need to agree to Jawgmap's Terms of Service https://www.jawg.io/en/terms/

#### Step 2: Getting api token
* Once logged in you should see you token at https://www.jawg.io/lab/

With this in place, make a GET request: https://api.jawg.io/routing/route/v1/car/${source.longitude},${source.latitude};${destination.longitude},${destination.latitude}?overview=full&access-token=${key}

#### Step 3: Getting geocodes from Jawgmaps
* Use the following code to get geocodes for a location using Jawgmaps' API
```python
import json
import requests
import os 

#API key for jawgmaps
token=os.environ.get('JAWGMAPS_API')

def get_geocodes_from_jawgmaps(address):
    url = f"https://api.jawg.io/places/v1/search?text={address}&access-token={token}&size=1"
    longitude,latitude=requests.get(url).json()['features'][0]['geometry']['coordinates']
    return(longitude,latitude)
```
#### Step 4: Extracting Polyline from Jawgmaps
### Note:
* We will be sending `overview` as `full`.
* Setting overview as full sends us complete route. Default value for `overview` is `simplified`, which is an approximate (smoothed) path of the resulting directions.
* Jawg accepts source and destination, as semicolon seperated
  `${longitude},{latitude}`

```python
import json
import requests
import os 
import polyline as poly

#API key for jawgmaps
token=os.environ.get('JAWGMAPS_API')

def get_polyline_from_jawgmap(source_longitude,source_latitude,destination_longitude,destination_latitude):
    #Query jawgmaps with Key and Source-Destination coordinates
    url='https://api.jawg.io/routing/route/v1/car/{a},{b};{c},{d}?overview=full&access-token={e}'.format(a=source_longitude,b=source_latitude,c=destination_longitude,d=destination_latitude,e=token)
    #converting the response to json
    response=requests.get(url).json()
    #Extracting polyline
    polyline_from_jawgmaps=response["routes"][0]['geometry']
    #print(polyline)
    return(polyline_from_jawgmaps)
```

Note:

We extracted the polyline for a route from Jawgmaps

We need to send this route polyline to TollGuru API to receive toll information

#### Step 5: Getting toll rates from TollGuru API
## [TollGuru API](https://tollguru.com/developers/docs/)

### Get key to access TollGuru polyline API
* create a dev account to receive a free key from TollGuru https://tollguru.com/developers/get-api-key
* suggest adding `vehicleType` parameter. Tolls for cars are different than trucks and therefore if `vehicleType` is not specified, may not receive accurate tolls. For example, tolls are generally higher for trucks than cars. If `vehicleType` is not specified, by default tolls are returned for 2-axle cars. 
* Similarly, `departure_time` is important for locations where tolls change based on time-of-the-day.
* Call the following function to fetch rates from TollGuru

```python
import json
import requests
import os 

#API key for Tollguru
Tolls_Key = os.environ.get('TOLLGURU_API_KEY')

def get_rates_from_tollguru(polyline):
    #Tollguru querry url
    Tolls_URL = 'https://dev.tollguru.com/v1/calc/route'
    #Tollguru resquest parameters
    headers = {
                'Content-type': 'application/json',
                'x-api-key': Tolls_Key
                }
    params = {
                 #Explore https://tollguru.com/developers/docs/ to get best of all the parameter that tollguru has to offer 
                'source': "jawgmaps",
                'polyline': polyline ,                      #this is polyline that we fetched from the mapping service      
                'vehicleType': '2AxlesAuto',                #'''Visit https://tollguru.com/developers/docs/#vehicle-types to know more options'''
                'departure_time' : "2021-01-05T09:46:08Z"   #'''Visit https://en.wikipedia.org/wiki/Unix_time to know the time format'''
                }
    #Requesting Tollguru with parameters
    response_tollguru= requests.post(Tolls_URL, json=params, headers=headers).json()
    #checking for errors or printing rates
    if str(response_tollguru).find('message')==-1:
        #print('\n The Rates Are ')
        #extracting rates from Tollguru response is no error
        # print(*response_tollguru['summary']['rates'].items(),end="\n\n")
        #print(*response_tollguru['route']['costs'].items(),end="\n\n")
        return(response_tollguru['route']['costs'])
    else:
        raise Exception(response_tollguru['message'])
```

The working code can be found in jawgmaps_polyline.py file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
