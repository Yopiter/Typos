<?php
require_once 'functions.php';
require_once 'DB_Connector.php';
initiateSession();
checkForParameters(['chap'], $_SESSION);
$oConnect = new DB_Connector();
setNewChap($oConnect, $_POST, 'chap');
$iChapNumm = getCurrentChapter();
$sText = getChapText($iChapNumm);
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
<a class="button" href="NewError.php">Note Error</a>
<?php
echo "<a class='button' href='http://www.wuxiaworld.com/bp-index/bp-chapter-$iChapNumm/'>Link to WuxiaWorld</a><br/>";
?>
<div class="changeChap">
    <form method="post">
        <?php
        printNovelSelect($oConnect, 'chap', $oConnect->getChapID(getCurrentNovel(), $iChapNumm, true));
        ?>
        <input type="submit" value="View Chapter">
    </form>
</div>
<?php
echo stringToShowString($sText);
?>
</body>
</html>
