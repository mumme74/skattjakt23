<?php
// Enable verbose output of error (or include from config.php)
error_reporting(-1);              // Report all type of errors
ini_set("display_errors", 1);     // Display all errors

// Create a DSN for the database using its filename
$fileName = __DIR__ . "/db/skattjakt23.sqlite";
$dsn = "sqlite:$fileName";

// Open the database file and catch the exception if it fails.
try {
  $db = new PDO($dsn);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Failed to connect to the database using DSN:<br>$dsn<br>";
  throw $e;
}

$tables = array(
  'users'=>'Users',
  'groups'=>'Groups',
  'groupInvites'=>'GroupMembers',
  'groupInvites'=>'GroupInvitations',
  'questions'=>'Questions',
  'questionChoices'=>'QuestionChoices',
  'answers'=>'Answers',
  'answerChoices'=>'AnswerChoices'
);

$timestamps = array(
  'Users',
  'Groups',
  'Questions',
  'QuestionChoices',
);

function updateTimestamp($table) {
  global $timestamps;
  if (array_search($table, $timestamps)) {
    $newDateTime = new DateTime();
    $newDateTime->setTimezone(new DateTimeZone("UTC"));
    return date_format($newDateTime, 'c');
  }
  return '';
}

function tableName($table) {
  global $tables;
  if (!$tables[$table])
    throw new Exception("Unknown tablename $table");
  return $tables[$table];
}

function execute($sql, $params = null) {
  global $db;
  $stmt = $db->prepare($sql);
  $success = $stmt->execute($params);
  return [
    'statement'=>$stmt,
    'success'=>$success,
    'insertId'=>(int)$db->lastInsertId()
  ];
}

function fetchAssoc($sql, $params = null) {
  $res = execute($sql, $params);
  if ($res['success'])
    return $res['statement']->fetchAll(PDO::FETCH_ASSOC);
  throw new Exception('Query failed:'. $sql);
}

?>