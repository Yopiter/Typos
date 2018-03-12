<?php
function getConnection($host = 'localhost', $user = 'frankmaendel', $password = '2Mratni1', $database = 'frankmaendel')
{
    $Connect = mysqli_connect($host, $user, $password, $database) or die ('Keine Verbindung möglich');
    mysqli_query($Connect, "SET NAMES 'utf8'");
    return $Connect;
}

if (!isset($_POST['parole']) || $_POST['parole'] !== 'WeNeedTypos') {
    die('fuck off from my website -.-');
}
//Skript, das der Server regelmäßig aufrufen muss um seine IP aktuell zu halten
$sIP = $_SERVER['REMOTE_ADDR'];
$oConnect = getConnection();
$sSql = "UPDATE `frankmaendel`.`Typo_IP` SET `ip` = '$sIP'";
$oResult = mysqli_query($oConnect, $sSql);
if (!$oResult) {
    die('error: ' . mysqli_error($oConnect));
}
echo 'done';
