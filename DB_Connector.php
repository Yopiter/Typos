<?php

class DB_Connector
{
    private $oConnection;

    public function __construct(string $sHost = 'localhost', string $sDB = 'Typos', string $sUser = 'admin', string $sPw = 'admin')
    {
        $this->oConnection = mysqli_connect($sHost, $sUser, $sPw, $sDB);
        if (!$this->oConnection) {
            die("Keine Verbindung zur Datenbank $sDB auf Host $sHost!");
        }
    }

    public function getNovels(): array
    {
        $sSQL = 'SELECT ID, Name FROM EP_Novels';
        $oResult = mysqli_query($this->oConnection, $sSQL);
        if (!$oResult) {
            setMessage('An error occured while fetching the novels: ' . mysqli_error($this->oConnection), 'error');
            return [0 => 'Error'];
        }
        $aReturn = [];
        while ($aRow = mysqli_fetch_array($oResult)) {
            $aReturn[$aRow['ID']] = $aRow['Name'];
        }
        return $aReturn;
    }

    public function getChapsFromNovel(int $iNovelId): array
    {
        $sSql = "Select ID, ChapNummer from EP_Chapters Where Novels_ID=$iNovelId ORDER BY ChapNummer ASC";
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An error occured while fetching the chapters: ' . mysqli_error($this->oConnection), 'error');
            return [0 => 'Error'];
        }
        $aChaps = [];
        while ($aRow = mysqli_fetch_array($oResult)) {
            $aChaps[$aRow['ID']] = $aRow['ChapNummer'];
        }
        return $aChaps;
    }

    public function getChapArr(int $iNovel, int $iChap): array
    {
        $sSql = "SELECT * From EP_Chapters where Novels_ID = $iNovel AND ChapNummer=$iChap";
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An error occured while fetching the novels: ' . mysqli_error($this->oConnection), 'error');
            return null;
        }
        $aResult = mysqli_fetch_array($oResult);
        if (!$aResult) {
            setMessage("Novel $iNovel does not have a chapter $iChap.", 'error');
            return null;
        }
        return $aResult;
    }

    public function getAllChapsFromNovel(int $iNovelID = 0): array
    {
        $sSql = "SELECT * FROM EP_Chapters WHERE Novels_ID=$iNovelID";
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An error occured while fetching all chapters: ' . mysqli_error($this->oConnection), 'error');
            return null;
        }
        $aResult = [];
        while ($aRow = mysqli_fetch_array($oResult)) {
            $aResult[$aRow['ChapNummer']] = $aRow;
        }
        return $aResult;
    }

    public function getChapArrFromID(int $iChapID): array
    {
        $sSql = "SELECT * From EP_Chapters where ID=$iChapID";
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An error occured while fetching the novels: ' . mysqli_error($this->oConnection), 'error');
            return null;
        }
        $aResult = mysqli_fetch_array($oResult);
        if (!$aResult) {
            setMessage("Chapter $iChapID not found.", 'error');
            return null;
        }
        return $aResult;
    }

    public function doArbitrarySQL(string $sSQL)
    {
        return mysqli_query($this->oConnection, $sSQL);
    }

    public function getNovelName(int $iNovelID)
    {
        $sSql = "SELECT Name FROM EP_Novels Where ID=$iNovelID";
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An error occured while fetching the novels name: ' . mysqli_error($this->oConnection), 'error');
            return 'error';
        }
        $aResult = mysqli_fetch_array($oResult);
        if (!$aResult) {
            setMessage("No Novel with the ID of $iNovelID was found.", 'error');
            return 'error';
        }
        return $aResult['Name'];
    }

    public function getAllErrorTypes()
    {
        $sSql = 'SELECT * FROM EP_ErrorTypes';
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An error occured while fetching the novels name: ' . mysqli_error($this->oConnection), 'error');
            return 'error';
        }
        $aResult = [];
        while ($aRow = mysqli_fetch_array($oResult)) {
            $aResult[$aRow['ID']] = $aRow;
        }
        return $aResult;
    }

    public function getChapID(int $iNovelID, int $iChapNummer, bool $bSilent = false)
    {
        $sSql = "SELECT ID FROM `EP_Chapters` Where Novels_ID=$iNovelID AND ChapNummer=$iChapNummer";
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An error occured while fetching the Chapter-ID: ' . mysqli_error($this->oConnection), 'error');
            return null;
        }
        $aResult = mysqli_fetch_array($oResult);
        if (!$aResult) {
            if (!$bSilent) {
                setMessage("Novel $iNovelID does not seem to have a chapter $iChapNummer.", 'error');
            }
            return false;
        }
        return $aResult['ID'];
    }

    public function createError(int $iChapID, string $sOrig, string $sReplace, string $sComment, int $iErrorType): bool
    {
        $sSql = "Insert into EP_Errors (`ID`,`Chaper_ID`,`original`,`corrected`,`type`,`Comment`) VALUES (NULL,$iChapID,'$sOrig','$sReplace',$iErrorType,'$sComment')";
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An (database-, not typo-) error occured while saving this error: ' . mysqli_error($this->oConnection), 'error');
            return false;
        }
        return true;
    }

    public function createChap(int $iNovelID, int $iChapNummer): bool
    {
        if ($this->getChapID($iNovelID, $iChapNummer, true) !== false) {
            return true;
        }
        $sSql = "INSERT INTO `EP_Chapters` (`ID`, `ChapNummer`, `Speicherort`, `Novels_ID`) VALUES (NULL, '$iChapNummer', '', '$iNovelID')";
        $oResult = mysqli_query($this->oConnection, $sSql);
        if (!$oResult) {
            setMessage('An error occured while saving this chapter: ' . mysqli_error($this->oConnection), 'error');
            return false;
        }
        return true;
    }
}
