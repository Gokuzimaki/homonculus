<?php
// bring in the connection.php file
if (!function_exists('briefquery')) {
	include '../../connection.php';
}
// create a refid and update the current cart with that data
$refid = simpleCodeGen(10)['modhashcode'];
$debuginfo = "";
$uh = md5($cudata['id']);
// var_dump($uh);
// send a request to the invoice generation endpoint
$vars = [];
$vars['displaytype'] = "invoicepdfgen";
$vars['cartid'] = $cartdata['id'];
$vars['cartd'] = $cartdata['id'];
$vars['mode'] = "invoice";
$vars['outs'] = "email";
$vars['refid'] = "$refid";
$vars['uh'] = "$uh";

$curldata['vars'] = $vars;
$fpath = "${host_addr}sysrestdefault/";
// $fpath = "${host_addr}snippets/display.php";

$curldata = CallCurl("POST", $fpath, [], $curldata);

$output = $curldata['result'];

$paymentdate = date("Y-m-d H:i:s");

$sucfail = $output == "" ? "success" : "failure";

$errout = "$output";

if ($sucfail == "success") {

	// thie means the invoice was generated and sent successfully
	// update the transaction refid
	genericSingleUpdate("cartdata", "refid", "$refid", "id", "$cartid");
	genericSingleUpdate("transaction", "transactionrefid", "$refid", "cartid", $cartid);
	
	// update the transaction status
	genericSingleUpdate("cartdata", "refstatus", "pending", "id", $cartid);
	genericSingleUpdate("transaction", "transactionstatus", "pending", "cartid", $cartid);

	if (isset($debug) && $debug == true) {
		$debuginfo .= "<br>--------------------------------Cart Data--------------------------------<br>".json_encode(stripExcessIndexes(getSingleCartData($cartid)))."<br>"; 
	}
} else {
	$errout = $output;
	if (isset($debug) && $debug == true) {
		$debuginfo .= "<br>-------------------------------- Operation Error --------------------------------<br>".$output."<br>"; 
	}
}

// setup redirection url
$returl = isset($returl) && $returl !== "" ? $returl: $GLOBALS['host_addr'] . "completion/?t=checkout&$retparams&sf=$sucfail&rf=$refid&ci=$GLOBALS[cartid]&uh=$uh&gateway=$GLOBALS[gateway]&errout=$errout";

$notid = $GLOBALS['cartid'];

// prepare content for the notification redirect portion
$action = "Create";
$actiontype = "cartdata";
$actiontitle = "Invoice Created";
$actiondetails = "Invoice Transaction Entry Successful for " . $cudata['nameout'].". Payment for the invoice must be logged from Transactions or Pending Dues, then approved by administration before value is given".($debuginfo ?? "");

///////////////////////////////////
// Debug operation return values //
///////////////////////////////////
if (isset($debug) && $debug == true) {
	$notrdtype = "none"; //stops redirection

	$nonottype = "none"; // stops notification table update

	$docronupdate = false; // stops cronalerts table update

	doRowDeleteByID($notid,'cartdata');

	// delete current transaction entries
	$deletequery = "cartid = '$notid'";
	$dodeletequery = doDeleteByQuery($deletequery, "transaction");

	if (isset($nwdata) && $nwdata !== null && $nwdata !== "") {
		$notrdtype = ""; //stops redirection
		$dataoutextras['debug'] = "Debug Action Details:<br>$actiondetails
		<br>Return URL: $returl
		<br>Notid: $notid
		<br>ProcessFlow: $processflow
		<br>gateway: $gateway
		";
	} else {
		//  if this is not an ajax based request, echo the debug information
		echo "Debug Action Details:<br>$actiondetails
		<br>Return URL: $returl
		<br>Notid: $notid<br>
		<br>ProcessFlow: $processflow<br>
		<br>gateway: $gateway<br>
		";
		// var_dump($dataoutextras);
	}
}
// $nonottype="none";// stops notification table update
// $notrdtype="none";//stops redirection
include 'nrsection.php';
exit();
?>