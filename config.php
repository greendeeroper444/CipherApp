<?php
	require('./vendor/autoload.php');


	$clientId = "Your client id";
	$clientSecret = "Your client secret";
	$redirectUri = 'http://localhost/CipherApp/login.php';

	$client = new Google\Client();
	$client->setClientId($clientId);
	$client->setClientSecret($clientSecret);
	$client->setRedirectUri($redirectUri);
	$client->addScope("email");
	$client->addScope("profile");