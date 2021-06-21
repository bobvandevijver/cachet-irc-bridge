<?php

namespace App\Model;

use DateTimeImmutable;

class Incident extends AbstractSharedModel
{
  public function getLatestHumanStatus(): string
  {
    return static::$accessor->getValue($this->data, '[latest_human_status]');
  }

  public function getLatestStatus(): int
  {
    return static::$accessor->getValue($this->data, '[latest_status]');
  }

  public function getPermaLink(): string
  {
    return static::$accessor->getValue($this->data, '[permalink]');
  }

  public function getUpdatedAt(): ?DateTimeImmutable
  {
    $lastUpdate = parent::getUpdatedAt();
    foreach ($this->getUpdates() as $update) {
      if ($update->getUpdatedAt() > $lastUpdate) {
        $lastUpdate = $update->getUpdatedAt();
      }
    }

    return $lastUpdate;
  }


  /**
   * @return IncidentUpdate[]
   */
  public function getUpdates(): array
  {
    return array_map(function (array $updateData) {
      return new IncidentUpdate($updateData);
    }, static::$accessor->getValue($this->data, '[updates]'));
  }
}
