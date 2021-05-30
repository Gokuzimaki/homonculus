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

// echo $host_pgateway_data['paystack']['defaultseckey'];

$cpaymode = $host_pgateway_data['paystack']['paymode'];

$defaultseckey = $cur_tran_key ?? $host_pgateway_data['paystack']['defaultseckey'];

$merror = $merror ?? "";

$paystack = new Yabacon\Paystack($defaultseckey);

// var_dump($paystack);
$modeop = varIndexRetriever("nwgappdata", "plain");

$dataoutextras['paymode'] = "paystack";

// the code below throws an exception if there was a problem completing the
// request, else returns an object created from the json response
// convert amount to kobo/cents
$amount = $amount * 100;
$reference = substr(sha1(date("Y-m-d H:i:s")), 0, 20);

$metadata = [
	'cart_id' => $cartid,
	'retparams' => $retparams,
	'troid' => $troid,
	'coupid' => $coupid,
	'modeop' => $modeop,
	'doreceipt' => $doreceipt??false,
	'returl' => $returl??"",
	'uh' => $uh
];
// full sample initialize response
/*{
	  "status": true,
	  "message": "Authorization URL created",
	  "data": {
	    "authorization_url": "https://standard.paystack.co/pay/0peioxfhpn",
	    "access_code": "0peioxfhpn",
	    "reference": "7PVGX8MEk85tgeEpVDtD"
	  }
}*/

// create transaction rudiment options

// for SDK
$paystackinitarrsdk = [
	'amount' => $amount,
	'email' => $email,
	'callback_url' => '' . $host_addr . 'snippets/modules/paystack/verify.php?mode='.$modeop.'&rtpath='.($cur_user_type ?? "").'&th='.($cur_acc_id ?? ""),
	'metadata' => json_encode($metadata),
];
// for CURL
$paystackinitarrcurl = [
	'amount' => $amount,
	'email' => $email,
	'callback_url' => '' . $host_addr . 'snippets/modules/paystack/verify.php?mode='.$modeop.'&rtpath='.($cur_user_type ?? "").'&th='.($cur_acc_id ?? ""),
	'metadata' => json_encode($metadata),
];


// Use a generated reference value if available
if(isset($referenceid) && $referenceid !== ""){
	$paystackinitarrsdk['reference'] = $referenceid;
	$paystackinitarrcurl['reference'] = $referenceid;
}

// use the paystack sdk
if($cpaymode == "SDK"){
	try {

		$trx = $paystack->transaction->initialize($paystackinitarrsdk);

	} catch (\Yabacon\Paystack\Exception\ApiException $e) {
		$processproceed = false;
		// var_dump($e);
		$errmsg = print_r($e->getResponseObject(), TRUE)  ." <br> ". $e->getMessage();

		if($modeop == "json"){
			$success = false;

		}else{

			// print_r($e->getResponseObject());
			// die($e->getMessage());
			
			$returl = ($returl ?? $host_addr."completion/")."?t=checkout&type=checkout&sf=failure&rf=&ci=&uh=$uh&gateway=failure&msgout=$errmsg";
			$merror = $errmsg;
			$success = false;
			$actionstatus = "failure";
			 
			// stop notification table update
			$nonottype = "none";
		}
	}
}


// use the paystack curl endpoints 
if($cpaymode == "CURL"){
	
	$method = "POST";
	$url = "https://api.paystack.co/transaction/initialize";
	
	$sortdata = array();
	$sortdata['http_headers'] = array();
	
	$sortdata['http_headers']["status"] = "true";
	
	$sortdata['http_headers']["headers"] = array();
	$headsets = [
		"Authorization: Bearer {$defaultseckey}" ,
		"Cache-Control: no-cache"
	];

	$sortdata['http_headers']["headers"] = $headsets;

	$data = $paystackinitarrcurl;

	$curldata = CallCurl($method, $url, $sortdata, $data);
	
	if ($curldata['result'] === false) {
		$processproceed = false;

		$errmsg = "An error occured, details are as follows:<br>".print_r($curldata,TRUE);

		// echo "Curl Output Error<br><br><br>$errmsg";
		// var_dump($curldata);
	}else{
		// var_dump($curldata['result']);

		$trx = json_decode($curldata['result']);

		// print_r($returl);
	}
}

// status should be true if there was a successful call
if (isset($trx) && !$trx->status && $processproceed == true) {

	$processproceed = false;
	$errmsg = $trx->message ?? "$errmsg";
}

// exit();

$success = $processproceed;
$actiontime = 8000;

// update transaction data on suuccess
if($processproceed == true){
	
	// store the current transaction refid in the cartdata table under the 
	// current cartdata entry if
	$trid = $trx->data->reference;

	// Get the user to click link to start payment or simply redirect to
	// the url generated
	$returl = $trx->data->authorization_url;

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

	// for data failure
	$actionstatus = "fail";
	$actiontitle ="An Error Occured";
	$actiondetails = "Apologies, we could not initiate your transaction, we advice you try again. Thank you.<br>$errmsg";
	if($modeop !== "json"){
		$returl = ($returl ?? $host_addr."completion/")."?t=checkout&type=checkout&sf=failure&rf=&ci=&uh=$uh&gateway=failure&msgout=Could Not initiate the current transaction.$errmsg";

		// stop notification table update
		$nonottype = "none";
	}

}



?>