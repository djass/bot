<?php 
ini_set('display_errors', 1);
//set POST variables
//$kw = $_POST['k'];
function get_info($keyword){
	$query = urlencode('windows.microsoft.com '.$keyword);  
	$url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=".$query;

	$body = file_get_contents($url);
	$json = json_decode($body);
	return $json->responseData->results[0]->url;
}
echo str_replace('en-us','fr-fr',get_info("ecran+noir"));