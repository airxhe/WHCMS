<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!$days) {
	$days = 0;
}


if (!$expires) {
	$expires = date("YmdHis", mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $days, date("Y")));
}

$banid = insert_query("tblbannedips", array("ip" => $ip, "reason" => $reason, "expires" => $expires));
$apiresults = array("result" => "success", "banid" => $banid);
?>