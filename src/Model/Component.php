<?php

namespace App\Model;

use RuntimeException;

class Component extends AbstractSharedModel
{
  public function getMessage(): string
  {
    throw new RuntimeException('Message does not exists on a Component');
  }

  public function getStatus(): int
  {
    return static::$accessor->getValue($this->data, '[status]');
  }

  public function getStatusName(): string
  {
    return static::$accessor->getValue($this->data, '[status_name]');
  }

  public function getLink(): ?string
  {
    return static::$accessor->getValue($this->data, '[link]');
  }

  public function getGroupId(): int
  {
    return static::$accessor->getValue($this->data, '[group_id]');
  }
}
