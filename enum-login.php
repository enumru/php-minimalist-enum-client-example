<pre>
<?php
session_start();

require('EnumHelper.php');

$client_id = Put here your client_id '';
$client_secret = Put here your client_secret '';
$redirect_uri = Put here your redirect_uri (https) '';

$enum = new EnumHelper($client_id, $client_secret, $redirect_uri);

if (!isset($_GET['code'], $_GET['state'], $_SESSION['state'])) {
	
	// Step 1. Authorization code request

	$state = 'abc' . mt_rand(100, 999);
	$_SESSION['state'] = $state;
	$enum->getAuthorizationCode($state);	

}
else {

	// Step 2. Authorization code response

	$state = $_GET['state'];
	$code = $_GET['code'];

	if ($state != $_SESSION['state']) die('Step 2. Authorization code response - state error!');
	//die('Step 2. Authorization code response -authorization code = ' . $code);


	// Step 3, 4. Access token
	$access_token = $enum->getAccessToken($code);

	//echo "\n\n\n";
	//echo "access_token:\n";
	//var_dump($access_token);



	// Step 5. Getting the user identification details

	$user_info = $enum->getUserInfo($access_token);

	echo "\n\n\n";
	echo "user_info:\n";
	var_dump($user_info);

}
