<?php

namespace App\Parser;

use App\Model\Incident;

class IncidentParser extends AbstractSharedParser
{
  protected function getEndpoint(): string
  {
    return $_ENV['API_STATUS_ENDPOINT'];
  }

  protected function parseObject(array $apiData): void
  {
    $apiIncident = new Incident($apiData);
    $dbIncident  = $this->db->getIncident($apiIncident->getId());

    if (!$dbIncident) {
      $this->console->text(sprintf('New incident! [%s] %s',
          $apiIncident->getLatestHumanStatus(), $apiIncident->getName()));
      $this->irc->newIncident($apiIncident);

      $this->db->storeIncident($apiIncident);
    } else {
      /** @noinspection PhpNonStrictObjectEqualityInspection */
      if ($dbIncident->getUpdatedAt() == $apiIncident->getUpdatedAt()) {
        // No updates
        return;
      }

      $this->console->text(sprintf('Updated incident! [%s] %s',
          $apiIncident->getLatestHumanStatus(), $apiIncident->getName()));
      $this->irc->updateIncident($apiIncident);

      $this->db->updateIncident($apiIncident);
    }
  }
}
