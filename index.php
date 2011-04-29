<?php
	//For the Internet Explorer accept cookies and session in the iframe.
	header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	
	//Include Facebook SDK (modified lines 434 n 435 from github.com/facebook/php-sdk)
	require_once('class/facebook.php');
	
	//App info
	$facebook_app_id = '' ;//Your Facebook APP ID
	$facebook_app_secret = ''; //Your Facebook APP Secret
	$facebook_canvas_page = ''; //Your Facebook canvas page (eg: http://apps.facebook.com/myappname)
		
	//Init Facebook
	$facebook = new Facebook(array(
		'appId'  => $facebook_app_id,
		'secret' => $facebook_app_secret,
		'cookie' => true
	));
	
	//Facebook signed request function (more info: http://developers.facebook.com/docs/authentication/signed_request)
	function parse_signed_request($signed_request, $secret)
	{
		list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
		
		// decode the data
		$sig = base64_url_decode($encoded_sig);
		$data = json_decode(base64_url_decode($payload), true);
		
		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
		{
			error_log('Unknown algorithm. Expected HMAC-SHA256');
			return null;
		}
	
		// check sig
		$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
		if($sig !== $expected_sig)
		{
			error_log('Bad Signed JSON signature!');
			return null;
		}
	
		return $data;
	}
	
	function base64_url_decode($input)
	{
		return base64_decode(strtr($input, '-_', '+/'));
	}
	
	
	//Set facebook session
	$facebook_session = parse_signed_request($_POST['signed_request'], $facebook_app_secret);
	
	//Set facebook permissions (more: http://developers.facebook.com/docs/authentication/permissions)
	$facebook_permissions = 'user_about_me,publish_stream';

	//Set login URL
	$facebook_login_url = $facebook->getLoginUrl(array('req_perms' => $facebook_permissions), $facebook_canvas_page)
	
	//Checks if the user is authenticated. If not, go to the login.
	if(!isset($facebook_session['oauth_token']))
	{
		echo '<script type="text/javascript">';
		echo '	this.top.location.href="' . $facebook_login_url . '";';
		echo '</script>';
	}
	else
	{
		//User data
		$me = $facebook->api("/me");
		
		//Print
		print_r($me);
	}
	
?>