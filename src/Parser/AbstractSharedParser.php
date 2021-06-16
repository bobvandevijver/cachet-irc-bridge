<?php

namespace App\Parser;

use App\DbConnector;
use App\IrcConnector;
use App\Model\AbstractSharedModel;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractSharedParser
{
  /** @var PropertyAccessorInterface */
  protected $accessor;
  /** @var SymfonyStyle */
  protected $console;
  /** @var DbConnector */
  protected $db;
  /** @var HttpClientInterface */
  protected $httpClient;
  /**  @var IrcConnector */
  protected $irc;

  public function __construct(
      SymfonyStyle $console, DbConnector $db, IrcConnector $irc, HttpClientInterface $httpClient,
      PropertyAccessorInterface $accessor)
  {
    $this->console    = $console;
    $this->db         = $db;
    $this->irc        = $irc;
    $this->accessor   = $accessor;
    $this->httpClient = $httpClient;
  }

  /**
   * @throws ClientExceptionInterface
   * @throws DecodingExceptionInterface
   * @throws RedirectionExceptionInterface
   * @throws ServerExceptionInterface
   * @throws TransportExceptionInterface
   */
  public function __invoke(): int
  {
    $response = $this->httpClient->request('GET', $_ENV['FRONTEND_HOST'] . $this->getEndpoint());
    if ($response->getStatusCode() !== 200) {
      $this->console->error([
          'HTTP request failed for:',
          $this->getEndpoint(),
          'The error message was:',
          json_encode($response->getInfo()),
      ]);

      return 1;
    }

    foreach ($this->accessor->getValue($response->toArray(), '[data]') as $apiData) {
      $this->parseObject($apiData);
    }

    return 0;
  }

  protected abstract function getEndpoint(): string;

  protected abstract function parseObject(array $apiData): void;
}
