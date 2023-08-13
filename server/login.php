<?php
require __DIR__ . '/vendor/autoload.php';
include_once 'connect.php';

use ReallySimpleJWT\Token;




function getSecret() {
  $secret = "This&sA3adTh£ngToDo";
  return $secret;
}

function signToken($user) {
  $expiration = time() + 3600 * 48;
  $issuer = 'skattjakt';
  $secret = getSecret();
  return Token::create($user['id'], $secret, $expiration, $issuer);
}

function login($cmd) {
  if (!isset($cmd['password']))
    throw new Exception('Missing password');
  if (!isset($cmd['login']))
    throw new Exception('Missing login');

  $login = $cmd['login'];
  $field = strstr($login, '@') ? 'email' : 'userName';
  $users = fetchAssoc("SELECT id, password FROM Users WHERE $field=?", [$login]);

  if (count($users) != 1)
    throw new Exception('User not found');

  if (password_verify($cmd['password'], $users[0]['password'],))
    return ['success'=>true,'token'=>signToken($users[0])];

  throw new Exception('Wrong password');
}

function verifyLogin( ) {
  foreach($_SERVER as $key => $value) {
    if ($key == 'HTTP_AUTHORIZATION') {
      if (Token::validate($value, getSecret()))
        return true;
    }
  }
  throw new Exception('You are not logged in');
}

?>