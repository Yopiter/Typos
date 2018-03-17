<?php
require_once 'DB_Connector.php';
require_once 'functions.php';
initiateSession();
$oConnect = new DB_Connector();

$iSelected = $_GET['chap'] ?? 0;
if (isset($_GET['chap'])) {
    if ($iSelected === 0 || $iSelected === '') {
        setMessage('Please choose a chapter below!', 'warning');
    } else {
        $aChap = $oConnect->getChapArrFromID($iSelected);
        setNovel($aChap['Novels_ID']);
        setChapter($aChap['ChapNummer']);
        redirectNow('NewError.php');
    }
}
?>
    <html>
    <head>
        <title>Typos and Friends</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="typos_styles.css">
    </head>
    <body>
    <?php
    showMessages();
    ?>
    <h1>Please choose a novel and a chapter</h1>
    <form method="get" action="index.php">
        <?php
        printNovelSelect($oConnect, 'chap', $iSelected);
        ?>
        <input type="submit" value="View Chapter">
    </form>
    <br/>
    <a class="button" href="importChaps.php">Import new Chaps (Slow AF)</a>
    </body>
    </html>
<?php
