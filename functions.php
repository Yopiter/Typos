<?php

function getChapText($iChapNummer, $sLineBreak = '\n')
{
    $sUrl = getChapUrl($iChapNummer);
    $sContent = file_get_contents($sUrl);
    $sContent = explode('<div id="chapterContent" class="innerContent fr-view">', $sContent)[1] ?? '';
    $sContent = explode('</div>', $sContent)[0] ?? '';
    $iHits = preg_match_all("/<p[^>]*>((?!<\/p>).)*<\/p>/", $sContent, $aRows);
    if (empty($iHits)) {
        die("Keine Inhalte auf der <a href='$sUrl'>Seite</a> gefunden");
    }
    return implode($sLineBreak, array_map(function ($sRow) {
        return preg_replace('/<.*?>/', '', $sRow);
    }, $aRows[0]));
}

function initiateSession()
{
    error_reporting(E_ALL);
    session_start();
    $_SESSION['#disabled'] = false;
    if (empty($_SESSION['novel'])) {
        $_SESSION['novel'] = 0;
    }
    if (empty($_SESSION['chap'])) {
        $_SESSION['chap'] = 1;
    }
    if (!checkIP($_SERVER['REMOTE_ADDR'])) {
        redirectNow('LogIn.php');
    }
}

function checkIP($sIP)
{
    $aIPs = [];
    $sIP = trim($sIP);
    $aFile = file('ip.txt', FILE_IGNORE_NEW_LINES);
    foreach ($aFile as $sUserString) {
        $aLine = explode('||', $sUserString);
        if (count($aLine) !== 2 || (int)$aLine[1] - time() > (72 * 60 * 60)) {
            continue;
        }
        $aIPs[trim($aLine[0])] = trim($aLine[1]);
    }
    if (empty($aIPs[$sIP])) {
        setMessage('Your IP is not registered in our system, please log in first', 'warning');
        return false;
    }
    /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
    if ((int)$aIPs[$sIP] - time() > (24 * 60 * 60)) {
        //veralteten Eintrag löschen
        unset($aIPs[$sIP]);
        file_put_contents('ip.txt', array_map(function ($sIP, $sTime) {
            return $sIP . '||' . $sTime . "\n";
        }, array_keys($aIPs), array_values($aIPs)));
        setMessage('Your Login has expired.', 'warning');
        return false;
    }
    return true;
}

function registerIP($sIP)
{
    $sNewLine = "\n" . $sIP . '||' . time();
    return file_put_contents('ip.txt', $sNewLine, FILE_APPEND) !== false;
}

function setNovel($iNovelId)
{
    $_SESSION['novel'] = $iNovelId;
}

function setChapter($iChapter)
{
    $_SESSION['chap'] = $iChapter;
}

function checkForParameters(array $aParameter, array $aArray = null)
{
    if (!$aArray) {
        $aArray = $_REQUEST;
    }
    $bFehler = false;
    foreach ($aParameter as $sParam) {
        if (empty($aArray[$sParam])) {
            setMessage("Missing parameter $sParam", 'error');
            $bFehler = true;
        }
    }
    if ($bFehler) {
        $iChap = $_SESSION['chap'] ?? 1;
        redirectNow("ViewChap.php?chap=$iChap");
    }
}

function setMessage($sNachricht, $sType = 'bestaetigung')
{
    $_SESSION['messages'][] = ['text' => $sNachricht, 'type' => $sType];
}

function showMessages()
{
    if (!empty($_SESSION['#disabled'])) {
        return;
    }
    if (!empty($_SESSION['messages'])) {
        foreach ($_SESSION['messages'] as $aFehler) {
            printMessageBox($aFehler['text'], $aFehler['type']);
        }
        unset($_SESSION['messages']);
    }
}

function printMessageBox($sNachricht, $sClass = 'bestaetigung')
{
    echo "<div class='$sClass'>$sNachricht</div>";
}

function importAllChaps(DB_Connector $oConnect)
{
    $aChapsFromDB = $oConnect->getAllChapsFromNovel();
    $iChap = 0;
    while (true) {
        if (isset($aChapsFromDB[$iChap])) {
            $iChap++;
            continue;
        }
        $sUrl = getChapUrl($iChap);
        $sContent = file_get_contents($sUrl);
        if (!$sContent || strpos($sContent, 'Oops! That page can’t be found.') !== false) {
            $iChap--;
            break;
        }
        //Chapter existiert
        $oConnect->createChap(getCurrentNovel(), $iChap);
        $iChap++;
    }
    return $iChap;
}

function getChapUrl(int $iChap)
{
    return "http://www.wuxiaworld.com/novel/blue-phoenix/bp-chapter-$iChap/";
}

function getCurrentNovel()
{
    return $_SESSION['novel'] ?? 0;
}

function getCurrentChapter()
{
    return $_SESSION['chap'] ?? 1;
}

function printTypeSelect(DB_Connector $oConnect, $sID = 'type', $iSelectedID = 0)
{
    $aTypes = $oConnect->getAllErrorTypes();
    $aTypes[0] = ['Name' => 'Choose error type'];
    echo "<select name='$sID'>";
    foreach ($aTypes as $iTyp => $aTyp) {
        $sSelected = $iTyp == $iSelectedID ? 'Selected' : '';
        echo "<option value=$iTyp $sSelected>$aTyp[Name]</option>";
    }
    echo '</select>';
}

function printNovelSelect(DB_Connector $oConnect, $sId = 'novel', $iSelectedID = '0')
{
    echo "<select name='$sId' onchange='if(this.value != 0) {this.form.submit();}'>";
    echo '<option value=0>Please choose a chapter</option>';
    $aNovels = $oConnect->getNovels();
    foreach ($aNovels as $iNovelId => $sNovel) {
        echo "<optgroup label='$sNovel'>";
        $aChaps = $oConnect->getChapsFromNovel($iNovelId);
        foreach ($aChaps as $iChapId => $iChapNummer) {
            $sSelected = $iSelectedID == $iChapId ? 'selected' : '';
            echo "<option value=$iChapId $sSelected>$iChapNummer</option>";
        }
        echo '</optgroup>';
    }
    echo '</select>';
}

function isUnique($sNeedle, $sHaystack)
{
    return substr_count(stringToCleanString($sHaystack), stringToCleanString($sNeedle)) === 1;
}

function stringToShowString(string $sInput = null)
{
    return str_replace(array('&#10;', '\n'), '<br/>', html_entity_decode($sInput));
}

function stringToTextareaString(string $sInput = null)
{
    return str_replace(array('<br/>', '\n'), '&#10;', html_entity_decode($sInput));
}

function stringToCleanString(string $sInput = null)
{
    return str_replace(array('<br/>', '&#10;'), '\n', htmlentities($sInput));
}

function redirectNow(string $sLocation)
{
    $_SESSION['#disabled'] = true;
    header("location:$sLocation");
}

function setNewChap(DB_Connector $oConnect, $aArray, string $sKey = 'chap')
{
    if (!empty($_GET['newChap'])) {
        $iNewChap = $_GET['newChap']; //Neues Chap über Button gegeben
    }
    if (!$aArray) {
        $aArray = $_GET;
    }
    if (isset($aArray[$sKey])) {
        $iNewChap = $aArray[$sKey]; //Höhere Prio als über URL
    }
    if (isset($iNewChap)) {
        $aChap = $oConnect->getChapArrFromID($iNewChap);
        setNovel($aChap['Novels_ID']);
        setChapter($aChap['ChapNummer']);
    }
}

function markErrorInText(string $sText, array $aError)
{
    $sErrorMarkup = getErrorMarkup($aError);
    if (!isUnique($aError['original'], $sText)) {
        setMessage("Error '$aError[original]' is not unique or does not appear at all in this chapter.", 'error');
        return $sText;
    }
    return str_replace($aError['original'], $sErrorMarkup, $sText);
}

function getErrorMarkup(array $aError)
{
    $aInnerMarkup = getInnerErrorMarkup($aError);
    return "<span id=$aError[ID]>$aInnerMarkup</span>";
}

function getInnerErrorMarkup(array $aError)
{
    $sApply = $aError['Apply'] ? 'false' : 'true';
    $sErrorMarkup = "<span class='typo' title='Click to (de-)activate this error' style='background-color: $aError[Color]' onclick='setApply($aError[ID], $sApply); return false'>$aError[original]</span>";
    if ($aError['Apply']) {
        $sErrorMarkup .= " | <a class='corrected' title='$aError[Name]' href='EditError.php?id=$aError[ID]'>$aError[corrected]</a>";
        $sErrorMarkup .= $aError['Comment'] ? " <span class='comment'>[$aError[Comment]]</span>" : '';
    }
    return $sErrorMarkup;
}

function getCorrectedChapText($iChapNummer, $iNovelID, DB_Connector $oConnect, $bSilent = false, $bRemove = false, array $aIgnorieren = [])
{
    $sChapText = getChapText($iChapNummer);
    $iChapID = $oConnect->getChapID($iNovelID, $iChapNummer);
    $aErrors = $oConnect->getAllErrorsFromChap($iChapID);
    foreach ($aErrors as $aError) {
        if (!$aError['Apply'] || in_array($aError['ID'], $aIgnorieren, false)) {
            //Nur aktive Fehler eintragen
            continue;
        }
        if (!isUnique($aError['original'], $sChapText)) {
            if (!$bSilent) {
                setMessage('An Error occured while getting the corrected chaptext: An error was not unique!', 'error');
            }
            continue;
        }
        $sChapText = str_replace($aError['original'], $bRemove ? '' : $aError['corrected'], $sChapText);
    }
    return $sChapText;
}

function doDownloadAsTxt($sContent, $sFilename)
{
    header('Content-type: text/plain');
    header("Content-Disposition: attachment; filename=$sFilename");
    foreach (explode('\n', $sContent) as $sLine) {
        echo $sLine . "\r\n";
    }
    exit();
}

function isErrorInputOK($sOrig, $sRepl, $sChapText, $iTyp, DB_Connector $oConnect, $iID = null)
{
    $bError = false;
    $bUnique = true;
    if (empty($sOrig) || !isUnique($sOrig, $sChapText)) {
        setMessage('The original string needs to be unique in this chapter!', 'error');
        $bError = true;
        $bUnique = false;
    }
    if ($sOrig === $sRepl) {
        setMessage('There is no difference between the original and the replacement. Looks like you made a typo... SHAME ON YOU!!', 'error');
        $bError = true;
    }
    if (empty($sRepl)) {
        setMessage('The replacement needs to contain something. If you want to delete something, use some surrounding words as a buffer for both the original and the replacing string');
        $bError = true;
    }
    if (empty($iTyp)) {
        setMessage('Please choose an error type. This will be used to distinguish between errors when highlighting them.', 'error');
        $bError = true;
    }
    //Chaptext minus alle anderen Fehler holen //Nur, wenn es normalerweise unique wäre!
    $sChapText = getCorrectedChapText(getCurrentChapter(), getCurrentNovel(), $oConnect, false, true, $iID ? [$iID] : []);
    if ($bUnique && !isUnique($sOrig, $sChapText)) {
        setMessage('This error seems to overlap with some other error in this chapter. Please consider merging them into one!', 'error');
        $bError = true;
    }
    return $bError;
}

function printNextChapLink(DB_Connector $oConnect)
{
    $iNextChapNum = $oConnect->getNextChapIdWithError(getCurrentNovel(), getCurrentChapter());
    if (!($iNextChapNum > getCurrentChapter())) {
        //Letztes Chapter erreicht
        return;
    }
    echo "<a href='ViewChap.php?newChap=$iNextChapNum' class='button'>Next erroneous chapter</a>";
}
