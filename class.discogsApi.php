<?php 

class discogsApi {
	private $endpoint = 'http://api.discogs.com/search?q=';
	private $search_term;

 public function __construct($session) { 
 }
 
 public function discogs_search($search = NULL, $session) {
	if (isset($search)) {
		//remove illegal words and format for discogs
		$illegal = array('180','gram','vinyl', 'sealed', 'lp','signed','numbered','new');
		$search = str_ireplace($illegal,'',$search);
		$search = str_replace(' ','+',$search);
		//build uri for discogs api
		$url = $this->endpoint . $search;
		//get listing from discogs
		$xml = $session->getDiscogsListing($url);
		//ensure that we have a result then fetch our fields from it
		if ($xml->searchresults->result->uri) {
			print($xml->searchresults->result->uri);
			$pieces = explode('/',$xml->searchresults->result->uri);	
			$master = $pieces[5];
			if (isset($master)) {
				$uri = 'http://api.discogs.com/release/' . $master;
		  		$discogs_info = $this->discogs_fetch_fields($uri, $session);
		  		return $discogs_info;
			}
		}
	 }
  }
  
/*
 * Fetches relevent data from discogs listing
 */
 
  public function discogs_fetch_fields($uri = NULL, $session) {
	$discogs['discogsUri'] = $uri;
	if (isset($uri)) {
		$xml = $session->getDiscogsListing($uri);
		$discogs['discogsId'] = (string) $xml->release->attributes();
		if ($xml->xpath('//release/images/image[@type="primary"]') && is_array($xml->xpath('//release/images/image[@type="primary"]'))) {
			$discogs['imageUri'] = (string) current($xml->xpath('//release/images/image[@type="primary"]/@uri'));
		} else if ($xml->xpath('//release/images/image[@type="primary"]/@uri')) {
			$discogs['imageUri'] = (string) $xml->xpath('//release/images/image[@type="primary"]/@uri');
		}
		foreach ($xml->release->genres->children() as $genre) {
			$discogs['genres'][] = (string) $genre;
		}
		if ($xml->release->styles) {
			foreach ($xml->release->styles->children() as $style) {
				$discogs['genres'][] = (string) $style;
			}
		}
		foreach ($xml->release->artists->children() as $artist) {
			$discogs['artists'][] = (string) $artist->name;	
		}
		foreach ($xml->release->labels->children() as $label) {
			$discogs['labels'][] = (string) $label->attributes()->name;	
		}
		foreach ($xml->release->tracklist->children() as $track) {
			$discogs['tracklist'][(string) $track->position] = (string) $track->title;	
		}		
		foreach ($xml->release->extraartists->children() as $xartist) {
			$discogs['xartists'][(string) $xartist->name] = (string) $xartist->role;
		}	
		return $discogs;
	}
  } 
}