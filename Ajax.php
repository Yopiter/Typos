<?php

include_once 'DB_Connector.php';
include_once 'functions.php';

function doApply($iErrorID, $bApply)
{
    $oConnect = new DB_Connector();
    $bApply = $bApply ? true : false;
    if ($oConnect->setApplyForError($iErrorID, $bApply)) {
        $aError = $oConnect->getErrorArray($iErrorID);
        return getInnerErrorMarkup($aError);
    }
    return 'An error occured while (de)activating this error!';
}

//MAIN
if (!isset($_POST['action'])) {
    die('No action was chosen for this ajax call...');
}
switch ($_POST['action']) {
    case 'apply':
        echo doApply($_POST['ID'], $_POST['apply']);
        break;
}
