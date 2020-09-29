<?php
class dbSetup {
    function __construct()
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/rb-mysql.php');
        R::setup('mysql:host=localhost;dbname=burza', 'root', '');
    }
}
