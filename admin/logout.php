<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$auth = new WHMCS_Auth();

if ($auth->logout()) {
	redir("logout=1", "login.php");
}

redir("", "login.php");
?>