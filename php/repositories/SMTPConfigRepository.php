<?php
//Carlos Rolan
namespace Repositories;

require_once __DIR__ . '/../models/SMTPConfig.php';
require_once __DIR__ . "/BaseRepository.php";

use Repositories\BaseRepository;
use Models\SMTPConfig;
use PDO;
use PDOException;

class SMTPConfigRepository extends BaseRepository
{

 public static function updateConfig($host, $port, $username, $encrypted_pass, $encryp, $auth_method)
 {
  try {
   self::getDBInstance()->beginTransaction();
   $stmt = self::getDBInstance()->prepare('UPDATE smtp_config SET  host = :host, port = :port, username = :username, pass = :pass, encryp = :encryp, auth_method = :auth_method WHERE id=1');

   //$stmt->bindParam(':familyName', $familyName, PDO::PARAM_STR);
   $stmt->bindParam(':host', $host, PDO::PARAM_STR);
   $stmt->bindParam(':port', $port, PDO::PARAM_INT);
   $stmt->bindParam(':username', $username, PDO::PARAM_STR);
   $stmt->bindParam(':pass', $encrypted_pass, PDO::PARAM_STR);
   $stmt->bindParam(':encryp', $encryp, PDO::PARAM_STR);
   $stmt->bindParam(':auth_method', $auth_method, PDO::PARAM_STR);

   $result = $stmt->execute();

   if ($result && $stmt->rowCount() > 0) {
    // Éxito
    self::getDBInstance()->commit();
    return true;
   } else {
    // La inserción falló o no se insertaron filas
    self::getDBInstance()->rollBack();
    // echo "Error en la inserción o ninguna fila insertada.";
    // echo "Puede ser porque no hay filas insertadas porque los valores son los mismos";
    return false;
   }
  } catch (PDOException $e) {
   var_dump($e);
   return false;
  }
 }

 public static function getSMTPConfig()
 {
  // hay que añadir el día, para que solo saque los del día actual
  $stmt = self::getDBInstance()->prepare("SELECT * from smtp_config");
  $stmt->execute();

  $stmt->setFetchMode(PDO::FETCH_CLASS, SMTPConfig::class);
  $result = $stmt->fetch();

  return $result;
 }

}