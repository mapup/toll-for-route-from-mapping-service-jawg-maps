# [Jawg Maps](https://www.jawg.io/lab/)

### Get key to access JawgLab (if you have an API key skip this)
#### Step 1: Signup/Login
* Create an account to access [JawgLab](https://www.jawg.io/lab/)
* go to signup/login link https://www.jawg.io/lab/
* you will need to agree to MapQuest's Terms of Service https://www.jawg.io/en/terms/

#### Step 2: Getting api token
* Once logged in you should see you token at https://www.jawg.io/lab/


With this in place, make a GET request: https://api.jawg.io/routing/route/v1/car/${source.longitude},${source.latitude};${destination.longitude},${destination.latitude}?overview=full&access-token=${key}

### Note:
* we will be sending `overview` as `full`.
* Setting overview as full sends us complete route. Default value for `overview` is `simplified`, which is an approximate (smoothed) path of the resulting directions.
* Jawg accepts source and destination, as semicolon seperated
  `${longitude},{latitude}`


```javascript
const request = require("request");
const polyline = require("polyline");

// REST API key from Jawg maps
const key = process.env.JAWG_KEY
const tollguruKey = process.env.TOLLGURU_KEY;

// Dallas, TX
const source = {
    longitude: '-96.7970',
    latitude: '32.7767',
}

// New York, NY
const destination = {
    longitude: '-74.0060',
    latitude: '40.7128'
};


const url = `https://api.jawg.io/routing/route/v1/car/${source.longitude},${source.latitude};${destination.longitude},${destination.latitude}?overview=full&access-token=${key}`;


const head = arr => arr[0];
const flatten = (arr, x) => arr.concat(x);

// JSON path "$..shapePoints"
const getPoints = body => head(body.routes.map(route => route.geometry))

const getPolyline = body => getPoints(JSON.parse(body));

const getRoute = (cb) => request.get(url, cb);

const handleRoute = (e, r, body) => console.log(getPolyline(body))

getRoute(handleRoute)
return;

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

```javascript

const tollguruUrl = 'https://dev.tollguru.com/v1/calc/route';

const handleRoute = (e, r, body) =>  {

  console.log(body);
  const _polyline = getPolyline(body);
  console.log(_polyline);

  request.post(
    {
      url: tollguruUrl,
      headers: {
        'content-type': 'application/json',
        'x-api-key': tollguruKey
      },
      body: JSON.stringify({
        source: "mapquest",
        polyline: _polyline,
        vehicleType: "2AxlesAuto",
        departure_time: "2021-01-05T09:46:08Z"
      })
    },
    (e, r, body) => {
      console.log(e);
      console.log(body)
    }
  )
}

getRoute(handleRoute);
```

The working code can be found in index.js file.

## License
ISC License (ISC). Copyright 2020 &copy;TollGuru. https://tollguru.com/

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted, provided that the above copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
