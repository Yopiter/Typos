<?php
require_once 'functions.php';
require_once 'DB_Connector.php';
initiateSession();
checkForParameters(['chap'], $_SESSION);
$oConnect = new DB_Connector();
if (isset($_POST['chap']) && !empty($_POST['chap'])) {
    $aChap = $oConnect->getChapArrFromID($_POST['chap']);
    setNovel($aChap['Novels_ID']);
    setChapter($aChap['ChapNummer']);
}
$iChap = getCurrentChapter();
$sText = getChapText($iChap);
?>
<html>
<head>
    <meta charset="utf-8">
    <title>View Chapter</title>
    <link rel="stylesheet" href="typos_styles.css">
</head>
<body>
<?php
showMessages();
?>
<h1>Chapter <?php echo $iChap ?></h1>
<a href="EditError.php">Note Error</a>
<?php
echo "<a href='http://www.wuxiaworld.com/bp-index/bp-chapter-$iChap/'>Link to WuxiaWorld</a><br/>";
?>
<div class="changeChap">
    <form method="post">
        <?php
        printNovelSelect($oConnect, 'chap', $oConnect->getChapID(getCurrentNovel(), $iChap, true));
        ?>
        <input type="submit" value="View Chapter">
    </form>
</div>
<?php
echo stringToShowString($sText);
?>
</body>
</html>
