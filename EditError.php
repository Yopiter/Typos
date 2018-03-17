<?php

if (!isset($_GET['id'])) {
    setMessage('No typo given to edit...', 'error');
    redirectNow('ViewChap.php');
}
