<?php

namespace rapidweb\googlecontacts\helpers;

abstract class GoogleHelper
{
    private static function loadConfig()
    {
       
		global $conf,$fk_user_gcs,$PDOdb;
		
  	        $config = new \stdClass;
		$config->clientID = $conf->global->GCS_GOOGLE_CLIENT_ID;
		$config->clientSecret = $conf->global->GCS_GOOGLE_CLIENT_SECRET;
		$config->redirectUri = dol_buildpath('/googlecontactsync/php-google-contacts-v3-api/redirect-handler.php',2);
		$config->developerKey = !empty($conf->global->GCS_GOOGLE_DEVELOPER_KEY) ? $conf->global->GCS_GOOGLE_DEVELOPER_KEY : '';
		
		if(!defined('GCS_NO_TOKEN')) {

			$PDOdb=new \TPDOdb;
			
			if(!empty($_SESSION['GCS_fk_user']))$fk_user_gcs=$_SESSION['GCS_fk_user'];
			
			$token = \TGCSToken::getTokenFor($PDOdb, $fk_user_gcs, 'user');
			$config->token = $token->token;
			$config->refresh_token = $token->refresh_token;
			
			
		}
		else{
			$config->token = '';
			$config->refresh_token = '';
		}
//var_dump($config);exit;
        return $config;
    }

    public static function getClient()
    {
        $config = self::loadConfig();

        $client = new \Google_Client();
        
        $client->setApplicationName('Rapid Web Google Contacts API');

        $client->setScopes(array(/*
        'https://apps-apis.google.com/a/feeds/groups/',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://apps-apis.google.com/a/feeds/alias/',
        'https://apps-apis.google.com/a/feeds/user/',*/
        'https://www.google.com/m8/feeds/',
        /*'https://www.google.com/m8/feeds/user/',*/
        ));

        $client->setClientId($config->clientID);
        $client->setClientSecret($config->clientSecret);
        $client->setRedirectUri($config->redirectUri);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setDeveloperKey($config->developerKey);
        
        if (isset($config->refresh_token) && $config->refresh_token) {
           	$client->refreshToken($config->refresh_token);
           
        }

        return $client;
    }

    public static function getAuthUrl(\Google_Client $client)
    {
        return $client->createAuthUrl();
    }

    public static function authenticate(\Google_Client $client, $code)
    {
        $client->authenticate($code);
    }

    public static function getAccessToken(\Google_Client $client)
    {
        return json_decode($client->getAccessToken());
    }
}
