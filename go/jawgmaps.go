package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"log"
	"net/http"
	"os"
	"time"
)

//Source Coordinates
const (
	source_longitude float64 = -96.7970
	source_latitude float64 = 32.7767
)

// Destination Coordinates
const (
	destination_longitude float64 = -74.0060
	destination_latitude float64 = 40.7128
)
func main() {

	//	Getting polyline from Jwag Maps

	// Key for Jwag Maps

	key_JWAGMAPS := os.Getenv("JWAGMAPS_KEY")

	url := fmt.Sprintf("https://api.jawg.io/routing/route/v1/car/%v,%v;%v,%v?overview=full&access-token=%s", source_longitude, source_latitude, destination_longitude, destination_latitude, key_JWAGMAPS)
	spaceClient := http.Client{
		Timeout: time.Second * 15, // Timeout after 15 seconds
	}

	req, err := http.NewRequest(http.MethodGet, url, nil)
	if err != nil {
		log.Fatal(err)
	}

	req.Header.Set("User-Agent", "spacecount-tutorial")

	res, getErr := spaceClient.Do(req)
	if getErr != nil {
		log.Fatal(getErr)
	}

	if res.Body != nil {
		defer res.Body.Close()
	}

	body, readErr := ioutil.ReadAll(res.Body)
	if readErr != nil {
		log.Fatal(readErr)
	}
	var result map[string]interface{}

	jsonErr := json.Unmarshal(body, &result)
	if jsonErr != nil {
		log.Fatal(result)
	}

	polyline := result["routes"].([]interface{})[0].(map[string]interface{})["geometry"].(string)
	fmt.Printf("\n%v\n\n", polyline)

	url_tollguru := "https://dev.tollguru.com/v1/calc/route"

	// key for Tollguru
	key_tollguru := os.Getenv("Tollgurukey")

	requestBody, err := json.Marshal(map[string]string{
		"source":         "jawg",
		"polyline":       polyline,
		"vehicleType":    "2AxlesAuto",
		"departure_time": "2021-01-05T09:46:08Z",
	})

	request, err := http.NewRequest("POST", url_tollguru, bytes.NewBuffer(requestBody))
	request.Header.Set("x-api-key", key_tollguru)
	request.Header.Set("Content-Type", "application/json")

	client := &http.Client{}
	resp, err := client.Do(request)
	if err != nil {
		panic(err)
	}
	defer resp.Body.Close()

	body, error := ioutil.ReadAll(resp.Body)
	if error != nil {
		log.Fatal(err)
	}

	fmt.Println("\nresponse Body:\n", string(body))
}

