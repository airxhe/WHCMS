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

require "../../../init.php";
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("cyberbit");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$hash = $_REQUEST['Hash'];
$xml = $_REQUEST['xml'];
$invoiceid = $OrderId = $_REQUEST['OrderId'];
$StatusCode = $_REQUEST['StatusCode'];
$StatusText = $_REQUEST['StatusText'];
$Time = $_REQUEST['Time'];
$invoiceid = explode("-", $invoiceid);
$invoiceid = $invoiceid[1];
$invoiceid = checkCbInvoiceID($invoiceid, "CyberBit");
$whmcs->load_function("gateway");
$fingerprint = sha1($StatusCode . $StatusText . $OrderId . $Time . $GATEWAY['hashkey']);

if ($fingerprint != $hash) {
	logTransaction("CyberBit", $_REQUEST, "Invalid Hash");
	redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
}


if ($StatusCode == "000") {
	logTransaction("CyberBit", $_REQUEST, "Successful");
	addInvoicePayment($invoiceid, $OrderId, "", "", "cyberbit");
	$result = select_query("tblinvoices", "userid", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	update_query("tblclients", array("gatewayid" => $OrderId), array("id" => $userid));
	redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	return 1;
}

logTransaction("CyberBit", $_REQUEST, "Unsuccessful");
redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
?>