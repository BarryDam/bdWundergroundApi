Wunderground
============

Easy to use PHP class for wunderground.com api to get the current weather conditions.
It will cache the wunderground results to prevent excessive usage.

You need your own wunderground.com apiKey. Get it for free at http://www.wunderground.com/weather/api/


Use example :

$objWeather = new WundergroundApi('Maastricht', API_KEY);

$objWeather->temperature;

$objWeather->strLocation;

$objWeather->condition;

$objWeather->humidity;

$objWeather->wind;

$objWeather->image;
