#Importing modules
import json
import requests
import os 
import polyline as poly

#API key for jawgmaps
token=os.environ.get('JAWGMAPS_API')
#API key for Tollguru
Tolls_Key = os.environ.get('TOLLGURU_API_KEY')

'''Fetching Geocodes from Jawgmaps'''
def get_geocodes_from_jawgmaps(address):
    url = f"https://api.jawg.io/places/v1/search?text={address}&access-token={token}&size=1"
    longitude,latitude=requests.get(url).json()['features'][0]['geometry']['coordinates']
    return(longitude,latitude)

'''Extracting polyline from Jawgmap'''    
def get_polyline_from_jawgmap(source_longitude,source_latitude,destination_longitude,destination_latitude):
    #Query jawgmaps with Key and Source-Destination coordinates
    url='https://api.jawg.io/routing/route/v1/car/{a},{b};{c},{d}?overview=full&access-token={e}'.format(a=source_longitude,b=source_latitude,c=destination_longitude,d=destination_latitude,e=token)
    #converting the response to json
    response=requests.get(url).json()
    #Extracting polyline
    polyline_from_jawgmaps=response["routes"][0]['geometry']
    #print(polyline)
    return(polyline_from_jawgmaps)
    
'''Calling Tollguru API'''
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
        return(response_tollguru['route']['costs'])
    else:
        raise Exception(response_tollguru['message'])

'''Testing'''
#Importing Functions
from csv import reader,writer
temp_list=[]
with open('testCases.csv','r') as f:
    csv_reader=reader(f)
    for count,i in enumerate(csv_reader):
        #if count>2:
        #  break
        if count==0:
            i.extend(("Polyline","TollGuru_Rates"))
        else:
            try:
                source_longitude,source_latitude=get_geocodes_from_jawgmaps(i[1])
                destination_longitude,destination_latitude=get_geocodes_from_jawgmaps(i[2])
                polyline=get_polyline_from_jawgmap(source_longitude,source_latitude,destination_longitude,destination_latitude)
                i.append(polyline)
            except:
                i.append("Routing Error") 
            
            try:
                rates=get_rates_from_tollguru(polyline)
            except:
                i.append(False)
            if rates=={}:
                i.append("NO_TOLL")
            else:
                i.append(rates['tag'])
        #print(f"{len(i)}   {i}\n")
        temp_list.append(i)

with open('testCases_result.csv','w') as f:
    writer(f).writerows(temp_list)

'''Testing Ends'''