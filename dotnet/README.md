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


```.net
using System;
using System.IO;
using System.Net;
using RestSharp;

public static string get_Response(string source_latitude,string source_longitude, string destination_latitude, string destination_longitude){
            string api_key="";
            string url="https://api.jawg.io/routing/route/v1/car/"+source_longitude+","+source_latitude+";"+destination_longitude+","+destination_latitude+"?overview=full&access-token="+api_key;
            WebRequest request = WebRequest.Create(url);
            WebResponse response = request.GetResponse();
            String polyline;
            using (Stream dataStream = response.GetResponseStream())
            {
                StreamReader reader = new StreamReader(dataStream);
                string responseFromServer = reader.ReadToEnd();
                string[] output = responseFromServer.Split("\"geometry\":\"");
                string[] temp = output[1].Split("\"");
                polyline=temp[0];
            }
            response.Close();
            return(polyline);
            
        }
# GET Request to Jawg for Polyline
public static string get_Response(string source_latitude,string source_longitude, string destination_latitude, string destination_longitude){
            string api_key="";
            string url="https://api.jawg.io/routing/route/v1/car/"+source_longitude+","+source_latitude+";"+destination_longitude+","+destination_latitude+"?overview=full&access-token="+api_key;
            WebRequest request = WebRequest.Create(url);
            WebResponse response = request.GetResponse();
            String polyline;
            using (Stream dataStream = response.GetResponseStream())
            {
                StreamReader reader = new StreamReader(dataStream);
                string responseFromServer = reader.ReadToEnd();
                string[] output = responseFromServer.Split("\"geometry\":\"");
                string[] temp = output[1].Split("\"");
                polyline=temp[0];
            }
            response.Close();
            return(polyline);
            
}
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
            var client = new RestClient("https://dev.tollguru.com/v1/calc/route");
            var request1 = new RestRequest(Method.POST);
            request1.AddHeader("content-type", "application/json");
            request1.AddHeader("x-api-key", "");
            request1.AddParameter("application/json", "{\"source\":\"jawgmaps\" , \"polyline\":\""+polyline+"\" }", ParameterType.RequestBody);
            IRestResponse response1 = client.Execute(request1);        
            var content = response1.Content;
            //Console.WriteLine(content);
            string[] result = content.Split("tag\":");
            string[] temp1 = result[1].Split(",");
            string cost = temp1[0];
            return cost;
```

The working code can be found in main.rb file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
