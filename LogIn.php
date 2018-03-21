<?php
require_once 'functions.php';
session_start();
$_SESSION['#disabled'] = false;
if (isset($_SESSION['loginName'])) {
    redirectNow('index.php');
}

$sName = $_POST['user'] ?? '';
$sPW = $_POST['pw'] ?? '';

$aUser = [];
$aFile = file('user.txt', FILE_IGNORE_NEW_LINES);
foreach ($aFile as $sUserString) {
    $aLine = explode('||', $sUserString);
    if (count($aLine) !== 2) {
        continue;
    }
    $aUser[trim($aLine[0])] = trim($aLine[1]);
}

if (!empty($sName)) {
    if (empty($aUser[$sName])) {
        setMessage('This user is not recognized in our system', 'error');
    } elseif ($aUser[$sName] !== $sPW) {
        setMessage('This password seems a bit off', 'error');
    } else {
        if (registerIP($_SERVER['REMOTE_ADDR'])) {
            setMessage('You have logged in.');
        } else {
            setMessage('There has been a problem with your login.', 'error');
        }
        if (isset($_SESSION['chap'])) {
            redirectNow('ViewChap.php');
        } else {
            redirectNow('index.php');
        }
    }
}

?>

<html>
<head>
    <title>Typos - LogIn</title>
    <meta charset='utf-8'>
    <link rel="stylesheet" href="typos_styles.css">
</head>
<body>
<?php
showMessages();
?>
<h1>Log in to see and edit errors</h1>
<form method='post'>
    <input type='text' value='<?php echo $sName; ?>' name='user' placeholder='Username'>Username<br/>
    <input type='password' name='pw' placeholder='Password'>Password<br/>
    <input type='submit' value='Log in'>
</form>
</body>
</html>
