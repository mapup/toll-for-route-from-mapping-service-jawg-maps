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

//const handleRoute = (e, r, body) => console.log(getPolyline(body))

//getRoute(handleRoute)
//return;

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
      body: JSON.stringify({ source: "jawgmaps", polyline: _polyline })
    },
    (e, r, body) => {
      console.log(e);
      console.log(body)
    }
  )
}

getRoute(handleRoute);
