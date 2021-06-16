<?php

namespace App\Parser;

use App\Model\Schedule;

class ScheduleParser extends AbstractSharedParser
{
  protected function getEndpoint(): string
  {
    return $_ENV['API_SCHEDULE_ENDPOINT'];
  }

  protected function parseObject(array $apiData): void
  {
    $apiSchedule = new Schedule($apiData);
    $dbSchedule  = $this->db->getSchedule($apiSchedule->getId());

    if (!$dbSchedule) {
      $this->console->text(sprintf('New schedule! [%s] %s',
          $apiSchedule->getHumanStatus(), $apiSchedule->getName()));
      $this->irc->newSchedule($apiSchedule);

      $this->db->storeSchedule($apiSchedule);
    } else {
      /** @noinspection PhpNonStrictObjectEqualityInspection */
      if ($dbSchedule->getUpdatedAt() == $apiSchedule->getUpdatedAt()) {
        // No updates
        return;
      }

      $this->console->text(sprintf('Updated schedule! [%s] %s',
          $apiSchedule->getHumanStatus(), $apiSchedule->getName()));
      $this->irc->updateSchedule($apiSchedule);

      $this->db->updateSchedule($apiSchedule);
    }
  }
}
