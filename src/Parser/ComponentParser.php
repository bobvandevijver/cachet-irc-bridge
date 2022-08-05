<?php

namespace App\Parser;

use App\Model\Component;

class ComponentParser extends AbstractSharedParser
{
  private $groupNameCache = [];

  protected function getEndpoint(): string
  {
    return $_ENV['API_COMPONENT_ENDPOINT'];
  }

  protected function parseObject(array $apiData): void
  {
    $apiComponent = new Component($apiData);
    $dbComponent  = $this->db->getComponent($apiComponent->getId());

    if (!$dbComponent) {
      $this->console->text(sprintf('New component! [%s] %s',
          $apiComponent->getStatusName(), $apiComponent->getName()));
      $this->irc->updateComponent($apiComponent, $this->getComponentGroup($apiComponent));

      $this->db->storeComponent($apiComponent);
    } else {
      /** @noinspection PhpNonStrictObjectEqualityInspection */
      if ($dbComponent->getUpdatedAt() == $apiComponent->getUpdatedAt()
          || $dbComponent->getStatus() === $apiComponent->getStatus()) {
        // No updates
        return;
      }

      $this->console->text(sprintf('Updated component! [%s] %s',
          $apiComponent->getStatusName(), $apiComponent->getName()));
      $this->irc->updateComponent($apiComponent, $this->getComponentGroup($apiComponent));

      $this->db->updateComponent($apiComponent);
    }
  }

  private function getComponentGroup(Component $component): ?string
  {
    if (!$groupId = $component->getGroupId()) {
      return NULL;
    }

    if (array_key_exists($groupId, $this->groupNameCache)) {
      return $this->groupNameCache[$groupId];
    }

    $this->console->text(sprintf('    Retrieving group information! [%d]', $groupId));

    $response = $this->httpClient->request('GET', $_ENV['FRONTEND_HOST'] . $_ENV['API_COMPONENT_GROUP_ENDPOINT'] . '/' . $groupId);
    if ($response->getStatusCode() !== 200) {
      $this->console->error([
          'HTTP request failed for:',
          $this->getEndpoint(),
          'The error message was:',
          json_encode($response->getInfo()),
      ]);

      return NULL;
    }

    return $this->groupNameCache[$groupId] = $this->accessor->getValue($response->toArray(), '[data][name]');
  }
}
