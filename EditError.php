<?php
require_once 'functions.php';
require_once 'DB_Connector.php';

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    setMessage('No typo given to edit...', 'error');
    redirectNow('ViewChap.php');
}

initiateSession();
$oConnector = new DB_Connector();
$sChapText = stringToCleanString(getChapText(getCurrentChapter()));

$iID = $_GET['id'] ?? $_POST['id'];
$aError = $oConnector->getErrorArray($iID);

$sOrig = stringToCleanString($_POST['original'] ?? $aError['original']);
$sRepl = stringToCleanString($_POST['corrected'] ?? $aError['corrected']);
$sComment = stringToCleanString($_POST['comment'] ?? $aError['Comment']);
$iType = $_POST['type'] ?? $aError['type'];

if (isset($_POST['sub']) && !isErrorInputOK($sOrig, $sRepl, $sChapText, $iType, $oConnector, $iID)) {
    $iChapID = $oConnector->getChapID(getCurrentNovel(), getCurrentChapter());
    if ($oConnector->changeError($iID, $sOrig, $sRepl, $sComment, $iType)) {
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
