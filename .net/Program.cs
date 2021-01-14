using System;
using System.IO;
using System.Net;
using RestSharp;
namespace mapquest
{
    public class Program
    {
        public static void Main()
        {
            string api_key="";
            //Source Details in latitude-longitude pair (Dallas, TX - coordinates)
            string source_longitude="-96.7970";  
            string source_latitude="32.7767";
            //Destination Details in latitude-longitude pair (New York, NY - coordinates)
            string destination_longitude="-74.0060";
            string destination_latitude="40.7128";
            string polyline;
            //Concatinating the srting to form the GET request url
            string url="http://www.mapquestapi.com/directions/v2/route?key="+source_longitude+","+source_latitude+";"+destination_longitude+","+destination_latitude+"?geometries=polyline&access_token="+api_key+"&overview=full";
            // Make a connection
            WebRequest request = WebRequest.Create(url);
            // Get the response.
            WebResponse response = request.GetResponse();
            // Get the stream containing content returned by the server.
            // The using block ensures the stream is automatically closed.           
            using (Stream dataStream = response.GetResponseStream())
            {
                // Open the stream using a StreamReader for easy access.
                StreamReader reader = new StreamReader(dataStream);
                // Read the content.
                string responseFromServer = reader.ReadToEnd();
                //Logic to split the polyline from the string
                string[] output = responseFromServer.Split("\"shapePoints\":[");
                string[] temp = output[1].Split("]");
                //Storing polyline 
                polyline=temp[0];
            }
            response.Close();
/***************************************************************TOLL GURU API***************************************************************/
        
        string toll_guru_api = ""; //Provide toll guru api
        
        var client = new RestClient("https://dev.tollguru.com/v1/calc/route");
        //mentioning the method of connection
        var request1 = new RestRequest(Method.POST);
        
        request1.AddHeader("content-type", "application/json");
        request1.AddHeader("x-api-key", toll_guru_api);
        //Providing parameter, Source,Polyline are most important
        request1.AddParameter("application/json", "{\"source\":\"mapquest\" , \"polyline\":\""+polyline+"\" }", ParameterType.RequestBody);
        //request1.AddParameter("application/json", "{\"from\":{\"address\":\"Main str, Dallas, TX\"},\"to\":{\"address\":\"Addison, TX\"},\"waypoints\":[{\"address\":\"Plano, TX\"},{\"address\":\"Allen, TX\"}],\"vehicleType\":\"2AxlesAuto\",\"departure_time\":1551541566,\"fuelPrice\":2.79,\"fuelPriceCurrency\":\"USD\",\"fuelEfficiency\":{\"city\":24,\"hwy\":30,\"units\":\"mpg\"},\"truck\":{\"limitedWeight\":44000},\"driver\":{\"wage\":30,\"rounding\":15,\"valueOfTime\":0},\"state_mileage\":true,\"hos\":{\"rule\":60,\"dutyHoursBeforeEndOfWorkDay\":11,\"dutyHoursBeforeRestBreak\":7,\"drivingHoursBeforeEndOfWorkDay\":11,\"timeRemaining\":60}}", ParameterType.RequestBody);
        IRestResponse response1 = client.Execute(request1);        
        var content = response1.Content;
        Console.WriteLine(content);
        //Logic to split the cost from the received string
        string[] result = content.Split("tag\":");
        string[] temp1 = result[1].Split(",");
        string cost = temp1[0];
        Console.WriteLine(cost); //Will give the cost of Primary_tag 

    }
}
}