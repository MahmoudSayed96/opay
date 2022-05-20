<?php

namespace Drupal\opay\Client;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Datetime\Time;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * Uses http client to interact with the OPay API.
 */
class OPayApiClient {

  /**
   * The Immutable Config Object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Psr\Log\LoggerInterface definition.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Drupal\Component\Datetime\Time definition.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * OPay Token Key.
   *
   * @var string
   */
  protected $token;

  /**
   * OPay Merchant ID.
   *
   * @var string
   */
  protected $merchantId;

  /**
   * OPay base API Uri.
   *
   * @var string
   */
  protected $apiUri;

  /**
   * Constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Client interface.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory interface.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger interface.
   * @param \Drupal\Component\Datetime\Time $time
   *   Time.
   */
  public function __construct(
    ClientInterface $http_client,
    ConfigFactoryInterface $config_factory,
    LoggerInterface $logger,
    Time $time
  ) {
    $this->httpClient = $http_client;
    $this->logger = $logger;
    $this->time = $time;
    $this->config = $config_factory->get('opay.settings');
    $this->token = $this->getKeyValue('token');
    $this->merchantId = $this->getKeyValue('merchant_id');
    $this->apiUri = $this->config->get('api_uri');
  }

  /**
   * Utilizes Drupal's httpClient to connect to the OPay API.
   *
   * Info: https://doc.opaycheckout.com.
   *
   * @param string $method
   *   get, post, patch, delete, etc. See Guzzle documentation.
   * @param string $endpoint
   *   The OPay API endpoint (ex. create)
   * @param array $query
   *   Any Query Parameters defined in the API spec.
   * @param array $body
   *   Array that will get converted to JSON for some requests.
   *
   * @return mixed
   *   RequestException or \GuzzleHttp\Psr7\Response body
   */
  public function request(string $method, string $endpoint, array $query = [], array $body = []) {
    try {
        $response = $this->httpClient->{$method}(
        $this->apiUri . $endpoint,
        $this->buildOptions($query, $body)
      );

      // @todo Add additional response options.
      $payload = Json::decode($response->getBody()->getContents());
      return $payload;
    }
    catch (RequestException $exception) {
      // Log Any exceptions.
      $this->logger->error('Failed to complete OPay API Task "%error"', ['%error' => $exception->getMessage()]);
      throw $exception;
    }
  }

  /**
   * Build options for the client.
   *
   * @param array $query
   *   An array of querystring params for guzzle.
   * @param array $body
   *   An array of items that guzzle with json_encode.
   *
   * @return array
   *   An array of options for guzzle.
   */
  private function buildOptions(array $query = [], array $body = []) {
    $options = [];
    $options['headers'] = $this->setAuthHeader();

    if (!empty($body)) {
      // Json key converts array to json & adds appropriate content-type header.
      $options['json'] = $body;
    }
    if (!empty($query)) {
      $options['query'] = $query;
    }
    return $options;
  }

  /**
   * Return auth header.
   *
   * @return array
   */
  protected function setAuthHeader() {
    return [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer ' . $this->token,
      'MerchantId' => $this->merchantId,
    ];
  }

  /**
   * Used to validate Configuration.
   *
   * This could be more thorough.
   *
   * @return bool
   *   TRUE or FALSE.
   */
  public function validateConfiguration() {
    $props = [
      'token',
      'merchant_id',
      'api_uri',
    ];

    foreach ($props as $prop) {
      if (empty($this->{$prop})) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Return a KeyValue.
   *
   * @param string $whichConfig
   *   Name of the config in which the key name is stored.
   *
   * @return mixed
   *   Null or string.
   */
  protected function getKeyValue($whichConfig) {
    if (empty($this->config->get($whichConfig))) {
      return NULL;
    }
    $keyValue = $this->config->get($whichConfig);
    if (empty($keyValue)) {
      return NULL;
    }

    return $keyValue;
  }

}
