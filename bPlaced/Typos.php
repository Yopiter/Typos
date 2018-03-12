<?php
function getConnection($host = 'localhost', $user = 'frankmaendel', $password = '2Mratni1', $database = 'frankmaendel')
{
    $Connect = mysqli_connect($host, $user, $password, $database) or die ('Keine Verbindung möglich');
    mysqli_query($Connect, "SET NAMES 'utf8'");
    return $Connect;
}

$oConnect = getConnection();
$sSql = 'SELECT ip FROM Typo_IP';
$oResult = mysqli_query($oConnect, $sSql);
if (!$oResult) {
    die('error: ' . mysqli_error($oConnect));
}
$sSql = trim(mysqli_fetch_array($oResult)['ip']);
header("location:http://$sSql:62658");
