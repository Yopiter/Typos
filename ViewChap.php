<?php
require_once 'functions.php';
require_once 'DB_Connector.php';
initiateSession();
checkForParameters(['chap'], $_SESSION);
$oConnect = new DB_Connector();
if (!empty($_GET['download'])) {
    $sChapTextCorrect = getCorrectedChapText(getCurrentChapter(), getCurrentNovel(), $oConnect);
    doDownloadAsTxt($sChapTextCorrect, 'Chapter_' . getCurrentChapter());
}
setNewChap($oConnect, $_POST, 'chap');
$iChapNumm = getCurrentChapter();
$sText = stringToCleanString(getChapText($iChapNumm));
$iChapID = $oConnect->getChapID(getCurrentNovel(), getCurrentChapter());
$aErrors = $oConnect->getAllErrorsFromChap($iChapID);
foreach ($aErrors as $iErrorID => $aError) {
    $sText = markErrorInText($sText, $aError);
}
?>
<html>
<head>
    <meta charset="utf-8">
    <title>View Chapter</title>
    <link rel="stylesheet" href="typos_styles.css">
    <script src="functions.js"></script>
</head>
<body>
<?php
showMessages();
?>
<h1>Chapter <?php echo $iChapNumm ?></h1>
<form method="post">
    <a class="button" href="NewError.php">Note Error</a>
    <a class="button" href="ViewChap.php?download=1">Download correct(-ish) text</a>
    <?php
    echo "<a class='button' href='" . getChapUrl($iChapNumm) . "'>Link to WuxiaWorld</a>";
    printNovelSelect($oConnect, 'chap', $oConnect->getChapID(getCurrentNovel(), $iChapNumm, true));
    printNextChapLink($oConnect);
    ?>
</form>
<br/>
<div class='info'>
    Click the marked error strings to activate or deactivate this error. Click the corrected, green string to edit this
    error. Click the comment to do absolutely nothing and waste your time.<br/>
    Hover your mouse over one of the parts to show more information.
</div>
<div class='viewText'>
    <?php
    echo stringToShowString($sText);
    ?>
</div>
</body>
</html>
