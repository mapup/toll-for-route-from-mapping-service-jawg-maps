const request = require("request");

const JAWG_API_KEY = process.env.JAWG_API_KEY;
const JAWG_API_URL = "https://api.jawg.io/routing/route/v1/car";

const TOLLGURU_API_KEY = process.env.TOLLGURU_API_KEY;
const TOLLGURU_API_URL = "https://apis.tollguru.com/toll/v2";
const POLYLINE_ENDPOINT = "complete-polyline-from-mapping-service";

const source = { longitude: '-96.7970', latitude: '32.7767' }; // Dallas, TX
const destination = { longitude: '-74.0060', latitude: '40.7128' }; // New York, NY

const url = `${JAWG_API_URL}/${source.longitude},${source.latitude};${destination.longitude},${destination.latitude}?overview=full&access-token=${JAWG_API_KEY}`;

const head = arr => arr[0];

// JSON path "$..shapePoints"
const getPoints = body => head(body.routes.map(route => route.geometry))

const getPolyline = body => getPoints(JSON.parse(body));

const getRoute = (cb) => request.get(url, cb);

const handleRoute = (e, r, body) => {

  console.log(body);
  const _polyline = getPolyline(body);
  console.log(_polyline);

  request.post(
    {
      url: `${TOLLGURU_API_URL}/${POLYLINE_ENDPOINT}`,
      headers: {
        'content-type': 'application/json',
        'x-api-key': TOLLGURU_API_KEY
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
