<?php

namespace App\Model;

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
}
