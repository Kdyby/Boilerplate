<?php

final class __GoogleAnalyticsData extends GoogleAnalytics
{

	const SPLIT_KEY = ':split_key';

	private $profiles;
	protected $actualProfile;

	private $startIndex = 1;
	private $maxResults = 50;
	private $steps = array();

	private $request;
	private $response;

	private $resultSet = array();

	private $counter = 0;
	private $maxLoops = 3;


	/**
	* Performs the curl calls to the $url specified.
	*
	* @param string $url
	* @param array $data - specify the data to be 'POST'ed to $url
	* @param array $header - specify any header information
	* @return $response from submission to $url
	*/
	public function _postTo($url, $data = array(), $header = array())
	{
		$this->request = new Curl();
		$this->request->setHeaders($header);
// 		$this->request->addProxy('192.168.1.160', 3128);

		if( count($data) > 0 ){
			$response = $this->request->post($url,$data);

		} else {
			$this->request->setHeader(Null, "application/x-www-form-urlencoded");
			$response = $this->request->get($url);
		}

		$this->response = $response;

		return $response->getBody();

	}


	public function getLastRequest()
	{
		return $this->request;
	}


	public function getLastResponse()
	{
		return $this->response;
	}


	public function getMaxResults()
	{
		return $this->maxResults;
	}


	public function getProfiles()
	{
		if( empty($this->profiles) ){
			$this->profiles = $this->getWebsiteProfiles();
		}

		return $this->profiles;
	}


	public function activateProfile($profile)
	{
		$this->actualProfile = $profile;
		$this->setProfile('ga:'.$profile['profileId']);
	}


	public function params($parameters)
	{
		if( !is_array($parameters) ){
			$parameters = func_get_args();
		}

		return urlencode(implode(',', $parameters));
	}


	public function getReport($properities = array(), $steps = False)
	{
		$this->resultSet = array();

		if( !$steps ){
			$properities['start-index'] = $this->startIndex;
			$properities['max-results'] = $this->maxResults;

		} else {
			if( empty($this->steps) ){
				$this->steps['start-index'] = $this->startIndex;
				$this->steps['max-results'] = $this->maxResults;
			}

			$properities = array_merge($properities, $this->steps);
		}

		do {
			$this->counter += 1;
			$results = parent::getReport($properities);

			$headers = $this->getLastResponse()->getHeaders();
			if( (int)$headers['Status-Code'] !== 200 ){
				if( $this->counter <= $this->maxLoops ){
					return $this->getReport($properities, $steps);

				} else {
					throw new GoogleAnalyticsException("Error occured during data download!");
				}

			}

			$this->counter = 0;

			if( !$steps ){
				$this->resultSet = array_merge($this->resultSet, $results);
				$properities['start-index'] += $this->maxResults;

			} else {
				if( count($results) < $this->maxResults ){
					$this->steps = array();

				} else {
					$this->steps['start-index'] += $this->maxResults;
				}

				return $results;
			}

		} while( !$steps AND count($results) == $this->maxResults );

		return $this->resultSet;
	}

	public function formatData(&$data, $format=Null, $dimensions = array() )
	{
		$results = array();

		switch($format){
			case GoogleAnalyticsData::SPLIT_KEY:
				foreach( $data AS $key => $row ){
					$dimensions_data = explode("~~", $key);

					foreach( $row AS $key => $value ){
						$newRow[str_replace('ga:', 'met_', $key)] = $value;
					}

					$row = $newRow;

					if( !empty($dimensions) ){
						foreach( $dimensions AS $i => $column ){
							if( isset($dimensions_data[$i]) ){
								$row[$column] = $dimensions_data[$i];

							} else {
								break;
							}
						}

					} else {
						foreach( $dimensions_data AS $value ){
							$row[] = $value;
						}
					}

					foreach( $row AS $key => $value ){
						$newRow[str_replace('ga:', 'dim_', $key)] = $value;
					}

					$results[] = $newRow;
				}
				break;
			default:
				return $data;
				break;
		}

		return $results;
	}

	public function getAdWordsCosts($steps = False)
	{
		$dimensions = array(
			'ga:keyword',
			'ga:adGroup',
			'ga:source',
			'ga:medium'
		);

		$metrics = array(
			'ga:adClicks',
			'ga:adCost',
			'ga:CPC',
			'ga:CPM',
			'ga:CTR',
			'ga:impressions'
		);

		$report = $this->getReport(array(
			'dimensions' => $this->params($dimensions),
			'metrics' => $this->params($metrics),
			'sort' => 'ga:medium'
			), $steps
		);

		return $this->formatData($report, GoogleAnalyticsData::SPLIT_KEY, $dimensions);
	}


	public function getProfilePreview($steps = False)
	{
		$dimensions = array(
			'ga:source',
			'ga:keyword',
			'ga:adContent',
			'ga:campaign',
			'ga:adGroup',
			'ga:referralPath',
			'ga:medium'
		);

		$metrics = array(
			'ga:visits',
			'ga:itemRevenue',
			'ga:itemQuantity',
			'ga:transactionRevenue',
			'ga:transactions',
			'ga:transactionShipping',
			'ga:transactionTax',
			'ga:uniquePurchases'
		);

		$report = $this->getReport(array(
			'dimensions' => $this->params($dimensions),
			'metrics' => $this->params($metrics),
			'sort' => 'ga:medium'
			), $steps
		);

		return $this->formatData($report, GoogleAnalyticsData::SPLIT_KEY, $dimensions);
	}

	public function getBrowsers($steps = False)
	{
		// what browsers visitors to your using?
		return $this->getReport(array(
			'dimensions' => $this->params('ga:browser'),
			'metrics' => $this->params('ga:visits'),
			'sort' => '-ga:visits'
			), $steps
		);
	}

	public function getTopTimeOnPages($steps = False)
	{
		// which are your top landing pages and how long they spent on the page?
		return $this->getReport(array(
			'dimensions' => $this->params('ga:landingPagePath','ga:pageTitle'),
			'metrics' => $this->params('ga:entrances','ga:timeOnPage'),
			'sort' => '-ga:entrances'
			), $steps
		);
	}

	public function getSearchKeywords($steps = False)
	{
		// which are your top internal search keywords by pageviews?
		return $this->getReport(array(
			'dimensions' => $this->params('ga:searchKeyword'),
			'metrics' => $this->params('ga:pageview'),
			'sort' => '-ga:pageviews'
			), $steps
		);
	}

	public function getTest($steps = False)
	{
		return $this->getReport(array(
			'dimensions' => $this->params("ga:source","ga:keyword"),
			'metrics' => $this->params(
				"ga:visits",
				"ga:adClicks",
				"ga:adCost",
				"ga:itemRevenue",
				"ga:itemQuantity",
				"ga:transactions",
				"ga:entrances",
				"ga:exits",
				"ga:newVisits",
				"ga:pageviews"),
			'sort' => "-ga:visits"
			), $steps
		);
	}

}


