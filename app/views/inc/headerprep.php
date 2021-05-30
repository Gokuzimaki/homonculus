<?php


// the default header, change the variables to what suits you in order to
// effect the final output of the content
$scriptextra = $scriptextra ?? "";

$mpagedescription = $mpagedescription ?? "The I-Colony Platform offers a suite of solutions and serivces that cater to your every need from when you come in to when you leave";

$mpagekeywords = $mpagekeywords ?? "I-Colony Platform, ReVisit, Visitor management, visitor management system, vms, queue management, queue management system, qms, I-Colony Tech";

// variable for holding identity's zendesk access script
$mpagezendeskscript = '
		<!--Start of Zendesk Chat Script-->
		<script type="text/javascript">
		window.$zopim||(function(d,s){var z=$zopim=function(c){
		z._.push(c)},$=z.s=
		d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
		_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute(\'charset\',\'utf-8\');
		$.src=\'https://v2.zopim.com/?5hUMd4nvAit9hcCOEsicOWeY0l8DqlXN\';z.t=+new Date;$.
		type=\'text/javascript\';e.parentNode.insertBefore($,e)})(document,\'script\');
		</script>
		<!--End of Zendesk Chat Script-->
	';

// variable holding drift data
$mpagedrift = '<!-- Start of Async Drift Code -->
		<!-- Start of Async Drift Code -->
		<script>
		"use strict";

		!function() {
		  var t = window.driftt = window.drift = window.driftt || [];
		  if (!t.init) {
		    if (t.invoked) return void (window.console && console.error && console.error("Drift snippet included twice."));
		    t.invoked = !0, t.methods = [ "identify", "config", "track", "reset", "debug", "show", "ping", "page", "hide", "off", "on" ],
		    t.factory = function(e) {
		      return function() {
		        var n = Array.prototype.slice.call(arguments);
		        return n.unshift(e), t.push(n), t;
		      };
		    }, t.methods.forEach(function(e) {
		      t[e] = t.factory(e);
		    }), t.load = function(t) {
		      var e = 3e5, n = Math.ceil(new Date() / e) * e, o = document.createElement("script");
		      o.type = "text/javascript", o.async = !0, o.crossorigin = "anonymous", o.src = "https://js.driftt.com/include/" + n + "/" + t + ".js";
		      var i = document.getElementsByTagName("script")[0];
		      i.parentNode.insertBefore(o, i);
		    };
		  }
		}();
		drift.SNIPPET_VERSION = \'0.3.1\';
		drift.load(\'tz352tieymhw\');
		</script>
		<!-- End of Async Drift Code -->
	';
// for onesignal offline
$mposnotbtn = $host_env !== "online" ? 'true' : "false";
$mposoffline = $host_env !== "online" ? 'allowLocalhostAsSecureOrigin: true,' : "";
$mproffline = $host_env !== "online" ? "/$host_appfname/" : "$host_addr/";
$mpofflinefile = $host_env !== "online" ? 'offline-local.html' : "offline.html";
$mpservwl = $host_env !== "online" ? $host_addr : "$host_addr";


$mpageonesignalappend = $mpageonesignalappend ?? "";

$mpageonesignal = '
	<!-- for onesignal script -->
    <script>
    	navigator.serviceWorker.register(\'' . $mproffline . 'OneSignalSDKWorker.js\');
    </script>
	<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
	<script src="' . $mpservwl . 'upup.min.js"></script>
    <script>
	    var OneSignal = window.OneSignal || [];
	    OneSignal.push(function() {
		    OneSignal.init({
		        appId: "' . $host_onesignal_appid . '",
		        /*requiresUserPrivacyConsent: true,*/
		        autoResubscribe: false,
				' . $mposoffline . '
		        notifyButton: {
		          enable: ' . $mposnotbtn . ',
		        },
		    });
		    /* In milliseconds, time to wait before prompting user. This time is relative to right after the user presses <ENTER> on the address bar and navigates to your page */
		    // var notificationPromptDelay = 30000;
		    /* Use navigation timing to find out when the page actually loaded instead of using setTimeout() only which can be delayed by script execution */
		    // var navigationStart = window.performance.timing.navigationStart;
		    /* Get current time */
		    var timeNow = Date.now();
		    /* Prompt the user if enough time has elapsed */
			// setTimeout(promptAndSubscribeUser, Math.max(notificationPromptDelay - (timeNow - navigationStart), 0));

		    // initialise app
	      	OneSignal.showNativePrompt();
	      	//run upup sw
	      	// console.log("Running scripts...");
	      	UpUp.start({
	    		\'cache-version\':\'v2\',
	    		\'content-url\':\'' . $mpofflinefile . '\',
	    		\'assets\':[
	    					\'' . str_replace("$host_addr","",$host_logo_large_small_image) . '\',
	    					\'' . str_replace("$host_addr","",$host_loaderimg) . '\',
	    					\'images/backgrounds/bglog.jpg\',
	    					\'admin/themes/modernclean/app-assets/css/vendors.css\',
	    					\'admin/themes/modernclean/app-assets/vendors/css/forms/icheck/icheck.css\',
	    					\'admin/themes/modernclean/app-assets/vendors/css/forms/icheck/custom.css\',
	    					\'admin/themes/modernclean/app-assets/css/app.css\',
	    					\'admin/themes/modernclean/app-assets/css/core/menu/menu-types/vertical-content-menu.css\',
	    					\'admin/themes/modernclean/app-assets/css/core/colors/palette-gradient.css\',
	    					\'admin/themes/modernclean/app-assets/css/pages/login-register.css\',
	    					\'admin/themes/modernclean/assets/css/style.css\',
	    					\'icons/font-awesome/css/all.min.css\',
	    					\'admin/themes/systemcss/my-color.css\',
	    					\'admin/themes/modernclean/app-assets/vendors/js/vendors.min.js\',
	    					\'admin/themes/modernclean/app-assets/vendors/js/ui/headroom.min.js\',
	    					\'admin/themes/modernclean/app-assets/vendors/js/forms/icheck/icheck.min.js\',
	    					\'admin/themes/modernclean/app-assets/js/core/app-menu.js\',
	    					\'admin/themes/modernclean/app-assets/js/core/app.js\',
	    					\'admin/themes/modernclean/app-assets/js/scripts/forms/form-login-register.js\',
	    					\'scripts/mylib.js\',

	    				 ],
	    		\'service-worker-url\': \'upup.sw.min.js\'
	    	});
	    });
	    /*function promptAndSubscribeUser() {
		    window.OneSignal.isPushNotificationsEnabled(function(isEnabled) {
		      if (!isEnabled) {
		        window.OneSignal.showSlidedownPrompt();
		      }
		    });
		}*/
	</script>
'.$mpageonesignalappend;

// $mpageonesignal="";

$mpagewebpushrappend = $mpagewebpushrappend ?? "";
$mpagewebpushr = '
	<!-- start webpushr code --> <script>(function(w,d, s, id) {if(typeof(w.webpushr)!==\'undefined\') return;w.webpushr=w.webpushr||function(){(w.webpushr.q=w.webpushr.q||[]).push(arguments)};var js, fjs = d.getElementsByTagName(s)[0];js = d.createElement(s); js.id = id;js.async=1;js.src = "https://cdn.webpushr.com/app.min.js";fjs.parentNode.appendChild(js);}(window,document, \'script\', \'webpushr-jssdk\'));webpushr(\'setup\',{\'key\':\'BFgD_6bPlopddI4ITyfIMjgfW8aKIaWwnZ7w_c8pgzeT-u9pWkI1IgLHeSTpWMVI4y8TBE4-sevYCVF4RQCjphc\' ,\'integration\':\'popup\' });</script><!-- end webpushr code -->
'.$mpagewebpushrappend;

$mpagehubspot = '
		<!-- Start of HubSpot Embed Code -->
			<script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/5215707.js"></script>
		<!-- End of HubSpot Embed Code -->
	';
//setup bodymovin library script - "lottie"
$mpagebodymovin = "";

$mpageimage = isset($mpageimage) ? $mpageimage : $host_addr . "images/favicon.ico";
$mpage_dash_alertdisplay = isset($mpage_dash_alertdisplay) ? $mpage_dash_alertdisplay : false;
$mpagetitle = isset($mpagetitle) ? $mpagetitle : "Welcome | $host_website_name";
$mpageurl = isset($mpageurl) ? $mpageurl : $host_addr;
$mpagecanonicalurl = isset($mpagecanonicalurl) ? $mpagecanonicalurl : $host_addr;
$mpageogtype = isset($mpageogtype) ? $mpageogtype : "website";
$mpageextrametas = isset($mpageextrametas) ? $mpageextrametas : "";
$mpagefbappid = isset($mpagefbappid) ? $mpagefbappid : "";
$mpagefbadmins = isset($mpagefbadmins) ? $mpagefbadmins : "";
$mpagesitename = isset($mpagesitename) ? $mpagesitename : "$host_website_name";
$mpagecrumbclass = isset($mpagecrumbclass) ? $mpagecrumbclass : "hidden";
$mpagecrumbtitle = isset($mpagecrumbtitle) ? $mpagecrumbtitle : "";
$mpagecrumbpath = isset($mpagecrumbpath) ? $mpagecrumbpath : "";
$mpagecrumbdata = isset($mpagecrumbdata) ? $mpagecrumbdata : "";
$mpageheaderclass = isset($mpageheaderclass) ? $mpageheaderclass : "";
$mpagelinkclass = isset($mpagelinkclass) ? $mpagelinkclass : "";
$mpageblogid = isset($mpageblogid) ? $mpageblogid : 1;
$mpagelinkclass2 = isset($mpagelinkclass2) ? $mpagelinkclass2 : "";
$mpagelinkclass3 = isset($mpagelinkclass3) ? $mpagelinkclass3 : "";
$mpage_lsb = isset($mpage_lsb) ? $mpage_lsb : "true";
$mpagecolorstylesheet = "";
$mpage_host_addr = $host_addr ?? "";
// google analytics
$defaultgatrackcode = isset($defaultgatrackcode) ? $defaultgatrackcode : "";
$mpagega = '
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=' . $defaultgatrackcode . '"></script>
		<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag(\'js\', new Date());
	  gtag(\'config\', \'' . $defaultgatrackcode . '\');
	</script>
	';
// googletagmanager head
$mpagegtmh = "
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-55GVTRH');</script>
		<!-- End Google Tag Manager -->
	";
// googletagmanager body
$mpagegtmb = "
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=GTM-55GVTRH\"
		height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
	";

// control the load point for the styles on the page
$mpagestyleload = "top";
$mpagemergedscripts = "";
$mpagemergedstyles = "";
if (!isset($mpagemaps)) {
	$mpagemaps = '
			<script src="http://maps.google.com/maps/api/js?key=' . $host_google_devkey . '"></script>
			<!-- // <script src="' . $host_addr . 'scripts/js/maplace.min.js"></script>-->
		';
}
// for holding socialnetwork initiation scripts
$mpagesdksmarkuproot = '
		<div id="fb-root"></div>
	';
// social sdks
$mpagesdks = '
		<script type="text/javascript">
		(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=223614291144392";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, \'script\', \'facebook-jssdk\'));</script>
		<!-- For google plus -->
		<script type="text/javascript">
		  (function() {
		    var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
		    po.src = \'https://apis.google.com/js/platform.js\';
		    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
		  })();
		</script>

		<!-- For twitter -->
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>

		<!-- For LinkedIn -->
		<script src="//platform.linkedin.com/in.js" type="text/javascript">
		 lang: en_US
		</script>
	';

$mpagebannerone = '
		<div class="site-content-banner">
	    	<img src="' . $host_addr . 'images/fiwl/slide2.jpg"/>
	    </div>
	';
$mpagebannermobileone = '

	';
$mpagevegasone = '

	';

//for holding extra lib scripts to be imported,loads above page
//action handling scripts
$mpagelibscriptextras = isset($mpagelibscriptextras) ? $mpagelibscriptextras : '';

// for holding extra lib styles in a per page scenario. also loads in page header
$mpagelibstyleextras = isset($mpagelibstyleextras) ? $mpagelibstyleextras : '';

//for holding extra scripts to be imported in the themescripts file
$mpagescriptextras = isset($mpagescriptextras) ? $mpagescriptextras : '';

//for holding extra styles to be imported themestylesheets file
$mpagestylesextras = '';

// carry the gsuite meta tag
$mpagegsuite = '<meta name="google-site-verification" content="0ZupU6lG3-Sfh1bzTU0khNBUTwJyMVHSexmz39pMBTg" />';

// mail chimp suite
$mpagemailchimp = "";
if (isset($host_env) && $host_env == "online") {
	$mpagemailchimp = '<script id="mcjs">!function(c,h,i,m,p){m=c.createElement(h),p=c.getElementsByTagName(h)[0],m.async=1,m.src=i,p.parentNode.insertBefore(m,p)}(document,"script","https://chimpstatic.com/mcjs-connected/js/users/35858f45a58a82684e9470269/be2b92142f064f13a3978026f.js");</script>';
}

// storeage area for all admin types
$mpage_admin_viewtypes = [];
// for modernclean template
$mpage_admin_viewtypes[0]["class"] = "vertical-content-menu";
$mpage_admin_viewtypes[1]["class"] = "vertical-menu-modern";
$mpage_admin_viewtypes[2]["class"] = "vertical-compact-menu";
$mpage_admin_viewtypes[3]["class"] = "vertical-menu";
$mpage_admin_viewtypes[4]["class"] = "vertical-overlay-menu";

//for adding extra styling to the logo when necessary, using class
//names
$mpagelogostyle = '';

// for adding extra classes to the toplinks holder element
$mpagehsclass = "";



$mpagegooglesiteverification = "";
$mpagegoogleseo = '
		<!-- JSON-LD markup generated by Google Structured Data Markup Helper. -->
		<!-- add to the head section !-->
		<script type="application/ld+json">
		[ {
		  "@context" : "http://schema.org",
		  "@type" : "Article",
		  "name" : "I-Colony",
		  "articleBody" : "I-Colony is a flexible estate adn small communities management tool.",
		  "url" : "https://i-colony.com/"
		}]
		</script>
	';

/*Active page variable sections
	*$activepage1=index.php
	*$activepage2=register.php
	*$activepage3=faq.php
	*
	*
	*/
$mpagesearchclass = "hidden"; // control variable for displaying header search panel

$data = array();
$hwdata = array();
$rweldata = array();
$iweldata = array();



// end defaultpage properties

// final crumbpath data
if ($mpagecrumbtitle !== "" || $mpagecrumbdata !== "") {
	$mpagecrumbpath = '
			<div class="row subheader2 ' . $mpagecrumbclass . ' mar-top-smxlg">
				<div class="container">
			        <div class="col-lg-6 col-md-6 col-sm-6">
			            <div class="custom-page-header">
			            	<h1>' . $mpagecrumbtitle . '</h1>
			            </div>
			        </div>
			        <div class="col-lg-6 col-md-6 col-sm-6">
			            <ol class="breadcrumb pull-right">
			                ' . $mpagecrumbdata . '
			            </ol>
					</div>
				</div>
		    </div>
		';

}
// get some specific popular posts
// $mpageblogoutone=getAllBlogEntries("viewer","",$mpageblogid,"blogtype");

// create the blog carousel output
$mpageblogcarouselout = '';
$mpageblogoutone = array();
if ($mpageblogcarousel !== "" && $mpageblogoutone['numrows'] > 2) {
	# code...
	$mpageblogcarouselout = '
			<!-- CAROUSEL START -->
			<div class="blog-carousel-area">
				<div class="blog-carousel">
					' . $mpageblogcarousel . '
				</div>
			</div>
		';

} else {
	$mpageblogcarouselout = $mpagevegasone;
}


$theme_data = array();
$theme_data['renderpath'] = $host_tpathplain . "themesnippets/admin/modernclean";
// block google analytics
// if not on live server o no tracking code available or
// no
if ((isset($defaultgatrackcode) && $defaultgatrackcode == "") || $host_env == "local"
	|| ($host_env == "online" && isset($host_test))) {
	$mpagega = "";
}

// check for available user or client account
$mpageacctype = "";
$userid = isset($_SESSION['userh' . $host_rendername . '']) &&
isset($_SESSION['useri' . $host_rendername . '' . $_SESSION['userh' . $host_rendername . ''] . '']) ?
$_SESSION['useri' . $host_rendername . '' . $_SESSION['userh' . $host_rendername . ''] . ''] : 0;
$clientid = isset($_SESSION['clienth' . $host_rendername . '']) && isset($_SESSION['clienti' . $host_rendername . '' . $_SESSION['clienth' . $host_rendername . ''] . '']) ?
$_SESSION['clienti' . $host_rendername . '' . $_SESSION['clienth' . $host_rendername . ''] . ''] : 0;
if ($userid !== 0) {
	$mpageacctype = "user";
}

if ($clientid !== 0) {
	$mpageacctype = "client";
}

// update push notification custom attributes
// echo "Web PUSHR UPDATE";
// var_dump($mpage_customattr);
if(isset($mpage_customattr)){
	setPushCustomAttributes($mpage_customattr);
}
?>