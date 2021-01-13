#Importing modules
import json
import requests
import os 
import polyline as Poly
#API key for jawgmaps
#token=os.environ.get('jawgmaps_api_key')
token=os.environ.get('jawgmaps_api')
#Source and Destination Coordinates
source_longitude='-0.429371'
source_latitude='47.521557'
destination_longitude='0.109369'
destination_latitude='48.465013'
#Query jawgmaps with Key and Source-Destination coordinates
url='https://api.jawg.io/routing/route/v1/car/{a},{b};{c},{d}?overview=full&access-token={e}'.format(a=source_longitude,b=source_latitude,c=destination_longitude,d=destination_latitude,e=token)
#converting the response to json
response=requests.get(url).json()
#checking for errors in response-to do
#The response is a dict where Polyline is inside first element named "routes" , first element is a list , go to 1st element there
#you will find a key named "geometry" which is essentially the Polyline''' 
#Extracting polyline
polyline=response["routes"][0]['geometry']
'''------------------------------------------------------Calling Tollguru API---------------------------------------------------------------------'''
#API key for Tollguru
Tolls_Key = os.environ.get('tollguru_api')
#Tollguru querry url
Tolls_URL = 'https://dev.tollguru.com/v1/calc/route'
#Tollguru resquest parameters
headers = {
            'Content-type': 'application/json',
            'x-api-key': Tolls_Key
          }
params = {
            'source': "jawgmaps",
            'polyline': polyline ,                      #this is polyline that we fetched from the mapping service      
            #'polyline': 'some_wrong_polyline_' ,      
            'vehicleType': '2AxlesAuto',                #'''TODO - Need to users list of acceptable values for vehicle type'''
            'departure_time' : "2021-01-05T09:46:08Z"   #'''TODO - Specify time formats'''
        }
#Requesting Tollguru with parameters
response_tollguru= requests.post(Tolls_URL, json=params, headers=headers).json()
#checking for errors or printing rates
if str(response_tollguru).find('message')==-1:
    print('\n The Rates Are ')
    #extracting rates from Tollguru response is no error
    print(*response_tollguru['summary']['rates'].items(),end="\n\n")
else:
    raise Exception(response_tollguru['message'])

