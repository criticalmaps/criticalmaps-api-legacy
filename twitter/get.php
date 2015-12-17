<?php
ini_set('display_errors', 1);

require_once("../credentials.php");
require_once('TwitterAPIExchange.php');

$settings = array(
    'oauth_access_token' => TWITTER_OAUTH_ACCESS_TOKEN,
    'oauth_access_token_secret' => TWITTER_OAUTH_ACCESS_TOKEN_SECRET,
    'consumer_key' => TWITTER_CONSUMER_KEY,
    'consumer_secret' => TWITTER_CONSUMER_SECRET
);

$url = 'https://api.twitter.com/1.1/search/tweets.json';
$getfield = '?q=criticalmaps';
$requestMethod = 'GET';

$twitter = new TwitterAPIExchange($settings);
echo $twitter->setGetfield($getfield)
             ->buildOauth($url, $requestMethod)
             ->performRequest();