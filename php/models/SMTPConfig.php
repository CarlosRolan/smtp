<?php

namespace Models;

//Carlos Rolan
use JsonSerializable;

class SMTPConfig implements JsonSerializable
{

 private $id;

 private $host;

 private $port;

 private $username;

 private $pass;

 private $encryp;

 private $auth_method;


 public function getRealPass()
 {
  return base64_decode($this->pass);
 }
 public function getHost()
 {
  return $this->host;
 }
 public function getPort()
 {
  return $this->port;
 }
 public function getUsername()
 {
  return $this->username;
 }
 public function getPass()
 {
  return $this->pass;
 }
 public function getEncryp()
 {
  return $this->encryp;
 }
 public function getAuthMethod()
 {
  return $this->auth_method;
 }
 public function setHost($host)
 {
  $this->host = $host;
 }
 public function setPort($port)
 {
  $this->port = $port;
 }
 public function setUsername($username)
 {
  $this->username = $username;
 }
 public function setPass($pass)
 {
  $this->pass = $pass;
 }
 public function setEncryp($encryp)
 {
  $this->encryp = $encryp;
 }
 public function setAuthMethod($auth_method)
 {
  $this->auth_method = $auth_method;
 }

 // Implementación del método jsonSerialize
 public function jsonSerialize()
 {
  return [
   'id' => $this->id,
   'host' => $this->host,
   'port' => $this->port,
   'username' => $this->username,
   'pass' => $this->getRealPass(),
   'encryp' => $this->encryp,
   'auth_method' => $this->auth_method
  ];
 }

}