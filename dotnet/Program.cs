using System;
using System.IO;
using System.Net;
using RestSharp;

namespace JawgLab
{
    static class Constants
    {
        public const string JAWG_API_KEY = Environment.GetEnvironmentVariable("JAWG_API_KEY");
        public const string JAWG_API_URL = "https://api.jawg.io/routing/route/v1/car";

        public const string TOLLGURU_API_KEY = Environment.GetEnvironmentVariable("TOLLGURU_API_KEY");
        public const string TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2";
        public const string POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service";

        // Philadelphia, PA
        public const string source_longitude = "-75.1652";
        public const string source_latitude = "39.9526";

        // New York, NY
        public const string destination_longitude = "-74.0060";
        public const string destination_latitude = "40.7128";
    }

    class Program
    {
        public static string get_Response(string source_latitude, string source_longitude, string destination_latitude, string destination_longitude)
        {
            string url = Constants.JAWG_API_URL + "/" + source_longitude + "," + source_latitude + ";" + destination_longitude + "," + destination_latitude + "?overview=full&access-token=" + Constants.JAWG_API_KEY;
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
                polyline = temp[0];
            }
            response.Close();
            return (polyline);

        }
        public static string Post_Tollguru(string polyline)
        {
            var client = new RestClient(Constants.TOLLGURU_API_URL + "/" + Constants.POLYLINE_ENDPOINT);
            var request1 = new RestRequest(Method.POST);
            request1.AddHeader("content-type", "application/json");
            request1.AddHeader("x-api-key", Constants.TOLLGURU_API_KEY);
            request1.AddParameter("application/json", "{\"source\":\"jawgmaps\" , \"polyline\":\"" + polyline + "\" }", ParameterType.RequestBody);
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
            string polyline = get_Response(Constants.source_latitude, Constants.source_longitude, Constants.destination_latitude, Constants.destination_longitude);
            Console.WriteLine(Post_Tollguru(polyline));
        }
    }
}
