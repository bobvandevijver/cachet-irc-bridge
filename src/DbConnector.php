<?php

namespace App;

use App\Model\Incident;
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
CREATE TABLE IF NOT EXISTS incidents (
      id INTEGER PRIMARY KEY NOT NULL,
      name TEXT,
      latest_status INTEGER,
      latest_human_status TEXT,
      permalink TEXT,
      created_at TEXT,
      updated_at TEXT
) WITHOUT ROWID 
SQL
    );
  }

  public function getIncident(int $id): ?Incident
  {
    $stmt = $this->db->prepare('SELECT * FROM incidents WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if (!$data = $stmt->fetch(PDO::FETCH_ASSOC)) {
      return NULL;
    }

    return new Incident($data);
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
                       updated_at) 
VALUES (:id,
        :name,
        :latest_status,
        :latest_human_status,
        :permalink,
        :created_at,
        :updated_at)
SQL
    );

    if (!$stmt->execute($this->incidentToParameters($incident))) {
      throw new RuntimeException(json_encode($stmt->errorInfo()));
    }
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
    updated_at = :updated_at
WHERE id = :id
SQL
    );
    $stmt->execute($this->incidentToParameters($incident));
  }

  private function incidentToParameters(Incident $incident): array
  {
    $fields = [
        'id',
        'name',
        'latest_status',
        'latest_human_status',
        'permalink',
        'created_at',
        'updated_at'];
    $result = [];
    foreach ($incident->getData() as $key => $value) {
      if (!in_array($key, $fields)) {
        continue;
      }

      $result[':' . $key] = $value;
    }

    return $result;
  }
}
