Wunderground
============

Easy to use PHP class for wunderground.com api to get the current weather conditions.
It will cache the wunderground results to prevent excessive usage.

You need your own wunderground.com apiKey. Get it for free at http://www.wunderground.com/weather/api/


Use example :

$objWeather = new WundergroundApi('Maastricht', API_KEY);

echo $objWeather->temperature;
echo $objWeather->strLocation;
echo $objWeather->condition;
echo $objWeather->humidity;
echo $objWeather->wind;
echo $objWeather->image;
