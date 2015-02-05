Wunderground
============

Easy to use PHP class for wunderground.com api to get the current weather conditions.
It will cache the wunderground results to prevent excessive usage.

You need your own wunderground.com apiKey. Get it for free at http://www.wunderground.com/weather/api/


### Example:

```
   $objWeather = new bdWundergroundApi('Maastricht', API_KEY);

   $objWeather->temperature;

   $objWeather->strLocation;

   $objWeather->condition;

   $objWeather->humidity;

   $objWeather->wind;

   $objWeather->image;

```

##BUY ME A BEER##
[![PayPayl donate button](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XX68BNMVCD7YS "Donate once-off to this project using Paypal")