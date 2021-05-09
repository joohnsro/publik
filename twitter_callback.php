<?php
require "vendor/autoload.php";

error_reporting(E_ALL);
ini_set("display_errors","On");

use Abraham\TwitterOAuth\TwitterOAuth;
use Publik\Services\TwitterService;

$url = "./publik.json";

$publikSettingsJSON = file_get_contents($url);
$publikSettings = json_decode($publikSettingsJSON, true);

$access_token = TwitterService::setOAuthToken( $publikSettings['twitter']['consumer_key'], $publikSettings['twitter']['consumer_secret'] );

$publikSettings['twitter']['oauth_token'] = $access_token['oauth_token'];
$publikSettings['twitter']['oauth_token_secret'] = $access_token['oauth_token_secret'];

file_put_contents( $url, json_encode($publikSettings) );

header("Location: http://localhost:8000/wp-admin/admin.php?page=publik_settings");
exit;