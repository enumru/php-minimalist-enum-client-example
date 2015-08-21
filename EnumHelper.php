<?php

class EnumHelper
{
	const BASE_URL = 'https://auth.enum.ru';
	const AUTH_URL = self::BASE_URL . '/OAuth/Authorize';
	const TOKEN_URL = self::BASE_URL . '/OAuth/Token';
	const INFO_URL = self::BASE_URL . '/Api/Me';

	private $client_id;
	private $client_secret;
	private $redirect_uri;

	public function __construct($client_id, $client_secret, $redirect_uri)
	{
		if (empty($client_id)) throw new Exception("client_id is required");
		if (empty($client_id)) throw new Exception("client_secret is required");
		if (empty($client_id)) throw new Exception("redirect_uri is required");

		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->redirect_uri = $redirect_uri;
	}

	public function getAuthorizationCode($state)
	{
		$params = array(
			'response_type' => 'code',
			'client_id' => $this->client_id,
			'redirect_uri' => $this->redirect_uri,
			'scope' => 'email phone',
			'state' => $state,
		);
		$params = http_build_query($params, null, '&', PHP_QUERY_RFC3986);
		//die($params);

		$url = self::AUTH_URL . '?' . $params;
		header('Location: ' . $url);
	}

	public function getAccessToken($authorizationCode)
	{
		$params = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'authorization_code',
			'code' => $authorizationCode,
			'redirect_uri' => $this->redirect_uri,
		);
		$params = http_build_query($params);
		//die($params);

		$curl = curl_init();
		if ($curl === FALSE) throw new Exception('curl_init error');
		$options = array(
		    CURLOPT_URL => self::TOKEN_URL,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_POST => true,
		    CURLOPT_POSTFIELDS => $params,
		);
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		if ($response === FALSE) throw new Exception(curl_error($curl), curl_errno($curl));
		curl_close($curl);

		$response = json_decode($response, TRUE);
		if (isset($response['error'])) throw new Exception('Error on getting token: ' . $response['error']);
		if (!isset($response['access_token'])) throw new Exception('Error on getting token: no access token');
		return $response['access_token'];
	}

	public function getUserInfo($accessToken)
	{
		$curl = curl_init();
		if ($curl === FALSE) throw new Exception('curl_init error');
		$options = array(
		    CURLOPT_URL => self::INFO_URL,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $accessToken),
		);
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		if ($response === FALSE) throw new Exception(curl_error($curl), curl_errno($curl));
		curl_close($curl);

		$response = json_decode($response, TRUE);
		return $response;
	}
}
