<?php
namespace App\Models;

use PDO;

abstract class BaseRepository {
    protected PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
}
