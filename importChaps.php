<?php
require_once 'functions.php';
require_once 'DB_Connector.php';
importAllChaps(new DB_Connector());
redirectNow('index.php');
