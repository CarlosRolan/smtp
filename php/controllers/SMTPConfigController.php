<?php
//Carlos Rolan
namespace Controllers;

require_once __DIR__ . '/../repositories/SMTPConfigRepository.php';
require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../controllers/LogController.php';

use Controllers\BaseController;
use Controllers\LogController;
use Repositories\SMTPConfigRepository;

class SMTPConfigController extends BaseController
{
  private static $ACTION_GET_SMTP_CONFIG = "GET_SMTP_CONFIG";
  private static $ACTION_SAVE_CONFIG = "SAVE_CONFIG";
  private static $ACTION_INIT_SMTP = "INIT_SMTP";
  private function updateSessionVarSMTP()
  {
    $config = SMTPConfigRepository::getSMTPConfig();
    session_start();

    if (isset($_SESSION["smtp_config"])) {
      if ($_SESSION["smtp_config"] == $config) {
        echo "SIN CAMBIOS EN EL SMTPConfig\n";
      } else {
        echo "Variable de SESSION ACUALIZADA\n";
        $_SESSION["smtp_config"] = $config;
      }
    } else {
      echo "Var de SESSION INICIALIZADA\n";
      $_SESSION["smtp_config"] = $config;
    }

    //var_dump($_SESSION["smtp_config"]);
  }
  /**
   * Constructor de la clase.
   * Puede ser utilizado para inicializar variables si es necesario.
   */
  public function __construct()
  {
    // Constructor
  }

  public function handlePostRequest()
  {
    // Obtiene la data del request
    $data = $this->getRequestData();
    //Ver el formato en el que llegan los datos
    //var_dump($data);
    // Obtiene y verifica la acción de la solicitud
    $action = $data['action'] ? $data['action'] : null;

    if (is_null($action)) {
      $this->sendResponse(false, "No action specified");
      return;
    }

    // Verifica que la solicitud sea de tipo POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->sendResponse(false, "ERROR is not a POST request " . $action);
      return;
    }

    // Ejecuta la acción correspondiente según la solicitud
    switch ($action) {

      case self::$ACTION_INIT_SMTP:
        $this->updateSessionVarSMTP();
        break;

      case self::$ACTION_GET_SMTP_CONFIG:
        $response = SMTPConfigRepository::getSMTPConfig();
        $this->sendResponse(true, $response);
        break;

      case self::$ACTION_SAVE_CONFIG:
        $smtpConfig = $data["smtpConfig"];
        $host = $smtpConfig["host"];
        $port = $smtpConfig["port"];
        $username = $smtpConfig["username"];
        $encrypted_pass = base64_encode($smtpConfig["pass"]);
        $encryp = $smtpConfig["encryp"];
        $auth_method = $smtpConfig["auth_method"];
        $response = SMTPConfigRepository::updateConfig($host, $port, $username, $encrypted_pass, $encryp, $auth_method);
        if ($response) {
          //Nuevo log cuando se cambia la configuracion SMTP
          LogController::saveAction("Nueva configuración SMTP guardada");
          $this->sendResponse(true, "Configuración SMTP actualizada");
        } else {
          $this->sendResponse(false, "Sin cambios en la configuración SMTP");
        }
        break;

      default:
        break;
    }
  }

}

$smtpConfigController = new SMTPConfigController();
$smtpConfigController->handlePostRequest();



