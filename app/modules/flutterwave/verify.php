<?php
// bring in connection.php if it doesn't exist
include '../../connection.php';
// so we can get @ session variables
if (session_id() == '') {
	session_start();
}
// Confirm that reference has not already gotten value
// This would have happened most times if you handle the charge.success event.
// If it has already gotten value by your records, you may call
// perform_success()

// Get this from https://github.com/yabacon/flutterwave-class
// require 'flutterwave.php';

// test for a $refdata variable and use that as the base for verification instead
// $_GET url parameter.

$defaultseckey = $host_pgateway_data['flutterwave']['defaultseckey'];

$trefdata = varIndexRetriever('tx_ref', "");

$tridata = varIndexRetriever('transaction_id', "");

$th = varIndexRetriever('th', 0);
if ($th > 0) {

	// this is a sub-account transaction
	$syssettings = getAppSettings($th);
	
	// var_dump($syssettings);
	// get the flutterwave id-
	$cursetdata = $syssettings['settings'] ?? [];

	$payments = $cursetdata['payments'];

	$flutterwave = $payments["flutterwave"] ?? [];

	$sec_key = $flutterwave["secret_key"] ?? "";

	// change the current secret key
	if ($sec_key !== "") {
		$defaultseckey = $sec_key;
	}

	//////////////////////////////////////////
	// Default Redirection settings control //
	//////////////////////////////////////////
	$istest = varIndexRetriever("rtpath", null);

	if ($istest !== null) {
		if ($istest == "admin") {
			$returl = "${host_addr}admin/adminindex";
		} else {
			$dashhead = $istest == "staff" ? "security" : $istest;

			$returl = "${host_addr}${dashhead}dashboard/";
		}
	}

}

$refdata = $refdata ?? $trefdata;

$cpaymode = $host_pgateway_data['flutterwave']['paymode'];

$success = true;
$msg = "";

$cartid = "";

$uh = "";
$troid = "";
$doreceipt = true;
$updgrade = "";
$coupid = "";
$entryvariant = "updatetransactionflutterwave";

$retparams = "";

$dataoutextras = $dataoutextras ?? [];

$returl = varIndexRetriever('returl', null);
$appendedparams = "";
$trx_data_transaction_date = strtotime(date("Y-m-d H:i:s"));

$modeop = varIndexRetriever("nwgappdata", "");
$modeop = $modeop == "" ? varIndexRetriever("modeop", "plain") : $modeop;

$trx_data_status = "";

if ($cpaymode == "CURL") {
	$method = "GET";
	// $url = "https://api.flutterwave.com/v3/transactions/$refdata/verify";
	$url = "https://api.flutterwave.com/v3/transactions/$tridata/verify";
	// echo $url;
	$sortdata = array();
	$sortdata['http_headers'] = array();

	$sortdata['http_headers']["status"] = "true";

	$sortdata['http_headers']["headers"] = array();
	$headsets = [
		"Authorization: Bearer {$defaultseckey}",
	];

	$sortdata['http_headers']["headers"] = $headsets;
	$data = [];

	$curldata = CallCurl($method, $url, $sortdata, $data);

	if ($curldata['result'] === false) {
		$success = false;
		$msg = "An error occured, details are as follows:<br>" . print_r($curldata, TRUE);
		// echo "Curl Output Error<br><br><br>$msg";
		// var_dump($curldata);
	} else {
		// var_dump($curldata['result']);
		$trx = json_decode($curldata['result']);
		// print_r($trx);
	}
}

// exit();
// status should be true if there was a successful call
if ($trx->status !== "success" && $success == true) {
	$success = false;
	$msg = $trx->message;
}

if ($modeop !== "json" && $success == false) {

	// print_r($e->getResponseObject());
	// die($e->getMessage());
	$returl = ($returl ?? $host_addr . "completion/") . "?t=checkout&sf=failure&rf=&ci=&uh=$uh&gateway=failure&msgout=$msg";
	$actionstatus = "fail";
	$actiontitle = "An Error Occured!!!";
	$actiondetails = "$msg";
	// stop notification table update
	$nonottype = "none";
}

// process transaction updates and value adding for purchases
if ($success == true) {
	// check if the current reference has already been updated in the cartdata
	// table
	$cartid = $trx->data->cart_id  ?? varIndexRetriever('cart_id','');

	$doreceipt = $trx->data->doreceipt  ?? varIndexRetriever('doreceipt',false) ?? $doreceipt;

	$trx_data_transaction_date = $trx->data->created_at;

	$trx_data_status = $trx->status;

	$actiontitle = "Verification Successful";

	$msg = $trx->message;


	$uh = $trx->data->uh ?? varIndexRetriever('uh','');

	$troid = $trx->data->troid ?? varIndexRetriever('troid','');

	$updgrade = $troid !== ""? $troid: "";
	$coupid = $trx->data->coupid ?? varIndexRetriever('coupid','');
	$modeop = $trx->data->modeop ?? "plain";

	$retparams = $trx->data->retparams ?? varIndexRetriever('retparams','');

	$tq = "SELECT * FROM cartdata WHERE (refid='$refdata' OR refid='$tridata') AND id='$cartid' AND
		MD5(uid)='$uh'";
	// echo "<br>TRQUERY: $tq<br>";
	$tqdata = briefquery($tq, __LINE__, "mysqli");
	$truid = $tqdata['resultdata'][0]['uid'];

	$cartdata = getSingleCartData($tqdata['resultdata'][0]['id'],"",["row"=>$tqdata['resultdata'][0]]);
	$numrows = $tqdata['numrows'];

	$dataoutextras['coupid'] = $coupid;
	$dataoutextras['cartid'] = $cartid;
	$dataoutextras['cartdata'] = $cartdata;
	$dataoutextras['uh'] = $uh;
	$dataoutextras['troid'] = $troid;

	$cartdataoutput = "";
	if ('success' === $trx_data_status && $numrows > 0) {
		$cartdata = getSingleCartData($cartid, "", ["row" => $cartdata]);
		$dataoutextras['cartdata'] = $cartdata;

		for ($i = 0; $i < $cartdata['cartcount']; $i++) {

			$itemdata = $cartdata['cartitems'][$i];

			$citemtitle = $itemdata['itemtitle'];
			$amount = $itemdata['amount'];

			$cartdataoutput .= 'Item: ' . $citemtitle . '. <br>
				Cost:' . $cartdata['cartcurrency'] . ' ' . $amount . '<br><br>';
		}

		// use trx info including metadata and session info to confirm that cartid
		// matches the one for which we accepted payment

		give_value($refdata, $trx);

		// $data['returl']="completion.php";
		perform_success("success");
		
		$actiontitle = "Payment Successful";
		$actionstatus = "successful";
		$actiontime = 120000;
		$actiondetails = "The payment transaction was successful, check your transactions menu for payment information and or receipt.";
	} else {
		$success = false;
		perform_success("failure");
		$actiontitle = "Error validating Transaction";
		$actionstatus = "fail";
		$actiondetails = "$msg";

	}
}

$actiondetails = $actiondetails ?? $msg;

// var_dump($defaultseckey);
// var_dump($success);
// exit();

// functions
function give_value($reference, $trx) {
	global $troid, $host_default_pgateway, $tridata, $cartdata;
	// Be sure to log the reference as having gotten value
	// write code to give value
	// $tqdata = $GLOBALS['tqdata']['resultdata'][0];
	// var_dump($tqdata);
	// var_dump($trx);
	$dt = strtotime($trx->data->created_at);

	$paymentdate = date('Y-m-d H:i:s', $dt);

	// echo $paymentdate;
	// update the ref status for the transaction
	// echo "ter1";
	// echo $GLOBALS['cartdata']['refstatus']." ".$trx->data->status;

	if ($GLOBALS['cartdata']['refstatus'] !== $trx->data->status) {
		$trx_data_status = $trx->data->status == "successful" ? "success" : $trx->data->status;

		// genericSingleUpdate("cartdata", "refid", $tridata, "id", $GLOBALS['cartid']);
		genericSingleUpdate("cartdata", "refstatus", $trx_data_status, "id", $GLOBALS['cartid']);
		genericSingleUpdate("cartdata", "apitype", "$host_default_pgateway", "id", $GLOBALS['cartid']);

		// update transaction date
		// echo "ter2";
		genericSingleUpdate("cartdata", "paymentdate", "$paymentdate", "id", $GLOBALS['cartid']);

		// update the transaction table as well
		// genericSingleUpdate("transaction", "transactionrefid", $tridata, "cartid", $GLOBALS['cartid']);
		genericSingleUpdate("transaction", "transactionstatus", $trx_data_status, "cartid", $GLOBALS['cartid']);
		genericSingleUpdate("transaction", "transactiontime", "$paymentdate", "cartid", $GLOBALS['cartid']);
		genericSingleUpdate("transaction", "apitype", "$host_default_pgateway", "cartid", $GLOBALS['cartid']);

		// update the coupon data
		genericSingleUpdate("transaction", "coupid", "$GLOBALS[coupid]", "cartid", $GLOBALS['cartid']);

		if ($GLOBALS["coupid"] !== "" && $GLOBALS["coupid"] > 0) {
			// update the package coupon use table data
			$q = "INSERT INTO pckgcpused(pckgcpid,uid,entrydate) VALUES('$GLOBALS[coupid]','$GLOBALS[troid]','$paymentdate')";
			$cqdata = briefquery($q, __LINE__, "mysqli", true);

		}

		// process the cart data item by item and attempt to store static elements
		// with countable units present in it in the appropriate table.
		if ($GLOBALS['cartdata']['orgid'] > 0) {
			activateRewardableTransactionAlerts($GLOBALS['cartid']);
		} else {
			staticPckgManagement($GLOBALS['cartid']);
		}

		// process the current entry for the productsandservices table

		// disable the prevous transaction if the 'troid' get parameter is
		// available
		if ($troid !== "" && $GLOBALS['cartdata']['orgid'] < 1) {
			// echo "herr";
			genericSingleUpdate("transaction", "status", "expired", "MD5(id)", "$troid");
			genericSingleUpdate("transaction", "enddate", date("Y-m-d H:i:s"), "MD5(id)", "$troid");
		}

	}

	// carry out secondary data updates in the event of package purchases
	// that require

}

function perform_success($sucfail = "success", $data = array()) {
	global $host_tpathplain, $dataoutextras, $host_website_name, $refdata, $cartid, $cartdata, $cartdataoutput, $returl, $troid, $coupid, $trdata, $retparams, $uh, $modeop, $msg, $success, $host_website_url, $doreceipt;

	$_SESSION['lastsuccess'] = 0;
	$extradata = [];

	if ($modeop == "json") {
		// this means that the current operation is part of an ajax request
		// therefore we initalise the nwtransaction variable to ensure the
		// redirection script treats this as such
		$nwtransaction = "json";
	}

	// inline
	// echo json_encode(['verified'=>true]);
	$refid = $refdata;

	$dataoutextras['refid'] = $refid;
	// var_dump($cartid);

	$trdata = getSingleTransactionData($troid);
	$transref = $trdata['transactionrefid'] ?? "";
	$trpackage = isset($trdata['transactionrefid']) ? $trdata : "";
	$itemtitle = $trdata['itemtitle'] ?? "";
	$uh = $GLOBALS['uh'];

	$returl = $GLOBALS['returl'] ?? $GLOBALS['host_addr'] . "completion/";
	if (isInString("index?error", $returl)['instring'] == true) {
		$returl = $GLOBALS['host_addr'] . "completion/";
	}

	$GLOBALS['appendedparams'] = "&t=checkout&$retparams&sf=$sucfail&rf=$refid&ci=$cartid&uh=$uh&gateway=flutterwave&utm_campaign=checkout&utm_package=$itemtitle&utm_transref=$transref";
	$GLOBALS['returl'] = $returl;

	

	if ($sucfail == "success" && $cartid !== "") {

		$paymentdetails = json_encode(["staticprocessed" => "processed", "paytype" => "complete", "paylog" => []]);

		$extradata['cartid'] = $cartid;
		$extradata['cartdata'] = $cartdata;

		$extradata['pudata'] = getSingleUserPlain($uh);

		$customername = $extradata['pudata']["nameout"];
		$email = $extradata['pudata']['email'];
		// sendout an email to the user with their receipt
		$title = "Your Purchase on " . strtoupper($host_website_name);
		$content = '
				<p style="text-align:left;">
					Hi there, ' . $customername . '<br>
					Your Payment on the ' . $host_website_name . ' platform,
					 for the following;
					<br>' . $cartdataoutput . '<br>
					 On, ' . date('D, d F, Y h:i:s A',
			strtotime($cartdata['entrydate'])) . '
					 	was successful.<br>
				</p>
				<p style="text-align:right;">Thank You.</p>
			';

		$footer = '

			';

		// // echo $emailout['rowmarkup'];
		$toemail = "$email";
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: '.$host_website_name.' <no-reply@' . $host_website_url . '>' . "\r\n";

		$subject = $title;

		// send out the email
		$mailtype = "defaultuserreceipt";
		$maildata = array();
		$maildata['headers'] = $headers;
		$maildata['title'] = $subject;
		$maildata['toemail'] = $toemail;
		if ($doreceipt == true) {

			$mailer = sendGenMail($mailtype, $maildata, $extradata);
		}

	}

}
// echo "<br>Return to: $returl$appendedparams<br>";
// exit();

// echo "<br>Action status: $actionstatus<br>";
// echo "<br>Action title: $actiontitle<br>";
// echo "<br>Action Details: $actiondetails<br>";

// $notrdtype = "none"; //stops redirection



$nonottype = "none"; // stops notification table update

// bring in the nrsection.php file
include $host_tpathplain . 'nrsection.php';

// echo "<br>Return to: $returl<br>";

exit();

// Full sample response here
/*
	{
	  "status": "success",
	  "message": "Transaction fetched successfully",
	  "data": {
	    "id": 1163068,
	    "tx_ref": "akhlm-pstmn-blkchrge-xx6",
	    "flw_ref": "FLW-M03K-02c21a8095c7e064b8b9714db834080b",
	    "device_fingerprint": "N/A",
	    "amount": 3000,
	    "currency": "NGN",
	    "charged_amount": 3000,
	    "app_fee": 1000,
	    "merchant_fee": 0,
	    "processor_response": "Approved",
	    "auth_model": "noauth",
	    "ip": "pstmn",
	    "narration": "Kendrick Graham",
	    "status": "successful",
	    "payment_type": "card",
	    "created_at": "2020-03-11T19:22:07.000Z",
	    "account_id": 73362,
	    "amount_settled": 2000,
	    "card": {
	      "first_6digits": "553188",
	      "last_4digits": "2950",
	      "issuer": " CREDIT",
	      "country": "NIGERIA NG",
	      "type": "MASTERCARD",
	      "token": "flw-t1nf-f9b3bf384cd30d6fca42b6df9d27bd2f-m03k",
	      "expiry": "09/22"
	    },
	    "customer": {
	      "id": 252759,
	      "name": "Kendrick Graham",
	      "phone_number": "0813XXXXXXX",
	      "email": "user@example.com",
	      "created_at": "2020-01-15T13:26:24.000Z"
	    }
	  }
	} 
*/
?>