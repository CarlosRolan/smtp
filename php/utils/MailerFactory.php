<?php
//Carlos Rolan
namespace Utils;

// Show PHP errors (Disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include library PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '/opt/lampp/htdocs/gmedia/librerias/PHPMailer/src/Exception.php';
require '/opt/lampp/htdocs/gmedia/librerias/PHPMailer/src/PHPMailer.php';
require '/opt/lampp/htdocs/gmedia/librerias/PHPMailer/src/SMTP.php';

require_once __DIR__ . "/../repositories/DatosEmpresaRepository.php";

use Repositories\DatosEmpresaRepository;
use DateTime;

class MailerFactory
{

    //FACTORY VARS
    public static $EMAIL_TOKEN_EXPIRED = "TOKEN_EXPIRED";
    public static $EMAIL_TOKEN_CLOSE_TO_EXPIRE = "TOKEN_CLOSE_TO_EXPIRE";
    public static $EMAIL_NO_PUBLI_TODAY = "NO_PUBLI_TODAY";
    public static $EMAIL_REPORTS_WAITING_TIME = "REPORTS_WAITING_TIME";

    private static $COMPANY_NAME = "Gmedia";
    private static $LEGAL_INFORMATION = "
    <tbody>
        <tr>
            <td valign='top' style='padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;color:#606060;font-family:Helvetica;font-size:11px;line-height:125%;text-align:left'>
                <p>CONFIDENCIALIDAD: Este mensaje y los ficheros anexos son confidenciales, especialmente en lo que respecta a los datos personales y se dirigen exclusivamente al destinatario referenciado. 
                Si usted no lo es y lo ha recibido por error o tiene conocimiento del mismo por cualquier motivo, le rogamos que nos lo comunique por este medio y proceda a destruirlo o borrarlo, y que en todo caso se abstenga de utilizar, reproducir, alterar, archivar o comunicar a terceros el presente mensaje y ficheros anexos, todo ello bajo pena de incurrir en responsabilidades legales. 
                Si el mensaje va dirigido a clientes, las opiniones o recomendaciones contenidas en el mensaje se entienden sujetas a los t&eacute;rminos y condiciones de contrato de servicio.
                PROTECCI&Oacute;N DE DATOS PERSONALES: En virtud del art. 5 de la Ley Org&aacute;nica 15/1999, le informamos que sus datos personales han sido incorporados a un fichero creado por ASYSGON, SL con la finalidad del mantenimiento de la relaci&oacute;n comercial. Puede ejercitar los derechos de acceso, rectificaci&oacute;n, cancelaci&oacute;n y oposici&oacute;n por escrito ante ASYSGON, SL, con domicilio en Calle C, Nave D10 Parque Tecnol&oacute;gico y Log&iacute;stico de Vigo 36315 Vigo (PTL &Aacute;rea-TexVigo). 
                Asimismo autoriza a que se d&eacute; traslado de sus datos a terceros cuando sea necesario para el cumplimiento de la prestaci&oacute;n de servicios.</p>
                <em>Este correo es autom&aacute;tico y no necesita contestaci&oacute;n</em>.
            </td>
        </tr>
    </tbody>
    ";

    private static function getEmailBodyNoPubli()
    {
        $dateTime = new DateTime();

        $dateString = $dateTime->format('d-m-Y');
        return "
        <body>
            <p>Estimado usuario,</p>
            <p>Queríamos informarle que <b>NO SE HA PROGRAMADO</b> campañas de publicidad para hoy en su cuenta de GMEDIA.</p>
            
            Avisamos de que su cuenta tiene ninguna campaña programada para el día de hoy, <b>" . $dateString . "</b> .
            Si tiene alguna pregunta o inquietud, no dude en ponerse en contacto con nuestro equipo de soporte a través de [dirección de correo electrónico o número de teléfono de soporte].
            
            <p>Atentamente,</p>
            <p>" . self::$COMPANY_NAME . "</p>
            " . self::$LEGAL_INFORMATION . "
            </body>
            </html>
        ";
    }

    private static function getEmailBodyTokenExpired()
    {
        $dateTime = new DateTime();

        $dateString = $dateTime->format('d-m-Y');
        return "
        <body>
            <p>Estimado usuario,</p>
        
            <p>Nos dirigimos a usted para informarle que el token de uso asociado a su cuenta ha caducado. Esto significa que su acceso actual al sistema ha expirado y requerirá una actualización.</p>
        
            <p>Por favor, proceda a generar un nuevo token de uso para continuar utilizando nuestros servicios. Puede hacerlo iniciando sesión en su cuenta y accediendo a la sección de configuración de tokens.</p>
        
            <p>Si necesita asistencia adicional o tiene alguna pregunta, no dude en ponerse en contacto con nuestro equipo de soporte.</p>
        
            <p>Atentamente,<br>
            <p>" . self::$COMPANY_NAME . "</p>
            " . self::$LEGAL_INFORMATION . "
        </body>
        ";
    }

    private static function getEmailBodyTokenCloseToExpire()
    {
        $dateTime = new DateTime();

        $dateString = $dateTime->format('d-m-Y');
        return "
        <body>
        <p>Estimado usuario,</p>
    
        <p>Queríamos informarle que el token de uso asociado a su cuenta está <b>próximo a caducar</b> (menos de 15 días). Le recomendamos que genere un nuevo token lo antes posible para evitar interrupciones en su acceso al sistema.</p>
    
        <p>Puede generar un nuevo token iniciando sesión en su cuenta y accediendo a la sección de configuración de tokens.</p>
    
        <p>Si necesita ayuda para generar un nuevo token o tiene alguna pregunta, nuestro equipo de soporte está aquí para ayudarlo.</p>
    
        <p>Atentamente,<br>
        <p>" . self::$COMPANY_NAME . "</p>
        " . self::$LEGAL_INFORMATION . "
        </body>
    ";
    }

    private static function getEmailBodyWaitingTimeReport()
    {
        return "
        <body>
        <p>Estimado usuario,</p>

        <p>Esto es un email autogenerado de informes de <b>TIEMPO DE ESPERA</b> de TODAS las SECCIONES</p>
    
        <p>Atentamente,<br>
        <p>" . self::$COMPANY_NAME . "</p>
        " . self::$LEGAL_INFORMATION . "
        </body>
    ";
    }

    private static function createTokenCloseToExpireEmail($config, $receptorEmail, $companyName)
    {
        //SMTP server pass
        $server_host = $config->getHost();
        $server_port = $config->getPort();
        //$server_name = 'Asysgio';
        $server_mail = $config->getUsername();
        $server_pass = $config->getRealPass();
        $server_encryp = $config->getEncryp();

        $mail = new PHPMailer(true);
        try {
            //=========Configuration SMTP=============//
            // Show output (Disable in production)
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            // Activate SMTP sending
            $mail->isSMTP();
            // SMTP Server
            $mail->Host = $server_host;
            // SMTP Identification
            $mail->SMTPAuth = true;
            $mail->CharSet = 'UTF-8';
            // SMTP User
            $mail->Username = $server_mail;
            // SMTP Password
            $mail->Password = $server_pass;
            $mail->SMTPSecure = $server_encryp;
            $mail->Port = $server_port;
            // Mail sender
            $mail->setFrom("no-reply@asysgon.com", 'GMEDIA-' . $companyName);

            // Recipients
            $mail->addAddress($receptorEmail, "TITULO");
            /* 
             * IMPORTANT (SOLO si no se añade address antes)
             * Es necesario tener un addres como recipient, porque la libreia PHPMailer lo quiere asi, 
             * Luego se elimina y se añade el addres cuando se vaya a enviar el email, pero si añadimos un address
             * como mínimo, no nos deja contruir un mailer
             * */
            //$mailer->addAddress('default@example.com', 'Default Recipient');

            // IMPORTANT!
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Mail content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'TOKEN caducará pronto';
            $mail->Body = MailerFactory::getEmailBodyTokenCloseToExpire();
            $mail->AltBody = 'TOKEN caducará pronto';
            //============================================//

        } catch (Exception $e) {
            echo "Could NOT CREATE instance of type [TOKEN_CLOSE_TO_EXPIRE].Mailer Error: {$mail->ErrorInfo}\n";
        }
        return $mail;
    }

    private static function createTokenExpired($config, $receptorEmail, $companyName)
    {
        //SMTP server pass
        $server_host = $config->getHost();
        $server_port = $config->getPort();
        //$server_name = 'Asysgio';
        $server_mail = $config->getUsername();
        $server_pass = $config->getRealPass();
        $server_encryp = $config->getEncryp();

        $mail = new PHPMailer(true);
        try {
            //=========Configuration SMTP=============//
            // Show output (Disable in production)
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            // Activate SMTP sending
            $mail->isSMTP();
            // SMTP Server
            $mail->Host = $server_host;
            // SMTP Identification
            $mail->SMTPAuth = true;
            $mail->CharSet = 'UTF-8';
            // SMTP User
            $mail->Username = $server_mail;
            // SMTP Password
            $mail->Password = $server_pass;
            $mail->SMTPSecure = $server_encryp;
            $mail->Port = $server_port;
            // Mail sender
            $mail->setFrom("no-reply@asysgon.com", 'GMEDIA-' . $companyName);

            // Recipients
            $mail->addAddress($receptorEmail, "TITULO");
            /* 
             * IMPORTANT (SOLO si no se añade address antes)
             * Es necesario tener un addres como recipient, porque la libreia PHPMailer lo quiere asi, 
             * Luego se elimina y se añade el addres cuando se vaya a enviar el email, pero si añadimos un address
             * como mínimo, no nos deja contruir un mailer
             * */
            //$mailer->addAddress('default@example.com', 'Default Recipient');

            // IMPORTANT!
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Mail content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'TOKEN CADUCADO';
            $mail->Body = MailerFactory::getEmailBodyTokenExpired();
            $mail->AltBody = 'TOKEN CADUCADO';
            //============================================//

        } catch (Exception $e) {
            echo "Could NOT create instance of type [TOKEN_EXPIRED].Mailer Error: {$mail->ErrorInfo}\n";
        }
        return $mail;
    }

    private static function createWaitingTimeReportEmail($config, $receptorEmail, $companyName)
    {
        //SMTP server pass
        $server_host = $config->getHost();
        $server_port = $config->getPort();
        //$server_name = 'Asysgio';
        $server_mail = $config->getUsername();
        $server_pass = $config->getRealPass();
        $server_encryp = $config->getEncryp();

        $mail = new PHPMailer(true);
        try {
            //=========Configuration SMTP=============//
            // Show output (Disable in production)
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            // Activate SMTP sending
            $mail->isSMTP();
            // SMTP Server
            $mail->Host = $server_host;
            // SMTP Identification
            $mail->SMTPAuth = true;
            $mail->CharSet = 'UTF-8';
            // SMTP User
            $mail->Username = $server_mail;
            // SMTP Password
            $mail->Password = $server_pass;
            $mail->SMTPSecure = $server_encryp;
            $mail->Port = $server_port;
            // Mail sender
            $mail->setFrom("no-reply@asysgon.com", 'GMEDIA-' . $companyName);

            // Recipients
            $mail->addAddress($receptorEmail, "TITULO");

            //Se añade en MailerController.php todos los archivos 
            //$mail->addStringAttachment($excelOutput, 'tiempo_espera.xls');

            /* 
             * IMPORTANT (SOLO si no se añade address antes)
             * Es necesario tener un addres como recipient, porque la libreia PHPMailer lo quiere asi, 
             * Luego se elimina y se añade el addres cuando se vaya a enviar el email, pero si añadimos un address
             * como mínimo, no nos deja contruir un mailer
             * */
            //$mailer->addAddress('default@example.com', 'Default Recipient');

            // IMPORTANT!
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Mail content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Informe de tiempo de espera';
            $mail->Body = MailerFactory::getEmailBodyWaitingTimeReport();
            $mail->AltBody = 'Informe de tiempo de espera';
            //============================================//

        } catch (Exception $e) {
            echo "Could NOT create instance of type [REPORTS_WAITING_TIME].Mailer Error: {$mail->ErrorInfo}";
        }
        return $mail;
    }

    private static function createNoPubliTodayEmail($config, $username, $companyName)
    {
        //SMTP server pass
        $server_host = $config->getHost();
        $server_port = $config->getPort();
        //$server_name = 'Asysgio';
        $server_mail = $config->getUsername();
        $server_pass = $config->getRealPass();
        $server_encryp = $config->getEncryp();

        $mail = new PHPMailer(true);
        try {
            //=========Configuration SMTP=============//
            // Show output (Disable in production)
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            // Activate SMTP sending
            $mail->isSMTP();
            // SMTP Server
            $mail->Host = $server_host;
            // SMTP Identification
            $mail->SMTPAuth = true;
            $mail->CharSet = 'UTF-8';
            // SMTP User
            $mail->Username = $server_mail;
            // SMTP Password
            $mail->Password = $server_pass;
            $mail->SMTPSecure = $server_encryp;
            $mail->Port = $server_port;
            // Mail sender
            $mail->setFrom("no-reply@asysgon.com", 'GMEDIA-' . $companyName);

            // Recipients
            $mail->addAddress($username, "TITULO");
            /* 
             * IMPORTANT (SOLO si no se añade address antes)
             * Es necesario tener un addres como recipient, porque la libreia PHPMailer lo quiere asi, 
             * Luego se elimina y se añade el addres cuando se vaya a enviar el email, pero si añadimos un address
             * como mínimo, no nos deja contruir un mailer
             * */
            //$mailer->addAddress('default@example.com', 'Default Recipient');

            // IMPORTANT!
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Mail content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'SIN PUBLICIDAD PARA HOY';
            $mail->Body = MailerFactory::getEmailBodyNoPubli();
            $mail->AltBody = 'SIN PUBLICIDAD PARA HOY';
            //============================================//

        } catch (Exception $e) {
            echo "Could NOT create instance of type [NO_PUBLI_TODAY].Mailer Error: {$mail->ErrorInfo}";
        }
        return $mail;
    }

    //PUBLIC METHODS
    public static function create($EMAIL_TYPE, $receptorEmail)
    {
        session_start();
        $config = $_SESSION["smtp_config"];

        $companyData = DatosEmpresaRepository::getDatosEmpresa();

        $companyName = self::$COMPANY_NAME;

        if ($companyData) {
            $companyName = $companyData->getNombreComercial();
        }

        switch ($EMAIL_TYPE) {

            case self::$EMAIL_NO_PUBLI_TODAY:
                return self::createNoPubliTodayEmail($config, $receptorEmail, $companyName);

            case self::$EMAIL_TOKEN_EXPIRED:
                return self::createTokenExpired($config, $receptorEmail, $companyName);

            case self::$EMAIL_TOKEN_CLOSE_TO_EXPIRE:
                return self::createTokenCloseToExpireEmail($config, $receptorEmail, $companyName);

            case self::$EMAIL_REPORTS_WAITING_TIME:
                return self::createWaitingTimeReportEmail($config, $receptorEmail, $companyName);

            default:
                # code...
                break;
        }

    }

    public static function newInstance($config, $username, $subject, $body, $altBody)
    {
        $nombreComercial = DatosEmpresaRepository::getDatosEmpresa()->getNombreComercial();
        if (!isset($nombreComercial)) {
            $nombreComercial = self::$COMPANY_NAME;
        }
        //SMTP server pass
        $server_host = $config->getHost();
        $server_port = $config->getPort();
        //$server_name = 'Asysgio';
        $server_mail = $config->getUsername();
        $server_pass = $config->getRealPass();
        $server_encryp = $config->getEncryp();

        $mail = new PHPMailer(true);
        try {
            //=========Configuration SMTP=============//
            // Show output (Disable in production)
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            // Activate SMTP sending
            $mail->isSMTP();
            // SMTP Server
            $mail->Host = $server_host;
            // SMTP Identification
            $mail->SMTPAuth = true;
            $mail->CharSet = 'UTF-8';
            // SMTP User
            $mail->Username = $server_mail;
            // SMTP Password
            $mail->Password = $server_pass;
            $mail->SMTPSecure = $server_encryp;
            $mail->Port = $server_port;
            // Mail sender
            $mail->setFrom("no-reply@asysgon.com", 'GMEDIA-' . $nombreComercial);

            // Recipients
            $mail->addAddress($username, "TITULO");
            /* 
             * IMPORTANT (SOLO si no se añade address antes)
             * Es necesario tener un addres como recipient, porque la libreia PHPMailer lo quiere asi, 
             * Luego se elimina y se añade el addres cuando se vaya a enviar el email, pero si añadimos un address
             * como mínimo, no nos deja contruir un mailer
             * */
            //$mailer->addAddress('default@example.com', 'Default Recipient');

            // IMPORTANT!
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Mail content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody;
            //============================================//

        } catch (Exception $e) {
            echo "Could NOT CREATE instance.Mailer Error: {$mail->ErrorInfo}";
        }
        return $mail;
    }
}







