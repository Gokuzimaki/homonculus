<?php
// bring in the connection.php file
// this file handles submission data to paystack
// kudos to https://github.com/yabacon/paystack-class
// for the class
// require 'Paystack.php';
if (!function_exists('briefquery')) {
	include_once '../../connection.php';
}

$processproceed = true;

$defrate = $host_dollar_rate;

// echo $host_pgateway_data['flutterwave']['defaultseckey'];

$cpaymode = $host_pgateway_data['flutterwave']['paymode'];

$defaultseckey = $cur_tran_key ?? $host_pgateway_data['flutterwave']['defaultseckey'];

$merror = $merror ?? "";

$errmsg = "";

// var_dump($paystack);
$modeop = varIndexRetriever("nwgappdata", "plain");

$dataoutextras['paymode'] = "flutterwave";

// the code below throws an exception if there was a problem completing the
// request, else returns an object created from the json response
// convert amount to kobo/cents
// $amount = $amount * 100;
$reference = substr(sha1(date("Y-m-d H:i:s")), 0, 20);

$trx = json_decode("[]"); 

$metadata = [
	'cart_id' => $cartid,
	'retparams' => $retparams,
	'troid' => $troid,
	'coupid' => $coupid,
	'modeop' => $modeop,
	'doreceipt' => $doreceipt ?? false,
	'returl' => $returl??"",
	'uh' => $uh
];
// store metadata url parameters
$metaparams = "&mode=$metadata[modeop]&retparams=$metadata[retparams]&cart_id=$metadata[cart_id]&troid=$metadata[troid]&coupid=$metadata[coupid]&doreceipt=$metadata[doreceipt]&uh=$metadata[uh]&returl=".urlencode($metadata["returl"])."";

// for CURL
$flutterwaveinitarrcurl = [
	'amount' => $amount,
	'currency' => $currency,
	"payment_options" => "card",
	'customer' => [
		"name" => "$cudata[nameout]",
		"email" => "$email",
		"phonenumber" => "$cudata[phonenumber]"
	],
	"customizations"=>[
		"title"=>$cur_acc_nameout ?? $host_website_name,
		"description"=>$cur_acc_desc ?? $host_slogan??"",
		"logo"=>$cur_acc_logo ?? $host_badge_image ?? $host_logo_large_small_image
	],
	'redirect_url' => '' . $host_addr . 'snippets/modules/flutterwave/verify.php?rtpath='.($cur_user_type ?? "").'&th='.($cur_acc_id ?? "").$metaparams,
	'meta' => json_encode($metadata),
];


// Use a generated reference value if available
$referenceid = isset($referenceid) && $referenceid !== "" ? $referenceid : simpleCodeGen(11)["modhashcode"];

$flutterwaveinitarrsdk['tx_ref'] = $referenceid;
$flutterwaveinitarrcurl['tx_ref'] = $referenceid;



if(isset($debug) && $debug == true){
	
	if($nwdata == "" || $nwdata == null){
		arrayVomit($flutterwaveinitarrcurl);
	}

	// echo "<br>";
}

// use the paystack curl endpoints 
if($cpaymode == "CURL"){
	
	$method = "POST";
	$url = "https://api.flutterwave.com/v3/payments";
	
	$sortdata = array();
	$sortdata['http_headers'] = array();
	
	$sortdata['http_headers']["status"] = "true";
	
	$sortdata['http_headers']["headers"] = array();
	$headsets = [
		"Authorization: Bearer {$defaultseckey}" ,
		"Content-Type: application/json",
		"Cache-Control: no-cache"
	];

	$sortdata['http_headers']["headers"] = $headsets;

	$data['curl_merged_fields'] = json_encode($flutterwaveinitarrcurl);


	$curldata = CallCurl($method, $url, $sortdata, $data);
	
	if ($curldata['result'] === false) {
		$processproceed = false;

		$errmsg = $curldata['error'];

		// echo "Curl Output Error<br><br><br>$errmsg";
		// var_dump($curldata);
	}else{
		// var_dump($curldata['result']);

		$trx = json_decode($curldata['result']);

		// print_r($returl);
	}
}

// status should be true if there was a successful call
if (isset($trx->status) && $trx->status !== "success" && $processproceed == true) {

	$processproceed = false;
	$errmsg .= "\r\n".$trx->message;
}

if(!isset($trx->data) && $processproceed == true){
	$processproceed = false;

	$errmsg .= "\r\n".$trx->message;

}

// exit();

$success = $processproceed;
$actiontime = 8000;

// update transaction data on suuccess
if($processproceed == true){
	
	// store the current transaction refid in the cartdata table under the 
	// current cartdata entry if
	$trid = $trx->data->tx_ref ?? $referenceid;

	// Get the user to click link to start payment or simply redirect to
	// the url generated
	$returl = $trx->data->link;

	// update the cart data table with the retrieved transaction reference
	genericSingleUpdate("cartdata", "refid", "$trid", "id", "$cartid");

	// update individual transaction data per database items
	genericSingleUpdate("transaction", "transactionrefid", "$trid", "cartid", "$cartid");

	if ($modeop == "json") {
		// bring in the redirection handler since this is an ajax
		// operation
		$dataoutextras['refid'] = $trid;
		$dataoutextras['redirect_url'] = $returl;

	}

}else{
	
	$success = false;
	
	$nonottype = "none";
	$errmsgout = $errmsg !== "" ? "Error(s):<br> $errmsg":"";
	// for data failure
	if($modeop !== "json"){
		$actionstatus = "fail";
		$actiontitle ="An Error Occured";
		$actiondetails = "Apolgies, we could not initiate your transaction, we advice you try again. Thank you. \r\n <br>$errmsgout";

		$returl = ($returl ?? $host_addr."completion/")."?t=checkout&type=checkout&sf=failure&rf=&ci=&uh=$uh&gateway=failure&msgout=Could Not initiate the current transaction.$errmsgout";

		// stop notification table update
		$nonottype = "none";
	}

}
?>