<?php
require_once 'DB_Connector.php';
require_once 'functions.php';
initiateSession();
$oConnector = new DB_Connector();
$sChapText = getChapText(getCurrentChapter());
$sOrig = $_GET['original'] ?? '';
$sRepl = $_GET['corrected'] ?? '';
$sComment = $_GET['comments'] ?? '';
$iType = $_GET['type'] ?? 0;

if (isset($_GET['sub'])) {
    //Prüfung aller Eingaben
    $bError = false;
    if (empty($sOrig) || !isUnique($sOrig, $sChapText)) {
        setMessage('The original string needs to be unique in this chapter!', 'error');
        $bError = true;
    }
    if (empty($sRepl)) {
        setMessage('The replacement needs to contain something. If you want to delete something, use some surrounding words as a buffer for both the original and the replacing string');
        $bError = true;
    }
    if (empty($iType)) {
        setMessage('Please choose an error type. This will be used to distinguish between errors when highlighting them.', 'error');
        $bError = true;
    }
    if (!$bError) {
        $iChapID = $oConnector->getChapID(getCurrentNovel(), getCurrentChapter());
        if ($oConnector->createError($iChapID, $sOrig, $sRepl, $sComment, $iType)) {
            setMessage('Error was saved to the database.');
            redirectNow('ViewChap.php');
        }
    }
}

?>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Error</title>
    <link rel="stylesheet" href="typos_styles.css">
</head>
<body>
<?php
showMessages();
?>
<h1>Edit Error</h1>
<form method="get">
    <label>
        <input type="text" disabled value="<?php echo $oConnector->getNovelName(getCurrentNovel()); ?>"> Novel
    </label><br/>
    <label>
        <input type="text" disabled value="<?php echo getCurrentChapter(); ?>"> Chapter
    </label><br/>
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
    <input type="submit" value="Submit" name="sub">
</form>
<textarea readonly><?php echo stringToTextareaString($sChapText); ?></textarea>
</body>
</html>
