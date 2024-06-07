<?php

namespace Controllers;

class BaseController
{

    public function __construct()
    {
    }

    public function sendSessionKey($key) {
        $result = $this->getSession($key);
        $this->sendResponse(true, $result);
    }

    public function getSession($clave)
    {
        //if(session_status() !== PHP_SESSION_ACTIVE) session_start();
        if(session_status() === PHP_SESSION_NONE) session_start();

        // Verificar si un usuario está autenticado
        if (isset($_SESSION[$clave])) {

            return  $_SESSION[$clave];
        } else {
            return false;
        }
    }

    public function sendResponse($status, $result)
    {
        header('Content-Type: application/json');
        if ($status) {
            echo json_encode(array(
                "status" => "success",
                "data" => $result
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "error" => $result
            ));
        }
        exit;
    }
    public function getRequestData()
    {
        $encodedData = file_get_contents('php://input');
        return json_decode($encodedData, true);
    }

    public function methodNotFound() {
        echo json_encode(array(
            "status" => "method not defined"
        ));
        exit;
    }
}
