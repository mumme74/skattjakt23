<?php
/**
 * Put values to database
 * {
 *   "upsert": {
 *     "table":table,
 *     "where": where, // optional -> insert
 *     "values": [
 *        {field:"field1", value: 'vlu'},
 *        {...}
 *      ]
 *   },
 *   "delete": {
 *     "table":table,
 *     "where": where
 *   }
 *   "select": {
 *     "table":table,
 *     "fields"?:['field1',...] | '*'
 *     "limit":{start?:num, count:num},
 *     "orderBy":[
 *        {field:field1, dir?:'DESC'|'ASC'},
 *        ...
 *     ],
 *     "where":{
 *      or: {
 *        {and: [
 *          {field:'field',op:'=',value:1}},
 *          {field:'field2',op:'in',values:[1,2,3]}}
 *        ]
 *      }
 *      'or' and 'and' may be omitted
 *     }
 *   },
 *   "updatesSince": 'DateString' // 2023-08-18T19:45:64.300
 * }
 *
 *
 * where: {
 *      or: [
 *        and: [
 *          {field:{op:'=',value}},
 *          {field2:{op:'in',values:[1,2,3]}}
 *        ]
 *      ]
 *      'or' and 'and' may be omitted
 *     }
 *
 */

include 'connect.php';
include 'json.common.php';
include 'login.php';

function clean($str) {
  $res = preg_replace_callback(
    '/;.*(?:from|where|select|delete|update|drop|create)/i',
    fn($match)=>
      '',
    $str);
  return $res;
}

function where($obj) {
  if (!isset($obj['where']))
    return array('str'=>'','params'=>array());

  $where = $obj['where'];
  $params = array();

  $pushToParam = mkPushToParams($params);

  $parse = function($obj) use($pushToParam) {
    $field = clean($obj['field']);
    $op = clean($obj['op']);
    if (isset($obj['value'])) {
      $pushToParam($obj['value']);
      return "$field $op ?" ;
    } else if (is_array($obj['values'])){
      $values = implode(',',
        array_map(function($vlu) use($pushToParam) {
          $pushToParam($vlu);
          return '?';
        }, $obj['values'])
      );
      return "$field $op ($values)";
    } else
      throw new Exception('Malformed where');
  };

  $and = function($obj) {
    $parts = array_map($parse, $obj);
    return implode(' AND ', $parts);
  };

  $or = function($obj) {
    $parts = is_array($obj) ?
      array_map($parse, $obj) :
        $and($obj);
    return implode(' OR ', $parts);
  };

  if (isset($where['or']))
    $str = $or($where['or']);
  else if (isset($where['and']))
    $str = $and($where['and']);
  else if (count($where) == 1)
    $str = $parse($where[0]);
  else
    throw new Exception('Multiple where must be wrapped in "and" or "or"');

  return array(
    'str'=>"WHERE $str",
    'params'=>$params
  );
}

function limit($cmd) {
  if (!isset($cmd['limit']))
    return '';
  $obj = clean($cmd['limit']);
  $count = clean($cmd['count']);
  if (isset($obj['start'])) {
    $start = clean($obj['start']);
    return "LIMIT $start, $count";
  }
  return "LIMIT $count";
}

function orderBy($cmd) {
  if (!isset($cmd['orderBy']))
    return '';

  $fields = implode(' ',
    array_map(function ($obj) {
      $field = clean($obj['field']);
      $dir = isset($obj['dir']) ? $obj['dir'] : '';
      return "$field $dir";
    }, $cmd['orderBy'])
  );

  return "ORDER BY $fields";
}

function fieldNames($arr) {
  $fields = array_map(function($obj){
    return clean($obj['field']);
  });
  return $fields;
}

function mkPushToParams(&$params) {
  return function ($vlu) use(&$params) {
    $vluOk = is_string($vlu) ? "'$vlu'" : $vlu;
    array_push($params, $vlu);
  };
}


function upsert($cmd) {
  $table = tableName($cmd['table']);
  $whereArr = where($cmd);
  $where = $whereArr['str'];
  $params = $whereArr['params'];

  $pushToParam = mkPushToParams($params);

  $insert = function($values) use($table, $pushToParam) {
    $fields = implode(',',
      array_map(fn($obj)=>clean($obj['field']), $values));
    $vals = implode(',',
      array_map(function($obj) use($pushToParam) {
        $pushToParam($obj['value']);
        return '?';
      }, $values));
    $sql = "INSERT INTO $table ($fields) VALUES ($vals)";
    return $sql;
  };

  $update = function($values) use($table, $where, $pushToParam) {
    $timestamp = updateTimestamp($table);
    if ($timestamp) {
      array_push($values, [
        'field'=>'updatedAt', 'value'=>$timestamp]);
    }
    $parts = array_map(
      function ($obj) use($pushToParam) {
        $pushToParam($obj['value']);
        return clean($obj['field']) . "=?";
      }, $values);
    $vals = implode(',', $parts);
    $sql = "UPDATE SET $vals $where";
    return $sql;
  };

  $insertSql = $insert($cmd['values']);
  $updateSql = $update($cmd['values']);
  $sql = "$insertSql ON CONFLICT DO $updateSql";
  $res = execute($sql, $params);
  if ($res['success'])
    return ['success'=>true, 'insertId'=>$res['insertId']];
  throw new Exception('Failed to modify database');
}

function del($cmd) {
  global $db;
  $table = tableName($cmd['table']);
  $whereArr = where($cmd);
  $where = $whereArr['str'];
  $ids = array_map(
    fn($row)=>$row['id'],
    fetchAssoc("SELECT id FROM $table $where", $whereArr['params'])
  );
  $params = $whereArr['params'];
  if (!$where)
    throw new Exception('Did not specify where');
  $sql = "DELETE FROM $table $where";
  $res = execute($sql, $params);
  if ($res['success']) {
    if (count($ids) > 0) {
      $deletes = implode(',',array_map(fn($id)=>"('$table', $id)", $ids));
      execute("INSERT INTO Deletes (tableName, deleteId) VALUES $deletes");
    }
    return ['success'=>true, 'affectedIds'=>$ids];
  }
  throw new Exception('Failed to modify database');
}

function select($cmd) {
  $table = tableName($cmd['table']);

  $limit = limit($cmd);
  $orderBy = orderBy($cmd);
  $whereArr = where($cmd);
  $where = $whereArr['str'];
  $params = $whereArr['params'];
  $fields = isset($cmd['fields']) ?
     is_array($cmd['fields']) ?
      implode(',', array_map(clean, $cmd['fields'])) :
      clean($cmd['fields'])
    : '*';

  $sql = trim("SELECT $fields FROM $table $where $orderBy $limit");
  $res = fetchAssoc($sql,$params);

  return $res;
}

function changesSince($dateOrig) {
  global $tables, $timestamps;
  $time = new DateTime($dateOrig);
  $time->setTimeZone(new DateTimeZone("UTC"));
  $date = $time->format("Y-m-d H:i:s");
  $deletes = array_reduce(fetchAssoc(
    "SELECT deleteId as id, tableName FROM Deletes " .
    "WHERE strftime('%Y-%m-%d %H:%M:%f',deletedAt) > ?",
    [$date]),
    function($carry, $row){
      if (isset($carry[$row['tableName']]))
        array_push($carry[$row['tableName']],$row['id']);
      else
        $carry[$row['tableName']] = [$row['id']];
      return $carry;
    },
    []
  );

  $mkAllRowsSince = function($field) use($date) {
    return function($table) use($field, $date) {
      return [
        'table'=>$table,
        'ids'=>fetchAssoc(
          "SELECT * FROM $table WHERE strftime('%Y-%m-%d %H:%M:%f', $field) > ?",
          [$date])
      ];
    };
  };

  $updates = array_map($mkAllRowsSince('updatedAt'), $timestamps);
  $inserts = array_map($mkAllRowsSince('createdAt'), $tables);

  return [
    'deletes'=>$deletes,
    'updates'=>$updates,
    'inserts'=>$inserts
  ];
}

function route($method) {
  if (count($method) > 0) {
    $key = array_key_first($method);
    $cmd = $method[$key];
    switch ($key) {
    case 'upsert':
      verifyLogin();
      return upsert($cmd);
    case 'delete':
      verifyLogin();
      return del($cmd);
    case 'select':
      verifyLogin();
      return select($cmd);
    case 'changesSince':
      verifyLogin();
      return changesSince($cmd);
    case 'login':
      return login($cmd);
    default: break; // fall to exception
    }
  }
  throw new Exception("Unknown method");
}

try {
  $raw = file_get_contents('php://input');
  $json = json_decode($raw, JSON_UNESCAPED_UNICODE);
  if (!$json) throw new Exception("Invalid json");
  $res = route($json);
  echo(json_encode($res,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
} catch (Exception $err) {
  echo(json_encode(buildError($err),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

?>