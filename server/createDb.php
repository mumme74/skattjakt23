<?php
include 'connect.php';


function execute($sql) {
  global $db;
  $stmt = $db->prepare($sql);
  $stmt->execute();
}

function dropTable($table) {
  $sql = "DROP TABLE IF EXISTS $table ;";
  echo('drop' . $sql);
  execute($sql);
}


dropTable('Users');
execute(<<<EOD
CREATE TABLE Users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  userName VARCHAR(30) UNIQUE NOT NULL,
  firstName VARCHAR(30) NOT NULL,
  lastName VARCHAR(30) NOT NULL,
  email VARCHAR NOT NULL,
  password VARCHAR NOT NULL,
  lastLogin DATETIME DEFAULT current_timestamp,
  createdAt DATETIME DEFAULT current_timestamp
)
EOD);

dropTable('Groups');
execute(<<<EOD
CREATE TABLE Groups (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(30) UNIQUE NOT NULL,
  creatorId INTEGER NOT NULL,
  createdAt DATETIME DEFAULT current_timestamp,
  FOREIGN KEY(creatorId) REFERENCES Users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
)
EOD);

dropTable('GroupInvitations');
execute(<<<EOD
CREATE TABLE GroupInvitations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  userId INTEGER,
  groupId INTEGER,
  createdAt DATETIME DEFAULT current_timestamp,
  FOREIGN KEY(userId) REFERENCES Users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(groupId) REFERENCES Groups(id)
    ON DELETE CASCADE ON UPDATE CASCADE
)
EOD);

dropTable('GroupMembers');
execute(<<<EOD
CREATE TABLE GroupMembers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  userId INTEGER,
  groupId INTEGER,
  createdAt DATETIME DEFAULT current_timestamp,
  FOREIGN KEY(userId) REFERENCES Users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(groupId) REFERENCES Groups(id)
    ON DELETE CASCADE ON UPDATE CASCADE
)
EOD);

dropTable('Questions');
execute(<<<EOD
CREATE TABLE Questions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  str VARCHAR(255),
  updatedAt DATETIME DEFAULT current_timestamp,
  createdAt DATETIME DEFAULT current_timestamp
)
EOD);

dropTable('QuestionChoices');
execute(<<<EOD
CREATE TABLE QuestionChoices (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  str VARCHAR(255),
  questionId INTEGER,
  type TINYINT, --  0= radio, 1=chbox, 2=text
  points REAL NOT NULL,
  updatedAt DATETIME DEFAULT current_timestamp,
  createdAt DATETIME DEFAULT current_timestamp,
  FOREIGN KEY(questionId) REFERENCES Questions(id)
    ON DELETE CASCADE ON UPDATE CASCADE
)
EOD);

dropTable('Answers');
execute(<<<EOD
CREATE TABLE Answers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  userId INTEGER,
  questionId INTEGER,
  answeredBy INTEGER,
  str VARCHAR(255),
  FOREIGN KEY(userId) REFERENCES Users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(questionId) REFERENCES Questions(id)
    ON DELETE CASCADE ON UPDATE CASCADE
)
EOD);

dropTable('AnswerChoices');
execute(<<<EOD
CREATE TABLE AnswerChoices (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  answerId INTEGER,
  questionChoiceId INTEGER,
  points REAL NOT NULL,
  autoCorrected BOOLEAN NOT NULL,
  answeredAt DATETIME current_timestamp,
  FOREIGN KEY(questionChoiceId) REFERENCES QuestionsChoices(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(answerId) REFERENCES Answers(id)
    ON DELETE CASCADE ON UPDATE CASCADE
)
EOD);

// ---------------------------------------------------
// Insert data

execute(<<<EOD
INSERT INTO Users  (id, password, userName, firstName, lastName, email)
VALUES
(1, 'jof', 'lazy', 'Fredrik', 'Johansson', 'Fredrik.Johansson3@vaxjo.se'),
(2, 'ohp', 'husbil', 'Pierre', 'Ohlsson', 'Pierre.Ohlsson@vaxjo.se'),
(3, 'lak', 'cyklist','Karl', 'Larsson', 'Karl.Larsson@vaxjo.se'),
(4, 'jopa', 'Öland', 'Paul', 'Johansson', 'Paul.Johansson@vaxjo.se'),
(5, 'astm', 'mx5a', 'Magnus', 'Åström', 'Magnus.Astrom@vaxjo.se'),
(6, 'lif', 'skogen', 'Fredrik', 'Lindström', 'Fredrik.Lidstrom@vaxjo.se'),
(7, 'hasn', 'Si! italiano', 'Sanja', 'Hadzic', 'Sanja.Hadzic@vaxjo.se'),
(8, 'ahm', 'Kia_owner', 'Martina', 'Åhnstrand', 'Martina.Ahnstrand@vaxjo.se'),
(9, 'kgb', 'Göteborg','Katarina', 'Grudeborn Bojestig', 'katarina.grudeborn-bojestig@vaxjo.se'),
(10, 'momi', 'Tingsvän', 'Marie-Louise', 'Mohlin-Gabrielsson', 'marie-louise.mohil-gabrielsson.@vaxjo.se'),
(11, 'nils', 'Välkommen!', 'Sofie', 'Nilsson', 'Sofie.Nilsson@vaxjo.se'),
(12, 'gem', 'Historia','Martin', 'Gereborg', 'Martin.Gereborg@vaxjo.se'),
(13, 'haki', 'saknar_du_oss?', 'Kristina', 'Hallberg', 'Kristina.Hallberg@vaxjo.se')
EOD);


execute(<<<EOD
INSERT INTO Groups (id, name, creatorId) VALUES
(1, 'husbil', 2),
(2, 'kgb', 9),
(3, 'italy', 7),
(4, 'kia', 13)
EOD);

execute(<<<EOD
INSERT INTO GroupInvitations (id, userId, groupId) VALUES
(1,12,4),(2,6,1)
EOD);

execute(<<<EOD
INSERT INTO GroupMembers (id, userId, groupId) VALUES
(1,2,1), -- Pierre i grupp 1 (ägare)
(2,3,2), -- Karl i grupp 2
(3,4,3), -- Paul i grupp 3
(4,5,4), -- Magnus i grupp 4
(5,6,2), -- Fredrik L i grupp 2
(6,7,3), -- Sanja i grupp 3 (ägare)
(7,8,4), -- Martina i grupp 4 (ägare)
(8,9,2), -- Katarina i grupp 2 (ägare)
(9,10,1), -- Mimmi i grupp 1
(10,11,4), -- Sofie i grupp 4
(11,12,3), -- Martin i grupp 3
(12,13,1) -- Kristina i grupp 1
EOD);

execute(<<<EOD
INSERT INTO Questions (id, str) VALUES
(1,'LitteraturHistoria: vart grävdes budkavlen ned i marken i Willhelm Mobergs "Rid i natt"'),
(2,'Matte: Vad blir summan av dessa 2 signerade heltal?\nBinärtalet "00000001" + "11111110"'),
(3,'Religion: Vart tog alla "rov" hjältar vägen när de dog, enligt Asatron?'),
(4,'Vad riskerar hända ifall du förväxlar +Cos med -Cos ledningarna i en elmotors resolver?'),
(5,'vad heter den ingenjör som omvälvde världen med världsomvändande teknikhändelse, som inträffade 3 år efter Alan Turings död.\nDenna sovjetiska ingenjör hade varit i Gulag lägret under 2a världskriget'),
(6,'Vad kännetecknar en Hesselman motor?'),
(7,'Vilket år vanns melodifestivalen av 3 bröder från Sverige?'),
(8,'Vad är korrekt angående bandgapet i ett halvledarmaterial'),
(9,'Vilket parseträd är rätt i grammatiken i meningen:\n "Sanja rättade läxan"'),
(10,'GWP värdet är ett mått på:'),
(11,'Vilka av dessa jetmotorer är konstruerade i Sverige?'),
(12,'Hur många radianer är ett var på en cirkel?'),
(13,'Vilket land kom den uppfinnare ifrån som en känd elbilstillverkare tagit sitt namn ifrån'),
(14,'Vilket år räknar man med att nästa datumrelaterade datorbugg kommer inträffa, typ som Y2K fast senare'),
(15,'Vilket år föddes den person som räknas som världens första programmerare?'),
(16,'Vad används dopplereffekten till på en fordonsradar i en bil?'),
(17,'Ett arbete skall ta 3,30 perioder, timpriset är 1000:-. Vad blir arbetskostnaden utan moms?'),
(18,'Vilket känt fotbollslag är starkt förknippat med förändringen kring datoriseringen?'),
(19,'Vad heter spindelbult på engelska?'),
(20,'Vilka länder är med i EU?')
EOD);

// type  0=radio, 1=chbox, 2=text
execute(<<<EOD
INSERT INTO QuestionChoices (id, str, questionId, type, points) VALUES
(1,'',1,2,2.0),
--fråga 2
(2,'',2,2,2.0),
-- fråga3
(3,'Odens palats',3,0,0.0),
(4,'Midgård',3,0,0.0),
(5,'Yggdrasil',3,0,0.0),
(6,'Valhall',3,0,1.0),
--fråga 4
(7,'Motorn går baklänges',4,1,0.0),
(8,'Motorn går ur fas och bränner upp lindningarna',4,1,0.0),
(9,'Motorn fungerar nästan lika bra, märks inte.',4,1,1.0),
(10,'Motorns rotor och stator förskjuts skadligt inuti',4,1,0.0),
--fråga 5
(11,'IC-kretsen uppfanns.',5,0,0.0),
(12,'P-pillret släpptes första gången.',5,0,0.0),
(13,'Biltelefonen uppfanns.',5,0,0.0),
(14,'En "färdkamrat" startade sin resa ovanpå en R7.',5,0,1.0),
(15,'Sovjet provsprängde världens hittills största kärnvapen.',5,0,0.0),
--fråga 6
(16,'Mer okomplicerad än en vanlig 2taktare.',6,1,0.0),
(17,'Högre kolvhastighet än en Formel 1 motor.',6,1,0.0),
(18,'Går köra på diesel och bensin',6,1,1.0),
(19,'Konkurrerades ut av förkammardieselmotorn',6,1,1.0),
--fråga7
(20,'',7,2,1.0),
--fråga8
(21,'Elektronförspänningen krävs för att utarma spärrskiktet',8,1,1.0),
(22,'Ligger på ca 1,8 volt på en blå lysdiod',8,1,0.0),
(23,'Ligger på ca 1,8 volt på en röd LED',8,1,1.0),
(24,'Är ca 0,2Volt på en kisel PN övergång.',8,1,0.0),
--fråga 9
(25,
'mening->subject->Sanja
      ->verb   ->rättade
      ->object ->läxan',9,0,1.0),
(26,
'mening->object->Sanja
      ->verb   ->rättade
      ->subject ->läxan',9,0,0.0),
(27,
'mening->substantiv->Sanja
      ->adjektiv  ->rättade
      ->superlativ->läxan',9,0,0.0),
--fråga 10
(28,'Bränslets energivärde',10,1,0.0),
(29,'Försurningsgrad på glykolen',10,1,0.0),
(30,'Växthusdrivande i förhållande till CH4',10,1,0.0),
(31,'Uppvärmningseffekt i förhållande till koldioxid',10,1,1.0),
--fråga 11
(32,'Avon motorn',11,1,0.0),
(33,'Volvo aero RM12',11,1,0.0),
(34,'Glan',11,1,1.0),
(35,'Skuten',11,1,1.0),
--fråga 12
(36,'360°',12,0,0.0),
(37,'12timmar',12,0,0.0),
(38,'2π',12,0,1.0),
(39,'4x90',12,0,0.0),
--fråga 13
(40,'USA',13,0,0.0),
(41,'Stor britanien',13,0,0.0),
(42,'Japan',13,0,0.0),
(43,'Serbien',13,0,1.0),
--fråga 14
(44,'år 3000',14,0,0.0),
(45,'år 2100',14,0,0.0),
(46,'år 2048',14,0,0.0),
(47,'år 2038',14,0,1.0),
--fråga 15
(48,'år 1912',15,0,0.0),
(49,'år 1903',15,0,0.0),
(50,'år 1878',15,0,0.0),
(51,'år 1815',15,0,1.0),
--fråga 16
(52,'',16,2,2.0),
--fråga 17
(53,'',17,2,2.0),
--fråga 18
(54,'',18,2,1.0),
--fråga 19
(55,'',19,2,1.0),
--fråga 20
(56,'Slovenien',20,1,1.0),
(57,'Österrike',20,1,1.0),
(58,'Andorra',20,1,0.0),
(59,'Irland',20,1,1.0),
(60,'Wales',20,1,0.0),
(61,'Malta',20,1,1.0),
(62,'Litauen',20,1,1.0);
EOD);

?>