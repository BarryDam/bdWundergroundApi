<?php
	/*****************************************************************
	 * WundergroundApi v 1.0 (05-02-2014)
	 * @copyright Barry Dam - BIC 
	 * Deze weer info maakt gebruik van wunderground.com api
	 *
	 * De api query kan een plaatsnaam bevatten of lat lng coordinaten!
	 * 
	 * bij de apiQuery heeft latlng coordinaten de voorkeur omdat sommige lokale plaatsnamen
	 * niet herkend worden (bijv Oirsbeek)
	 *
	 * de gratis api kan 500 apicalls per dag aan, maximaal 10 per minuut
	 *
	 * gebruik door $objWeather = new WundergroundApi('Maastricht', apikey, 'NL');
	 * alles in de public $data is opvraagbaar... en in de arrApiResult staat alles (dus je kunt meer info gebruiken)
	 *
	 *	LET OP: @use URL_BASE en FILE_PATH !!
	 *
	*******************************************************************/
	class WundergroundApi {
		
		private $config = array(
			'fileCache'			=> false,
			'apiKey'			=> false,
			'apiURL'			=> false,
			'apiLang'			=> 'NL',
			'strFolder'			=> 'modules/weather/', // folder name relative to  root
			'strImageFolder'	=> 'modules/weather/images/'  // folder name relative to  root
		);

		/**
		 * @example $objWeather->strLocation 
		 */
		public $data = array(
			'strLocation' 	=> false,
			'arrApiResult'	=> false, // the decoded json from cache or api result
			'temperature'	=> false,
			'condition'		=> false,
			'humidity'		=> false,
			'wind'			=> false,
			'image'			=> false
		);
		
		/**
		 *  Magic methods 
		 */

		/**
		 * @param (string) $getLangAbbr = optional
		 */
		public function __construct($getLocation = false, $getApiKey = false, $getLangAbbr = false)
		{
			if (! $getLocation || ! $getApiKey) throw new Exception('Check your clsWeather Params!', 1);
			$this->config['apiKey'] 	= $getApiKey;
			$this->strLocation		= $getLocation;
			if ($getLangAbbr) $this->config['apiLang'] = strtoupper($getLangAbbr);
			$this->setData();
		}

		public function __set($getName, $getValue)
		{
			$this->data[$getName] = $getValue;
			if ($getName == 'strLocation') {
				$this->config['fileCache'] = FILE_PATH.$this->config['strFolder'].'cache/json.'.strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $getValue)).'.'.$this->config['apiLang'].'.txt';
				$this->config['apiURL'] 	= 'http://api.wunderground.com/api/'.$this->config['apiKey'].'/conditions/lang:'.$this->config['apiLang'].'/q/'.$getValue.'.json';
			}
		}

		public function __get($getName)
		{
			if (array_key_exists($getName, $this->data)) {
				return $this->data[$getName];
			}
		}

		/**
		 * setters 
		 */

		private function setData($boolSecondAttempt = false)
		{
			/**
			 * first check for cache 
			 */
				if (is_file($this->config['fileCache']) &&
					(time() - filemtime($this->config['fileCache'])) < (60*60) // fileage younger than 1 hour
				) {
					$this->arrApiResult = json_decode(file_get_contents($this->config['fileCache']), true);
				}
			/**
			 * get new data 
			 */
				if (! $this->arrApiResult) {
					/**
					 * Curl request 
					 */
					$ch 	= curl_init();
					curl_setopt($ch, CURLOPT_URL, $this->config['apiURL']);
					curl_setopt($ch, CURLOPT_HEADER, 0);// 1 = headers aan
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
					$arrCurlResult['data'] 	= curl_exec($ch);
					$arrCurlResult['info'] 	= curl_getinfo($ch);
					$arrCurlResult['error'] 	= curl_error($ch);
					curl_close($ch);

					/**
					 * Process the curl data &nd check for current_observation
					 */
					$jsonData = ($arrCurlResult['data']) ?  json_decode($arrCurlResult['data'], true) : array() ;
					if (! empty($jsonData['current_observation'])) {
						$this->arrApiResult = $jsonData;
						/**
						 *  save to cache file
						 */
						if ($arrCurlResult['data']) file_put_contents($this->config['fileCache'], $arrCurlResult['data']);		
					} else if($boolSecondAttempt) {
						/**
						 * Don't throw an exception but just log the error 
						 */
						file_put_contents(FILE_PATH.$this->config['strFolder'].'cache/error'.time().'.txt', implode($arrCurlResult));
					} else {
						/* try an second attempt (force the cache or redo a curl request) */
						if (is_file($this->config['fileCache'])) touch($this->config['fileCache']);
						$this->setData(true);
					}			
				}
			/**
			 *  set the data
			 */
			if (! $this->arrApiResult) return ;
			$arrObservation 	= $this->arrApiResult['current_observation'];
			$this->temperature 	= round($arrObservation['temp_c']).'&deg;';
			$this->condition	= $arrObservation['weather'];
			$this->image		= URL_BASE.$this->config['strImageFolder'].self::setData_image($arrObservation['icon']);
			$this->humidity		= $arrObservation['relative_humidity'];
			$this->wind			= $arrObservation['wind_kph'].' km/h - '.$arrObservation['wind_dir'];
		}
		private static function setData_image($getStrIcon = false) 
		{
			if (! $getStrIcon) return false;
			$arrTypes = array(
				'thunder'		=> array('tstorms'),
				'rain'			=> array('rain'),
				'snow'			=> array('chanceflurries','flurries','chancesleet','sleet','chancesnow','snow'),
				'fog'			=> array('fog'),
				'cloudy'		=> array('cloudy'),
				'mostly-cloudy'	=> array('mostlycloudy'),
				'partly-cloudy'	=> array('partlycloudy'), 
				'clear-ish'		=> array('chancerain','chancetstorms'),
				'clear-sunny'	=> array('clear','mostlysunny','partlysunny'),
				'sunny'			=> array('hazy','sunny')
			);
			foreach ($arrTypes as $strImage => $arrType) {
				if (in_arraY($getStrIcon, $arrType)) return $strImage.'.png';
			}
			return 'default.png';
		}
	};
?>