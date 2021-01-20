using System;
using System.IO;
using System.Net;
using RestSharp;

namespace JawgLab
{
    class Program
    {
        public static string get_Response(string source_latitude,string source_longitude, string destination_latitude, string destination_longitude){
            string api_key="";
            string url="https://api.jawg.io/routing/route/v1/car/"+source_longitude+","+source_latitude+";"+destination_longitude+","+destination_latitude+"?overview=full&access-token="+api_key;
            WebRequest request = WebRequest.Create(url);
            // Get the response.
            WebResponse response = request.GetResponse();
            // Display the status.
            String polyline;
            // Get the stream containing content returned by the server.
            // The using block ensures the stream is automatically closed.           
            using (Stream dataStream = response.GetResponseStream())
            {
                // Open the stream using a StreamReader for easy access.
                StreamReader reader = new StreamReader(dataStream);
                // Read the content.
                string responseFromServer = reader.ReadToEnd();
                //Console.WriteLine(responseFromServer);
                // Display the content.
                string[] output = responseFromServer.Split("\"geometry\":\"");
                string[] temp = output[1].Split("\"");
                polyline=temp[0];
            }
            response.Close();
            return(polyline);
            
        }
        public static string Post_Tollguru(string polyline){
            var client = new RestClient("https://dev.tollguru.com/v1/calc/route");
            var request1 = new RestRequest(Method.POST);
            request1.AddHeader("content-type", "application/json");
            request1.AddHeader("x-api-key", "Api_Key");
            request1.AddParameter("application/json", "{\"source\":\"jawgmaps\" , \"polyline\":\""+polyline+"\" }", ParameterType.RequestBody);
            IRestResponse response1 = client.Execute(request1);        
            var content = response1.Content;
            //Console.WriteLine(content);
            string[] result = content.Split("tag\":");
            string[] temp1 = result[1].Split(",");
            string cost = temp1[0];
            return cost;
        }
        static void Main(string[] args)
        {
            string source_longitude="-84.995150";
            string source_latitude="41.729450";
            string destination_longitude="-87.182250";
            string destination_latitude="41.58333";
            string polyline = get_Response(source_latitude,source_longitude,destination_latitude,destination_longitude);
            Console.WriteLine(Post_Tollguru(polyline));                
    }
}
}
