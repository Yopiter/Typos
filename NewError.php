<?php
require_once 'DB_Connector.php';
require_once 'functions.php';
initiateSession();
$oConnector = new DB_Connector();
setNewChap($oConnector, $_POST, 'chap');
$sChapText = stringToCleanString(getChapText(getCurrentChapter()));
$sOrig = stringToCleanString($_GET['original'] ?? '');
$sRepl = stringToCleanString($_GET['corrected'] ?? '');
$sComment = stringToCleanString($_GET['comment'] ?? '');
$iType = $_GET['type'] ?? 0;

if (isset($_GET['sub']) && !isErrorInputOK($sOrig, $sRepl, $sChapText, $iType, $oConnector)) {
    $iChapID = $oConnector->getChapID(getCurrentNovel(), getCurrentChapter());
    if ($oConnector->createError($iChapID, $sOrig, $sRepl, $sComment, $iType)) {
        setMessage('Error was saved to the database.');
        redirectNow('NewError.php');
    }
}

?>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Error in <?php echo $oConnector->getNovelName(getCurrentNovel()); ?></title>
    <link rel="stylesheet" href="typos_styles.css">
</head>
<body>
<?php
showMessages();
?>
<h1>Edit Error in <?php echo $oConnector->getNovelName(getCurrentNovel()); ?></h1>
<form method="post">
    <label><?php
        printNovelSelect($oConnector, 'chap', $oConnector->getChapID(getCurrentNovel(), getCurrentChapter()));
        ?> Choose a different chapter</label>
</form>
<form method="get">
    <label>
        <input type="text" name="original" value="<?php echo $sOrig; ?>" placeholder="Erroneous string"> Original text
        from the novel
    </label><br/>
    <label>
        <input type="text" name="corrected" value="<?php echo $sRepl ?>" placeholder="Corrected string"> Corrected
        string to replace
        the original one.
    </label><br/>
    <label>
        <input type="text" name="comment" value="<?php echo $sComment ?>" placeholder="Comments"> Additional comments
        for this error
    </label><br/>
    <label>
        <?php
        printTypeSelect($oConnector, 'type', $iType);
        ?> Error type
    </label><br/>
    <input type="submit" value="Submit" name="sub"><br/>
</form>
<textarea readonly><?php echo stringToTextareaString($sChapText); ?></textarea>
<a class="button" href="ViewChap.php">Cancel and view chapter</a>
</body>
</html>
