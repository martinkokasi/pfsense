<?php
use OAuth\OAuth2\Service\Facebook;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
function authenticate($key, $secret) {
  require_once __DIR__ . '/../bootstrap.php';
  $servicesCredentials = array('key' => '',
                               'secret' => '');
  // Session storage
  $storage = new Session();
  $serviceFactory = new \OAuth\ServiceFactory();
  $uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
  $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
  $currentUri->setQuery('');
  // Setup the credentials for the requests
  $credentials = new Credentials($key, $secret, $currentUri->getAbsoluteUri());
  // Instantiate the Facebook service using the credentials, http client and storage mechanism for the token
  /** @var $facebookService Facebook */
  $facebookService = $serviceFactory->createService('facebook', $credentials,
                                                  $storage, array('email', 'user_about'));
  if (preg_match('/code=([^&]+)/', $_GET['redirurl'], $results)) {
    $token = $results[1];
  }
  if ($token) {
    // This was a callback request from facebook, get the token
    $facebookService->requestAccessToken($token);
    // Send a request with it
    if (json_decode($facebookService->request('https://graph.facebook.com/v2.3/oauth/access_token?'), true)) {
      return $token;
    }
  } else {
    $url = $facebookService->getAuthorizationUri();
    header('Location: ' . $url);
  }
}
