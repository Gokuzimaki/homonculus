<?php
	include('../../connection.php');
	// Retrieve the request's body
	

	// parse event (which is json string) as object
	// Give value to your customer but don't give any output
	// Remember that this is a call from Paystack's servers and 
	// Your customer is not seeing the response here at all
	$event =Yabacon\Paystack\Event::capture();
	http_response_code(200);

	/* It is a important to log all events received. Add code *
     * here to log the signature and body to db or file       */
    openlog('MyPaystackEvents', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
    syslog(LOG_INFO, $event->raw);
    closelog();

    /* Verify that the signature matches one of your keys*/
    $my_keys = [
                'live'=>"".$host_pgateway_data['paystack']['defaultseckey']."",
                'test'=>"".$host_pgateway_data['paystack']['defaultpubkey']."",
              ];
    $owner = $event->discoverOwner($my_keys);
    if(!$owner){
        // None of the keys matched the event's signature
        die();
    }

	$switchobj=$event->obj;
	switch($switchobj->event){
	    // charge.success
	    case 'charge.success':
	    	// get the metadata for the transaction
	    	$cartid=$switchobj->data->metadata->cart_id;
			$uh=$switchobj->data->metadata->uh;
			$troid=$switchobj->data->metadata->troid;
			$coupid=$switchobj->data->metadata->coupid;
			// get the current cart information
			$tq="SELECT * FROM cartdata WHERE refid='$refdata' AND id='$cartid' AND 
			MD5(uid)='$uh'";
			$tqdata=briefquery($tq,__LINE__,"mysqli");

			$cartdata=$tqdata['resultdata'][0];
			// get the userid for this transaction
			$truid = $cartdata['uid'];

			$itemdata=json_decode($cartdata['cartdata'],true);
			// disable the prevous transaction if the 'troid' get parameter is 
		  	// available
		  	$paymentdate=date("Y-m-d H:i:s");
		  	if($troid!==""){
		  		 genericSingleUpdate("transaction", "status", "expired", "MD5(id)", "$troid");
		  		 genericSingleUpdate("transaction", "enddate", date("Y-m-d H:i:s"), "MD5(id)", "$troid");
		  		 // update the transaction with the coupon id data
		  		 genericSingleUpdate("transaction", "coupid", "$coupid", "MD5(id)", "$troid");
		  		 // update the package coupon use table data
				$q="INSERT INTO pckgcpused(pckgcpid,uid,entrydate) VALUES('$coupid','$truid','$paymentdate')";
				$cqdata=briefquery($q,__LINE__,"mysqli",true);
		  	}
		  	
			$cartdataoutput="";
			for($i=0;$i<count($itemdata);$i++){
				$citemtitle=$itemdata[$i]['itemtitle'];
				$amount=$itemdata[$i]['amount'];
				$cartdataoutput.='Item: '.$citemtitle.'. <br>
				Cost:'.$cartdata['cartcurrency'].' '.$amount.'<br><br>';
			}
			// get the users information
			$sq="SELECT * FROM users WHERE MD5(id)='$uh'";
			$sqdata=briefquery($sq,__LINE__);
			$udata=array();
			$extradata=[];
			// user account type
			$u="";
			$email="gokuzimaki@gmail.com";
			if($sqdata['numrows']>0){
				$data['row']=$sqdata['resultdata'][0];
				$udata=getSingleUserPlain($data['row']['id'],"",$data);
				$customername=$udata['nameout'];
				$u=isset($udata['usersubtype'])&&$udata['usersubtype']!==""?
				$udata['usersubtype']:"personal";
				$email=$udata['email'];

			}

			$extradata['pudata']=$udata;

			// update the ref status for the transaction
			$cartid=$tqdata['id'];
			$extradata['cartid']=$cartid;
			genericSingleUpdate("cartdata","refstatus",$trx->data->status,"id",$tqdata['id']);

			// update transaction date
			genericSingleUpdate("cartdata","paymentdate",$paymentdate,"id",$tqdata['id']);

			// update the transaction table as well
			genericSingleUpdate("transaction","transactionstatus",$trx->data->status,"cartid",$tqdata['id']);
			genericSingleUpdate("transaction","transactiontime",$paymentdate,"cartid",$tqdata['id']);
			
			// send user email notification on payment success if none sent
			$title="Your Purchase on ".strtoupper($host_rendername);
			$content='
				<p style="text-align:left;">
					Hi there, '.$customername.'<br>
					Your Payment on the '.$host_website_name.' platform,
					 for the following; 
					<br>'.$cartdataoutput.'<br>
					 On, '.date('D, d F, Y h:i:s A',
					 	strtotime($cartdata['entrydate']).'
					 	was successful.<br>
				</p>
				<p style="text-align:right;">Thank You.</p>
			';
			$footer='

			';
			
			// // echo $emailout['rowmarkup'];
			$toemail="$email";
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= 'From: <no-reply@'.$host_website_name.'>' . "\r\n";

			$subject=$title;

			// send out the email
			$mailtype="defaultuserreceipt";
			$maildata=array();
			$maildata['headers']=$headers;
			$maildata['title']=$subject;
			$maildata['toemail']=$toemail;
			$mailer=sendGenMail($mailtype,$maildata,$extradata);
			
	    break;
	}
	exit();
	/*sample JSON string returned from paystack
		{
			"event": "charge.success",
			"data": {
				"id": 748541,
				"domain": "test",
				"status": "success",
				"reference": "87pfjx9yjj",
				"amount": 67800,
				"message": null,
				"gateway_response": "Successful",
				"paid_at": "2017-02-09T17:36:15.000Z",
				"created_at": "2017-02-09T17:35:48.000Z",
				"channel": "card",
				"currency": "NGN",
				"ip_address": "154.118.45.106",
				"metadata": {},
				"log": null,
				"fees": null,
				"fees_split": null,
				"customer": {
					"id": 181336,
					"first_name": null,
					"last_name": null,
					"email": "support@paystack.com",
					"customer_code": "CUS_dw5posshfd1i5uj",
					"phone": null,
					"metadata": null,
					"risk_action": "default"
				},
				"authorization": {
					"authorization_code": "AUTH_z7k6ysdd",
					"bin": "412345",
					"last4": "1381",
					"exp_month": "01",
					"exp_year": "2020",
					"channel": "card",
					"card_type": "visa visa",
					"bank": "TEST BANK",
					"country_code": "NG",
					"brand": "visa",
					"reusable": true
				},
				"plan": {
					"id": 2511,
					"name": "bleh",
					"plan_code": "PLN_3g5vcz2c1pkc4n0",
					"description": null,
					"amount": 67800,
					"interval": "hourly",
					"send_invoices": true,
					"send_sms": true,
					"currency": "NGN"
				},
				"subaccount": {
				  
				},
				"paidAt": "2017-02-09T17:36:15.000Z"
			}
		}
	*/

?>