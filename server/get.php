<?php
/**
 * Get from database
 * api:
 *   get.php
 *    fetch=tablename[& fields | where | orderBy | limit]
 *
 *
 *   fields:
 *     fields=field1,field2...
 *
 *   where:
 *     where=field1;op;value
 *      op: = | != | like | not like | in | not in ...
 *      value: num | 'str' | "str"
 *        value when op=in: value can be repeated by ; separations
 *
 *    ie: where=field1;=;1 and field2;in;1;2;3 or field3;=;4
 *      and -> higher priority than or
 *
 *   orderBy:
 *     orderBy=field1 [ DESC | ASC]; field2 [ DESC | ASC ] ....
 *
 *   limit:
 *     limit=start;howMany |
 *     limit=howMany
 *
 */

include 'connect.php';
include 'json.common.php';

$method = $_GET;

function limit() {
  global $method;
  if (isset($method['limit'])) {
    $parts = explode(':',$method['limit']);
    if ($parts->count() > 2)
      throw new Exception('Wrong limit clause');
    if ($parts->count() == 2)
      return "LIMIT $parts[0]->trim(), $parts[1]->trim()";
    else
      return "LIMIT $parts[0]->trim()";
  }
  return "";
}

function fields() {
  global $method;
  if (isset($method['fields'])) {
    // rudimentary sql injection safe
    $fields = array_map(function($itm){
      return trim($itm);
    }, explode(',', $method['fields']));
    return implode(', ',$fields);
  }
  return '*';
}

function where() {
  global $method;
  if (isset($method['where'])) {
    $orParts = preg_split('/ or /i', urldecode($method['where']));
    $orResult = array();
    foreach($orParts as $oParts) {
      $andParts = preg_split('/ and /i', $oParts);
      $andResult = array();
      foreach($andParts as $part) {
        $res = explode(';', $part);
        if (count($res) < 3)
          throw new Exception('Malformed Where');
        $fld = trim(array_shift($res));
        $op = trim(array_shift($res));
        if (str_ends_with('in', strtolower($op))) {
          $values = implode(',', $res);
          array_push($andResult, " $fld $op ($values) ");
        } else {
          array_push($andResult, " $fld $op $res[0] ");
        }
      }
      array_push($orResult, implode(' AND ', $andResult));
    }
    $result = implode(' OR ', $orResult);
    return " WHERE $result ";
  }
  return "";
}

function orderBy() {
  global $method;
  if (isset($method['orderBy'])) {
    $result = array();
    $parts = explode(',', urldecode($method['orderBy']));
    foreach($parts as $part) {
      $atom = preg_split('/\s+/', $part);
      array_push($result, array_map(function ($itm) use ($atom) {
        return trim($itm);
      }, $atom));
      return ' ORDER BY ' . implode(',', array_map(function ($atom){
        return implode(' ', $atom);
      }, $result));
    }
  }
  return "";
}




function route() {
  global $method;
  if (isset($method['fetch']))
    return fetch();
}

function fetch() {
  global $method, $tables;
  $cmd = $method['fetch'];
  $fields = fields();
  $where = where();
  $orderBy = orderBy();
  $limit = limit();


  if (isset($tables[$cmd])) {
    return fetchAssoc("SELECT $fields FROM $tables[$cmd] $where $orderBy $limit");
  } else {
    return array('error'=>array('message'=>"Unknown fetch:$cmd"));
  }
}

// route and report
try {
  $res = route();
  echo(json_encode($res,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}catch (Exception $err) {
  echo(json_encode(buildError($err),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

?>