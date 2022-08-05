<?php

namespace App;

use App\Model\AbstractSharedModel;
use App\Model\Component;
use App\Model\Incident;
use App\Model\Schedule;
use PDO;
use RuntimeException;

class DbConnector
{
  private $db;

  public function __construct(string $dbDir)
  {
    $this->db = new PDO(sprintf('sqlite:%s%s%s', $dbDir, DIRECTORY_SEPARATOR, $_ENV['DB_FILE']));

    // Create tables if not yet created
    $this->db->query(<<<SQL
CREATE TABLE IF NOT EXISTS components (
      id INTEGER PRIMARY KEY NOT NULL,
      name TEXT,
      status INTEGER,
      status_name TEXT,
      created_at TEXT,
      updated_at TEXT,
      data TEXT
) WITHOUT ROWID
SQL
    );
    $this->db->query(<<<SQL
CREATE TABLE IF NOT EXISTS incidents (
      id INTEGER PRIMARY KEY NOT NULL,
      name TEXT,
      latest_status INTEGER,
      latest_human_status TEXT,
      permalink TEXT,
      created_at TEXT,
      updated_at TEXT,
      data TEXT
) WITHOUT ROWID
SQL
    );
    $this->db->query(<<<SQL
CREATE TABLE IF NOT EXISTS schedules (
      id INTEGER PRIMARY KEY NOT NULL,
      name TEXT,
      human_status TEXT,
      created_at TEXT,
      updated_at TEXT,
      data TEXT
) WITHOUT ROWID
SQL
    );
  }

  public function getComponent(int $id): ?Component
  {
    $stmt = $this->db->prepare('SELECT data FROM components WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if (!$data = $stmt->fetch(PDO::FETCH_ASSOC)) {
      return NULL;
    }

    return new Component(json_decode($data['data'], true));
  }

  public function getIncident(int $id): ?Incident
  {
    $stmt = $this->db->prepare('SELECT data FROM incidents WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if (!$data = $stmt->fetch(PDO::FETCH_ASSOC)) {
      return NULL;
    }

    return new Incident(json_decode($data['data'], true));
  }

  public function getSchedule(int $id): ?Schedule
  {
    $stmt = $this->db->prepare('SELECT data FROM schedules WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if (!$data = $stmt->fetch(PDO::FETCH_ASSOC)) {
      return NULL;
    }

    return new Schedule(json_decode($data['data'], true));
  }

  public function storeComponent(Component $component): void
  {
    $stmt = $this->db->prepare(<<<SQL
INSERT INTO components (id,
                        name,
                        status,
                        status_name,
                        created_at,
                        updated_at,
                        data)
VALUES (:id,
        :name,
        :status,
        :status_name,
        :created_at,
        :updated_at,
        :data)
SQL
    );

    if (!$stmt->execute($this->componentToParameters($component))) {
      throw new RuntimeException(json_encode($stmt->errorInfo()));
    }
  }

  public function storeIncident(Incident $incident): void
  {
    $stmt = $this->db->prepare(<<<SQL
INSERT INTO incidents (id,
                       name,
                       latest_status,
                       latest_human_status,
                       permalink, 
                       created_at,
                       updated_at,
                       data)
VALUES (:id,
        :name,
        :latest_status,
        :latest_human_status,
        :permalink,
        :created_at,
        :updated_at,
        :data)
SQL
    );

    if (!$stmt->execute($this->incidentToParameters($incident))) {
      throw new RuntimeException(json_encode($stmt->errorInfo()));
    }
  }

  public function storeSchedule(Schedule $schedule): void
  {
    $stmt = $this->db->prepare(<<<SQL
INSERT INTO schedules (id,
                       name,
                       human_status,
                       created_at,
                       updated_at,
                       data)
VALUES (:id,
        :name,
        :human_status,
        :created_at,
        :updated_at,
        :data)
SQL
    );

    if (!$stmt->execute($this->scheduleToParameters($schedule))) {
      throw new RuntimeException(json_encode($stmt->errorInfo()));
    }
  }

  public function updateComponent(Component $component): void {
    $stmt = $this->db->prepare(<<<SQL
UPDATE components
SET name = :name,
    status = :status,
    status_name = :status_name,
    created_at = :created_at,
    updated_at = :updated_at,
    data = :data
WHERE id = :id
SQL
    );
    $stmt->execute($this->componentToParameters($component));
  }

  public function updateIncident(Incident $incident): void
  {
    $stmt = $this->db->prepare(<<<SQL
UPDATE incidents
SET name = :name,
    latest_status = :latest_status,
    latest_human_status = :latest_human_status,
    permalink = :permalink, 
    created_at = :created_at,
    updated_at = :updated_at,
    data = :data
WHERE id = :id
SQL
    );
    $stmt->execute($this->incidentToParameters($incident));
  }

  public function updateSchedule(Schedule $schedule): void
  {
    $stmt = $this->db->prepare(<<<SQL
UPDATE schedules
SET name = :name,
    human_status = :human_status,
    created_at = :created_at,
    updated_at = :updated_at,
    data = :data
WHERE id = :id
SQL
    );
    $stmt->execute($this->scheduleToParameters($schedule));
  }

  private function componentToParameters(Component $component): array
  {
    return $this->sharedModelToParameters($component, [
        'id',
        'name',
        'status',
        'status_name',
        'created_at',
        'updated_at',
    ]);
  }

  private function incidentToParameters(Incident $incident): array
  {
    return $this->sharedModelToParameters($incident, [
        'id',
        'name',
        'latest_status',
        'latest_human_status',
        'permalink',
        'created_at',
        'updated_at',
    ]);
  }

  private function scheduleToParameters(Schedule $schedule): array
  {
    return $this->sharedModelToParameters($schedule, [
        'id',
        'name',
        'human_status',
        'created_at',
        'updated_at',
    ]);
  }

  private function sharedModelToParameters(AbstractSharedModel $sharedModel, array $fields): array
  {
    $result = [];
    foreach ($sharedModel->getData() as $key => $value) {
      if (!in_array($key, $fields)) {
        continue;
      }

      $result[':' . $key] = $value;
    }

    $result[':data'] = json_encode($sharedModel->getData());

    return $result;
  }
}
