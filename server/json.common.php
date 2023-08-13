<?php
header('Content-Type: application/json; charset=utf-8', true);


function buildError($err) {
  $e = array('error'=>array('message'=>"$err"));
  return $e;
}

?>