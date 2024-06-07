<?php
//carlos Rolan
namespace Controllers;

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../utils/MailerFactory.php';
require_once __DIR__ . '/../repositories/UsersRepository.php';
require_once __DIR__ . '/../repositories/SMTPConfigRepository.php';
require_once __DIR__ . '/../repositories/CampaingRepository.php';
require_once __DIR__ . '/../controllers/LogController.php';
require_once __DIR__ . '/../repositories/TiempoEsperaRepository.php';
require_once __DIR__ . '/../repositories/SeccionRepository.php';
require_once __DIR__ . '/../services/HTTPRequestService.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/UserApi.php';

use Services\HTTPRequestService;
use Repositories\SeccionRepository;
use Repositories\UsersRepository;
use Repositories\CampaingRepository;
use Repositories\TiempoEsperaRepository;

use Controllers\BaseController;
use Controllers\LogController;

use Utils\MailerFactory;

use Models\UserApi;

use Exception;
use RecursiveDirectoryIterator;
use DirectoryIterator;
use RecursiveIteratorIterator;

class MailerController extends BaseController
{

  private static $ACTION_SEND_EMAIL = "SEND_EMAIL";
  private static $ACTION_SEND_EMAIL_ALL_USERS = "SEND_EMAIL_ALL_USERS";
  private static $ACTION_SEND_EMAIL_ALL_API_USERS = "SEND_EMAIL_ALL_API_USERS";
  private static $ACTION_SEND_EMAIL_WAITING_TIME_REPORTS = "SEND_EMAIL_WAITING_TIME_REPORTS";

  /**
   * @return boolean Si es TRUE se envia un email de notificación de que no hay campañas o que las campañas no tienen publicidad PARA HOY
   */
  private function checkCampaings()
  {
    $allCampaings = CampaingRepository::getAllCampaings();

    $numOfCamps = count($allCampaings);
    if ($numOfCamps == 0) {
      echo "There are NO campaings\n";
      return true;
    }
    foreach ($allCampaings as $campaing) {

      echo $campaing->Nombre . ":\n";

      if ($campaing->isInDate()) {
        echo "Campaing is IN DATE\n";
        if ($campaing->hasPlaylist()) {
          echo "Campaing has playlist\n";
          return false;
        } else {
          echo "Campaing has NO playlist\n";
        }
      } else {
        echo "Campaing is OUT in date\n";
      }
    }

    echo "No campaings OUT OF DATE or with NO playlist\n";
    return true;

  }


  /**
   * @param UserApi $userApi con este usuario recogemos los datos del token de la tabla de usuarios_token
   * @return int Devuelve un entero indicando el estado del Token api del usuario
   */
  private function checkToken($userApi)
  {
    echo "Cheking token for user " . $userApi->getUserApi() . " ...\n";
    $tokenApi = UsersRepository::getTokenForUser($userApi->getId());

    if ($tokenApi->isTokenExpired()) {
      echo "The token for the user has expired\n";
      return 0;
    } else {
      echo "The token is NOT EXPIRED\n";
    }

    if ($tokenApi->isTokenCloseToExpire()) {
      echo "The token for the user is close to expire (less than 15 days)\n";
      return 1;
    } else {
      echo "The token is NOT CLOSE to expire (15 days)\n";
    }
    return -1;
  }

  private function generateTempExcelFiles()
  {
    //Se envia un informo de tiempo de espera por cada seccion a cada usuariop
    $sections = SeccionRepository::getAllSections();

    foreach ($sections as $section) {

      $seccion = $section->getCode();
      $horas = "F";
      $fecha_inicio = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d'))));
      $fecha_fin = date('Y-m-d');
      $domingo = "T";
      $nombreSeccion = $section->getName();
      $arrayDatos = TiempoEsperaRepository::getReportData($domingo, $fecha_inicio, $fecha_fin, $seccion);

      $postData = [
        "seccion" => $seccion,
        "arrayDatos" => json_encode($arrayDatos),
        "horas" => $horas,
        "fecha_inicio" => $fecha_inicio,
        "fecha_fin" => $fecha_fin,
        "domingo" => $domingo,
        "nombreSeccion" => $nombreSeccion,
        //IMPORTANTE PARA QUE NOS GUARDE LOS EXCEL EN UNA CARPETA TEMP
        "tempDir" => true
      ];

      $url = "http://localhost/gmedia/generarArchivoEspera.php";
      //este post se encarga de generar el archivo de excel y guardarlo en un a carpeta temporal
      $fileToAttacht = HTTPRequestService::makePOSTRequest($url, $postData);
      echo "Saving excelFile in the temporal dir\n";
    }

  }

  private function deleteTempDir($directory)
  // Function to recursively delete a directory and its contents
  {
    if (!is_dir($directory)) {
      throw new Exception("Directory does not exist: $directory");
    }

    $dir = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $fileinfo) {
      if ($fileinfo->isDir()) {
        rmdir($fileinfo->getRealPath());
      } else {
        unlink($fileinfo->getRealPath());
      }
    }
    echo "Removing EXCEL TEMP dir\n";
    rmdir($directory);
  }

  private function getAllFilesInTempDir($directory)
  {
    $files = [];

    // Ensure the directory exists
    if (!is_dir($directory)) {
      throw new Exception("Directory does not exist: $directory");
    }

    // Use DirectoryIterator to iterate over files in the directory
    try {
      $dir = new DirectoryIterator($directory);
      foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot() && $fileinfo->isFile()) {
          $files[] = $fileinfo->getPathname();
        }
      }
    } catch (Exception $e) {
      throw new Exception("Error reading directory: " . $e->getMessage());
    }

    return $files;
  }

  private function sendEmail($emailType, $userName, $userEmail)
  {
    $mail = MailerFactory::create($emailType, $userEmail);
    //OJO Si el email es de tipo informe de espera adjunto todos los archivos excel a cada email
    if ($emailType == MailerFactory::$EMAIL_REPORTS_WAITING_TIME) {
      $allExcelFiles = self::getAllFilesInTempDir(TEMP_EXCEL_DIR);
      foreach ($allExcelFiles as $excel) {
        $mail->addAttachment($excel);
      }
    }
    try {
      echo "Sending mail: " . $userName . " - " . $userEmail;
      $mail->send();
      $mail->smtpClose();
      LogController::saveAction("Enviando correo al usuario: " . $userName . " - " . $userEmail);
    } catch (Exception $e) {
      var_dump($e);
      echo "Exception from MailerController on sending email to USER";
    }
  }

  /**
   * Constructor de la clase.
   * Puede ser utilizado para inicializar variables si es necesario.
   */
  public function __construct()
  {

  }

  //AARON !! - ESTE ES EL METODO QUE TIENES QUE USAR EN PHP
  public function sendNoPubliNotifications()
  {
    if ($this->checkCampaings()) {
      echo "TRYING TO SEND NO_PUBLI_NOTIFICATION to ALL...\n";
      $allUsers = UsersRepository::getAllUsers();
      foreach ($allUsers as $user) {
        echo "user:\n";
        var_dump($user);
        if ($user->hasCorreo()) {
          if ($user->isNotificationActive()) {
            $this->sendEmail(MailerFactory::$EMAIL_NO_PUBLI_TODAY, $user->getNombre(), $user->getCorreo());
          } else {
            echo "The user has NO active NOTIFICATIONS\n";
          }
        } else {
          echo "The user has NO email\n";
        }
      }
    } else {
      echo "NOT sending emails\n";
    }
  }

  //AARON !! -ESTE ES EL METODO QUE TIENES QUE USAR EN PHP 
  public function sendTokenNotifications()
  {
    echo "TRYING TO SEND TOKEN_NOTIFICATION to ALL API ..\n";
    $allApiUsers = UsersRepository::getAllUsersApi();
    foreach ($allApiUsers as $apiUser) {
      echo "api_user:\n";
      var_dump($apiUser);
      if ($apiUser->hasEmail()) {
        if ($apiUser->hasAPiKey()) {
          echo "The api_user has API key\n";

          $tokenCheckResult = $this->checkToken($apiUser);

          var_dump($tokenCheckResult);

          switch ($tokenCheckResult) {
            //Token ha expirado
            case 0:
              $this->sendEmail(MailerFactory::$EMAIL_TOKEN_EXPIRED, $apiUser->getUserApi(), $apiUser->getEmail());
              break;

            //Token le quedan menos de 15 dias para expirar
            case 1:
              $this->sendEmail(MailerFactory::$EMAIL_TOKEN_CLOSE_TO_EXPIRE, $apiUser->getUserApi(), $apiUser->getEmail());
              break;

            //el token es valido y todavia le queda tiempo
            case -1:
              break;

            default:
              break;
          }

        } else {
          echo "The api_user is not type API key\n";
        }
      } else {
        echo "The api_user has NO email\n";
      }
    }
  }

  //AARON !! -ESTE ES EL METODO QUE TIENES QUE USAR EN PHP
  public function sendWaitingTimeReports()
  {
    $this->generateTempExcelFiles();
    $allUsers = UsersRepository::getAllUsers();
    foreach ($allUsers as $user) {
      if ($user->hasCorreo()) {
        if ($user->isNotificationActive()) {
          $this->sendEmail(MailerFactory::$EMAIL_REPORTS_WAITING_TIME, $user->getNombre(), $user->getCorreo());
        } else {
          echo "The user has NO active NOTIFICATIONS\n";
        }
      } else {
        echo "The user has NO email\n";
      }
    }
    $this->deleteTempDir(TEMP_EXCEL_DIR);
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

    switch ($action) {
      case self::$ACTION_SEND_EMAIL:
        $this->sendResponse(true, "ENDPOINT NOT IMPLEMENTED");
        break;

      case self::$ACTION_SEND_EMAIL_ALL_USERS:
        $this->sendNoPubliNotifications();
        //No se envia una response para poder debuguear el flujo de la logica del envio de emails
        break;

      case self::$ACTION_SEND_EMAIL_ALL_API_USERS:
        $this->sendTokenNotifications();
        //No se envia una response para poder debuguear el flujo de la logica del envio de emails
        break;

      case self::$ACTION_SEND_EMAIL_WAITING_TIME_REPORTS:
        $this->sendWaitingTimeReports();
        //No se envia una response para poder debuguear el flujo de la logica del envio de emails
        break;

      default:
        break;
    }

  }
}
$mailerController = new MailerController();
$mailerController->handlePostRequest();