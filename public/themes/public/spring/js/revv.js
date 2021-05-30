$(document).ready(function(){

	// this section controls fetching event entry data for user in a form
    // amongst order data retrievals
    $(document).on("change", `[data-evdata=true],[data-gddata=true],
        [data-brdata=true],[data-bddata=true],[data-bsdata=true],[data-bstdata=true]`, function() {
        // expected attributes on the element are as follows
        /*
            data-ftt=carries the name of the parent element
            data-fttt=carries the data-name attibute value of target select element
            data-fttype=carries the type of data retrieval to be done
            data-ftset=carries the type of data retrieval to be done
        */
        var $this = $(this);
        var cftset = typeof $this.attr('data-ftset') == "undefined" ? "" : $this.attr('data-ftset');
        var dset = [{
            "fdtype": "evdata",
            "data": "evddata",
            "dataftset": cftset
        }, {
            "fdtype": "bsdata",
            "data": "bsddata",
            "dataftset": cftset
        }, {
            "fdtype": "brdata",
            "data": "brddata",
            "dataftset": cftset
        }, {
            "fdtype": "bddata",
            "data": "bdddata",
            "dataftset": cftset
        }, {
            "fdtype": "gddata",
            "data": "gdddata",
            "dataftset": cftset
        }, {
            "fdtype": "bstdata",
            "data": "bstddata",
            "dataftset": cftset
        }];
        // console.log("Dset: ",dset);
        var clen = dset.length;
        for (var i = 0; i < clen; i++) {
            var cdata = dset[i];
            // console.log("cdata: ",cdata);
            var type = $this.attr("data-" + cdata.fdtype + "");

            var data = $this.attr("data-" + cdata.data + "");
            // console.log("type: ",type ," Data",data);
            if (typeof data !== "undefined") {
                data = JSON.parse(data);
                // console.log("type: ",type);
                getFDdata($this, type, data);
            }
        }
    });

    // controls clicking of hstrigger (hide show)
    $(document).on("click change change.hstrigger select2:select", "[data-hstrigger],[data-name=select2plain][data-hstrigger]", function() {
        // console.log("Fired hstrigger!!!");
        // get the current element data-hsname value
        var hstel = $(this).attr("data-hstrigger");
        // use to mark parent element if any is available, thereby limiting the 
        // scope of the hide show feature
        var dopar=false;
        if($(this).get(0).hasAttribute("data-hspar")){
            dopar=true;
            var parent=$("form[name="+$(this).attr('data-hspar')+"],div[name="+$(this).attr('data-hspar')+"]");
        }

        // get the type of value system in use values are
        // 'single', nothing or 'multiple'
        var hsttype = $(this).attr("data-hsttype");
        if (hsttype === null || hsttype === undefined || hsttype === NaN) {
            hsttype = "";
        }
        var curval = $(this).val();
        // get the trigger value for the current element
        var hstval = $(this).attr("data-hstval");
        var hstopts = typeof $(this).attr("data-hstopts")!=="undefined"?
        $(this).attr("data-hstopts"):
        "";
        // console.log("hsttype: ",hsttype," hstval: ",hstval," curval: ",curval);
        if (hsttype == "multiple") {
            try {
                eldata = JSON.parse(hstel);
                valdata = JSON.parse(hstval);
                optsdata = hstopts!==""?JSON.parse(hstopts):{};
                eldata = eldata.t;
                valdata = valdata.v;
                ellen = valdata.length;
            } catch (e) {
                ellen = 0;
                console.log("An error occured with hide/show trigger:", e)
            }
            var curelt="";
            // run through all the valid hsnames for the current element
            // and proceed to show or hide elements as appropriate
            for (var i = 0; i < ellen; i++) {
                var curel = typeof eldata[i]!=="undefined"?eldata[i]:curelt;
                curelt=curelt!==curel&&curel!==""?curel:curelt;
                // console.log("Cur elt",curelt);
                var cval = valdata[i];
                // console.log("hsttype: ",hsttype," hstval: ",hstval," curval: ",curval);
                if (curel !== "") {
                    // get hide show element
                    var hsel = dopar==false?$('[data-hsname*=' + curel + ']'):parent.find('[data-hsname*=' + curel + ']');
                    if (hsel.length&&hsel.length < 1) {
                        hsel = dopar==false?$('[data-hsname*=",' + curel + '"],[data-hsname*="' + curel + ',"],[data-hsname*=",' + curel + ',"]'):
                        parent.find('[data-hsname*=",' + curel + '"],[data-hsname*="' + curel + ',"],[data-hsname*=",' + curel + ',"]');
                    }
                    // store the showable elements for the current interaction
                    var hselshset=[];
                    // store the hideable elements for the current interaction
                    var hselhidset=[];
                    for (var ir = 0; ir < hsel.length; ir++) {
                        var chsel=$(hsel[ir]);
                        var curattr=chsel.attr("data-hsname");
                        var ncarr = [];
                        var cncarr = true;
                        // console.log("Cur Hsel:",chsel," Cur attr: ", curattr);
                        if (typeof chsel!=="undefined" && chsel.length > 0) {

                            // split the names and store them in an array
                            var chselattr = chsel.attr("data-hsname").split(",");

                            for (var it = 0; it < chselattr.length; it++) {
                                ncarr[ncarr.length] = chselattr[it];

                            }
                            // console.log("CHselattr: ",chsel.attr("data-hsname"),"Split length: ",ncarr.length);

                            // check for conflicts in the current entry in case the entry should be
                            // shown as its valid for the current value set
                            for (var ii = 0; ii < ncarr.length; ii++) {
                                if (curval == ncarr[ii]) {

                                    // console.log("curval: ",curval,"ncarr: ",ncarr[ii]);

                                    // stop the current element from
                                    // being hidden
                                    cncarr = false;
                                    break;
                                }
                            }
                        }
                        
                        // check for outright value connection
                        if (curval == cval&&typeof chsel!=="undefined") {
                            hselshset[hselshset.length]=chsel;

                            // chsel.removeClass("hidden");

                            // console.log("hstriggerd: true visible"," hsel: ",chsel);

                        } else {
                            if (cncarr == true&&typeof chsel!=="undefined") {
                                hselhidset[hselhidset.length]=chsel;
                                // chsel.addClass("hidden");
                                // console.log("hstriggerd: true hidden"," hsel: ",chsel);
                            }
                        }                    
                    }
                    // console.log("hshidearr: ",hselhidset," hsel: ",hselshset);

                    // hide the hideables
                    for (var il = 0; il < hselhidset.length; il++) {
                        var chsel=hselhidset[il];
                        if(typeof chsel!=="undefined"){
                            chsel.addClass('hidden')
                        }
                    }

                    // show the showables
                    for (var im = 0; im < hselshset.length; im++) {
                        var chsel=hselshset[im];
                        if(typeof chsel!=="undefined"){
                            chsel.removeClass('hidden')
                        }
                    }
                    
                }
            }
            ;// console.log(eldata);
        } else {
            // get the current element itself
            var hstout = hstel !== "true" ? hstel : hstval;
            var hsel = $('[data-hsname="' + hstout + '"]');

            if (typeof hsel.length!=="undefined" && hsel.length == 0) {
                hsel = $('[data-hsname*=",' + hstout + '"],[data-hsname*="' + hstout + ',"],[data-hsname*=",' + curel + ',"]');
            }
            // console.log("Cur Hsel:",hsel);

            var precd = "";
            if (hstval.indexOf("*cval*") > -1) {
                precd = hstval.split("*cval*");
                prevval = precd[0];
                hstval = hstval.replace("*cval*", $(this).val());
            }
            curval = precd + curval;
            // console.log("hsttype: ",hsttype," hstval: ",hstval," curval: ",curval);
            if ((curval == hstval&&typeof hsel!=="undefined") || 
                (curval !== "" && hstval == "*any*"&&typeof hsel!=="undefined")) {
                // console.log("hstriggerd: true"," hsel: ",hsel);
                hsel.removeClass("hidden");
            } else {
                // console.log("hstriggerd: true hidden"," hsel: ",hsel);
                if(typeof hsel!=="undefined"){
                    hsel.addClass("hidden");
                }
            }

        }
    });

    // handles clicks on vm selection box for handling image attachment and 
    // camera capture
    $(document).on("change", "select[name=capturemode],select[name=idcapturemode]", function() {
        var cval = $(this).val();
        var copt = $(this).attr("data-camfeed");
        var stopdatain={};
        stopdatain.reset=true;
        if(typeof copt!=="undefined" && copt!==""){
            copt=$.parseJSON(copt);
            stopdatain=typeof copt.stopdata!=="undefined"?copt.stopdata:"";
            if(typeof stopdatain.obj=="string"){
                stopdatain.obj=$.find(stopdatain.obj).length>0?$($.find(stopdatain.obj)):"";
            }
        }else{
            copt={"v":"video","c":"canvas","io":"camoutimg","ip":"imgcapturedata","par":"camuploadarea"};
        }
        // console.log("Copts: ",copt);
        // get the camera 
        // get the parent element
        // console.log("Change value:",cval);
        if (cval == "camera") {
            prepAPhoto(copt.v, copt.c, {
                "imgout": copt.io,
                "input": copt.ip,
                "stopdata":stopdatain
            });

        } else {

            stopAPhoto(copt.v, copt.c, copt.ip, stopdatain);
        }



        if (cval !== "attach" && cval !== "upload") {

            // remove the data in the attachment photo section
            if(typeof copt.sec!=="undefined"&&copt.sec!==""){
                var sec= $.find('#'+copt.sec+'');
                if(sec.length>0){
                    $(sec).find('img._isprev').attr({"src": "","data-isprev-ls":"","data-load-ls":""}).addClass("hidden");
                    $(sec).find('input[name=profpic]').val("");
                }
            } else{
                $('div[data-felname=imagesection] img._isprev').attr({"src": "","data-isprev-ls":"","data-load-ls":""}).addClass("hidden");
                $('div[data-felname=imagesection] input[name=profpic]').val("");
                $('div[data-felname=imagesection3] img._isprev').attr({"src": "","data-isprev-ls":"","data-load-ls":""}).addClass("hidden");
                $('div[data-felname=imagesection3] input[name=profpic]').val("");
            }         

        }
    });
    $(document).on("click","button.btn-pictaker",function(){
        event.preventDefault();
    });

    // handle loading of image
    $(document).on("change","img[data-loadprepphoto=true]",function(){ 
        // console.log("Loaded...");
        if($(this).attr("src")!==""){
            var formname=$(this).attr("data-formname");
            var parid=$(this).attr("data-parid");
            // get the name of the text area where the parsded image data is to be 
            // stored for upload
            var preptarget=$(this).attr("data-preptarg");
            var prepobj=$(this).attr("data-prepobj");
            prepATPhoto('form[name='+formname+'] img[data-parid='+parid+']',
                $('form[name='+formname+'] input[name='+prepobj+']'),
                $('form[name='+formname+'] [name='+preptarget+']'));
        }
    });
});
