<?php

namespace Repositories;

require_once __DIR__ . "/../models/Database/Database.php";

use Models\Database\Database;

class BaseRepository
{
 private static $DB_INSTANCE;

 public static function getDBInstance()
 {
  if (!isset(self::$DB_INSTANCE)) {
   self::$DB_INSTANCE = Database::getConnection();
  }
  return self::$DB_INSTANCE;
 }

}