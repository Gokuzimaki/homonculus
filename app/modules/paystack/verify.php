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

// Get this from https://github.com/yabacon/paystack-class
// require 'Paystack.php';

// test for a $refdata variable and use that as the base for verification instead 
// $_GET url parameter.

$defaultseckey = $host_pgateway_data['paystack']['defaultseckey'];

$trefdata =  varIndexRetriever('reference',"");

$th =  varIndexRetriever('th',0);
if($th > 0){

	// this is a sub-account transaction
	$syssettings = getAppSettings($th);
	/*foreach ($syssettings as $key => $value) {
    	$keyout = strtoupper($key);
    	echo "<br><br>------------------------------$keyout--------------------------<br>";
    	var_dump($value);
    }*/
	// var_dump($syssettings);
	// get the paystack id-
	$cursetdata = $syssettings['settings']??[];

	$payments = $cursetdata['payments'];

	$paystack = $payments["paystack"] ?? [];

	$sec_key = $paystack["secret_key"] ?? "";

	// change the current secret key
	if($sec_key !== ""){
		$defaultseckey = $sec_key;
	}

	//////////////////////////////////////////
	// Default Redirection settings control //
	//////////////////////////////////////////
	$istest = varIndexRetriever("rtpath",null);

	if($istest !== null){	
		if ($istest == "admin") {
		    $returl = "${host_addr}admin/adminindex";
		} else {
		    $dashhead = $istest == "staff" ? "security" : $istest;
		
		    $returl = "${host_addr}${dashhead}dashboard/";
		}
	}

}

$refdata = $refdata ?? $trefdata;

$cpaymode = $host_pgateway_data['paystack']['paymode'];

$success = true;
$msg = "";

$cartid = "";

$uh = "";
$troid = "";
$doreceipt = true;
$updgrade = "";
$coupid = "";
$entryvariant = "updatetransactionpaystack";

$retparams = "";

$dataoutextras = $dataoutextras ?? [];

$trx_data_transaction_date = strtotime(date("Y-m-d H:i:s"));

$modeop =  varIndexRetriever("nwgappdata","");
$modeop =  $modeop == "" ? varIndexRetriever("modeop","plain"):$modeop;


$paystack = new Yabacon\Paystack($defaultseckey);

$trx_data_status = "";

if($cpaymode == "SDK" ){
	try {
		// the code below throws an exception if there was a problem completing the
		//  request, else returns an object created from the json response
		$trx = $paystack->transaction->verify(['reference' => $refdata]);

		$success = true;
		

	} catch (\Yabacon\Paystack\Exception\ApiException $e) {
		$msg = print_r($e->getResponseObject(), TRUE) ." <br> ". $e->getMessage();
		// print_r($e->getResponseObject());
		// die($e->getMessage());
		
		$success = false;
		
		
	}
}

if($cpaymode == "CURL"){
	$method = "GET";
	$url = "https://api.paystack.co/transaction/verify/$refdata";
	
	$sortdata = array();
	$sortdata['http_headers'] = array();
	
	$sortdata['http_headers']["status"] = "true";
	
	$sortdata['http_headers']["headers"] = array();
	$headsets = [
		"Authorization: Bearer {$defaultseckey}"
	];

	$sortdata['http_headers']["headers"] = $headsets;
	$data = [];

	$curldata = CallCurl($method, $url, $sortdata, $data);
	
	if ($curldata['result'] === false) {
		$success = false;
		$msg = "An error occured, details are as follows:<br>".print_r($curldata,TRUE);
		// echo "Curl Output Error<br><br><br>$msg";
		// var_dump($curldata);
	}else{
		// var_dump($curldata['result']);
		$trx = json_decode($curldata['result']);
		// print_r($trx);
	}
}

// exit();
// status should be true if there was a successful call
if (!$trx->status && $success == true) {
	$success= false;
	$msg = $trx->message;
}
	

if($modeop !== "json" && $success == false){

	// print_r($e->getResponseObject());
	// die($e->getMessage());	
	$returl = ($returl ?? $host_addr."completion/")."?t=checkout&sf=failure&rf=&ci=&uh=$uh&gateway=failure&msgout=$msg";
	
	// stop notification table update
	$nonottype = "none";
}

// process transaction updates and value adding for purchases
if($success == true){
	// check if the current reference has already been updated in the cartdata
	// table
	$cartid = $trx->data->metadata->cart_id;
	
	$doreceipt = $trx->data->metadata->doreceipt ?? $doreceipt;

	$returl = $trx->data->metadata->returl ?? "";

	$trx_data_transaction_date = $trx->data->transaction_date;

	$trx_data_status = $trx->data->status;

	$actiontitle = "Verification Successful";

	$msg = $trx->message;

	$uh = $trx->data->metadata->uh;
	$troid = $trx->data->metadata->troid;
	$updgrade = isset($trx->data->metadata->troid) ? $trx->data->metadata->troid : "";
	$coupid = $trx->data->metadata->coupid;
	$modeop = $trx->data->metadata->modeop ?? "plain";
	
	$retparams = $trx->data->metadata->retparams;

	$tq = "SELECT * FROM cartdata WHERE refid='$refdata' AND id='$cartid' AND
		MD5(uid)='$uh'";
	// echo "<br>TRQUERY: $tq<br>";
	$tqdata = briefquery($tq, __LINE__, "mysqli");
	$truid = $tqdata['resultdata'][0]['uid'];

	$cartdata = $tqdata['resultdata'][0];
	$numrows = $tqdata['numrows'];

	$dataoutextras['coupid'] = $coupid;
	$dataoutextras['cartid'] = $cartid;
	$dataoutextras['cartdata'] = $cartdata;
	$dataoutextras['uh'] = $uh;
	$dataoutextras['troid'] = $troid;
	

	$cartdataoutput = "";
	if ('success' === $trx->data->status && $numrows > 0) {
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
		$msg = $trx->message;
		$actiontitle = "Error validating Transaction";
		$actionstatus = "failure";
		perform_success("failure");

	}
}

$actiondetails = $actiondetails ?? $msg;

/*var_dump($defaultseckey);
var_dump($success);
exit();*/
// functions
function give_value($reference, $trx) {
	global $troid;
	// Be sure to log the reference as having gotten value
	// write code to give value
	// $tqdata=$GLOBALS['tqdata']['resultdata'][0];
	// var_dump($tqdata);
	// var_dump($trx);
	$dt = strtotime($trx->data->transaction_date);

	$paymentdate = date('Y-m-d H:i:s', $dt);

	// echo $paymentdate;
	// update the ref status for the transaction
	// echo "ter1";
	// echo $GLOBALS['cartdata']['refstatus']." ".$trx->data->status;

	if($GLOBALS['cartdata']['refstatus'] !== $trx->data->status){


		genericSingleUpdate("cartdata", "refstatus", $trx->data->status, "id", $GLOBALS['cartid']);
		genericSingleUpdate("cartdata", "apitype", "paystack", "id", $GLOBALS['cartid']);

		// update transaction date
		// echo "ter2";
		genericSingleUpdate("cartdata", "paymentdate", "$paymentdate", "id", $GLOBALS['cartid']);

		// update the transaction table as well
		genericSingleUpdate("transaction", "transactionstatus", $trx->data->status, "cartid", $GLOBALS['cartid']);
		genericSingleUpdate("transaction", "transactiontime", "$paymentdate", "cartid", $GLOBALS['cartid']);
		genericSingleUpdate("transaction", "apitype", "paystack", "cartid", $GLOBALS['cartid']);

		// update the coupon data
		genericSingleUpdate("transaction", "coupid", "$GLOBALS[coupid]", "cartid", $GLOBALS['cartid']);

		if($GLOBALS["coupid"] !== "" && $GLOBALS["coupid"] > 0){
			// update the package coupon use table data
			$q = "INSERT INTO pckgcpused(pckgcpid,uid,entrydate) VALUES('$GLOBALS[coupid]','$GLOBALS[troid]','$paymentdate')";
			$cqdata = briefquery($q, __LINE__, "mysqli", true);

		}
		
		// process the cart data item by item and attempt to store static elements
		// with countable units present in it in the appropriate table.
		if($GLOBALS['cartdata']['orgid'] > 0){
			activateRewardableTransactionAlerts($GLOBALS['cartid']);
		}else{
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
	global $host_tpathplain,$dataoutextras,$host_website_name, $refdata, $cartid, $cartdata, $cartdataoutput, $returl, $troid, $coupid, $trdata, $retparams, $uh, $modeop,$msg,$success,$host_website_url,$doreceipt;

	$_SESSION['lastsuccess'] = 0;
	$extradata = [];

	if($modeop == "json"){
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

	$returl = $GLOBALS['returl'] ?? $GLOBALS['host_addr']."completion/";
	if(isInString("index?error", $returl)['instring'] == true){
		$returl = $GLOBALS['host_addr']."completion/";
	}

	$GLOBALS['appendedparams'] = "&t=checkout&$retparams&sf=$sucfail&rf=$refid&ci=$cartid&uh=$uh&gateway=paystack&utm_campaign=checkout&utm_package=$itemtitle&utm_transref=$transref";
	$GLOBALS['returl'] = $returl;

	$GLOBALS['actiontitle'] = "Payment Successful";
	$GLOBALS['actionstatus'] = "successful";
	$GLOBALS['actiontime'] = 120000;
	$GLOBALS['actiondetails'] = "The payment transaction was successful, check your transactions menu for payment information and or receipt.";

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
		$headers .= 'From: <no-reply@' . $host_website_url . '>' . "\r\n";

		$subject = $title;

		// send out the email
		$mailtype = "defaultuserreceipt";
		$maildata = array();
		$maildata['headers'] = $headers;
		$maildata['title'] = $subject;
		$maildata['toemail'] = $toemail;
		if($doreceipt == true){

			$mailer = sendGenMail($mailtype, $maildata, $extradata);
		}


	}

}
// echo "<br>Return to: $returl$appendedparams<br>";
// exit();

// $notrdtype = "none"; //stops redirection

$nonottype = "none"; // stops notification table update

// bring in the nrsection.php file
include $host_tpathplain.'nrsection.php';
exit();
// full sample verify response is here
/*
	{
	"status":true,
	"message":"Verification successful",
	"data":{
	"amount":27000,
	"currency":"NGN",
	"transaction_date":"2016-10-01T11:03:09.000Z",
	"status":"success",
	"reference":"DG4uishudoq90LD",
	"domain":"test",
	"metadata":0,
	"gateway_response":"Successful",
	"message":null,
	"channel":"card",
	"ip_address":"41.1.25.1",
	"log":{
	"time_spent":9,
	"attempts":1,
	"authentication":null,
	"errors":0,
	"success":true,
	"mobile":false,
	"input":[ ],
	"channel":null,
	"history":[
	{
	"type":"input",
	"message":"Filled these fields: card number, card expiry, card cvv",
	"time":7
	},
	{
	"type":"action",
	"message":"Attempted to pay",
	"time":7
	},
	{
	"type":"success",
	"message":"Successfully paid",
	"time":8
	},
	{
	"type":"close",
	"message":"Page closed",
	"time":9
	}
	]
	},
	"fees":null,
	"authorization":{
	"authorization_code":"AUTH_8dfhjjdt",
	"card_type":"visa",
	"last4":"1381",
	"exp_month":"08",
	"exp_year":"2018",
	"bin":"412345",
	"bank":"TEST BANK",
	"channel":"card",
	"signature": "SIG_idyuhgd87dUYSHO92D",
	"reusable":true,
	"country_code":"NG"
	},
	"customer":{
	"id":84312,
	"customer_code":"CUS_hdhye17yj8qd2tx",
	"first_name":"BoJack",
	"last_name":"Horseman",
	"email":"bojack@horseman.com"
	},
	"plan":"PLN_0as2m9n02cl0kp6"
	}
	}
*/
?>