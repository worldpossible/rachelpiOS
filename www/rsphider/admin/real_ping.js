//      Script that transfers requests from HTML client to PHP server script and vice versa.
//      Handling refresh for Real-time Logging during index and re-index procedure
//      by means of asynchronous requests (AJAX)  to the server.

//  Define variables
    // holds an instance of XMLHttpRequest
    var xmlHttp = createXmlHttpRequestObject();

    // holds the remote server address and parameters
    var serverAddress = "real_get.php";
    var getLog = "action=GetLog";               // get all new log info since last request
    var checkRefreshParams = "action=Ready";    //  check for server availabilty and index/re-index ready status
     
    // variables used to check the server availability 
    var requestsCounter = 0;            // counts how many log infos have been retrieved
    var checkInterval = 10;             // counts interval for checking server availability and refresf rate
    var refresh = 1;                    // basis for refresh rate
    var maxRefreshTime = 10;            // max. value for refresh (seconds)

// creates an XMLHttpRequest instance
function createXmlHttpRequestObject() {
    // will store the reference to the XMLHttpRequest object
    var xmlHttp;
    // this should work for all browsers except IE6 and older
    try {
        // try to create XMLHttpRequest object
        xmlHttp = new XMLHttpRequest();
    }
    catch(e)
    {
        // assume IE6 or older
        var XmlHttpVersions = new Array("MSXML2.XMLHTTP.6.0",
                                        "MSXML2.XMLHTTP.5.0",
                                        "MSXML2.XMLHTTP.4.0",
                                        "MSXML2.XMLHTTP.3.0",
                                        "MSXML2.XMLHTTP",
                                        "Microsoft.XMLHTTP");
        // try every prog id until one works
        for (var i=0; i<XmlHttpVersions.length && !xmlHttp; i++) {
            try { 
                // try to create XMLHttpRequest object
                xmlHttp = new ActiveXObject(XmlHttpVersions[i]);
            } 
            catch (e) {}
        }
    }
    // return the created object or display an error message
    if (!xmlHttp)
        alert("Error creating the XMLHttpRequest object.");
    else 
        return xmlHttp;
}

// call server asynchronously
function process() {
    // only continue if xmlHttp isn't void
    if (xmlHttp) {    
        // try to connect to the server
        try {
            // if just starting, or if we hit the specified number of requests,
            // check for server Refresh, otherwise ask for a new logging data
            if (requestsCounter % checkInterval == 0) {
                // check if server is available
                xmlHttp.open("POST", serverAddress + "?" + checkRefreshParams, true);
                xmlHttp.onreadystatechange = handleCheckingRefresh;
                xmlHttp.send(null);
            } else {
                // get new logging data
                xmlHttp.open("POST", serverAddress + "?" + getLog, true);
                xmlHttp.onreadystatechange = handleGettingLog;
                xmlHttp.send(null);
            }
        }
        catch(e)
        {
        alert("Can't connect to server:\n" + e.toString());
        }
    }
}

// function called when the state of the HTTP request changes
function handleCheckingRefresh() {
    // when readyState is 4, we are ready to read the server response
    if (xmlHttp.readyState == 4) {
        // continue only if HTTP status is "OK"
        if (xmlHttp.status == 200) {
            try {
                // do something with the reponse from the server
                checkRefresh();
            }
            catch(e)
            {
            alert("Error reading refresh rate from server:.\nError string: " + e.toString());
            }
        } else{
            alert("Error reading refresh rate from server:\nHTTP status: " + xmlHttp.statusText);
        }
    }
}

// handles the response received from the server
function checkRefresh() {
    // retrieve the server's response 
    refresh = xmlHttp.responseText;
    // obtain a reference to the <div> element on the HTML-page
    realLog = document.getElementById("realLogContainer");
    // check for correct refresh rate
    if (refresh <= maxRefreshTime){
        if (requestsCounter = '0'){
            realLog.innerHTML += "Server, we are waiting for some fresh log info! <br/>";
        }
        // increase requests count and re-initialize sequences
        requestsCounter++;       
        setTimeout("process();", refresh  * 1000);            
    }           
}

// function called when the state of the HTTP request changes
function handleGettingLog() {
    // when readyState is 4, we are ready to read the server response
    if (xmlHttp.readyState == 4) {
        // continue only if HTTP status is "OK"
        if (xmlHttp.status == 200) {
            try {
                // do something with the reponse from the server
                getInfo();
            }
            catch(e)
            {
            alert("Error receiving fresh log info.\nError string: " + e.toString());
            }
        } else {
            alert("Error receiving fresh log info.\nHTTP status: " + xmlHttp.statusText);
        }
    }
}

// handles the response received from the server
function getInfo() {
    // retrieve the server's response 
    var response = xmlHttp.responseText;
    
    // obtain a reference to the <div> element on the HTML-page and display new looging data
    realLog = document.getElementById("realLogContainer");
  
    if (requestsCounter <= '1') {
       realLog.innerHTML += "<a class='navdown'  href='javascript:JumpDown()' title='Jump to bottom of page'>Down </a><br /><br />  "+ response +"";        
    } else {      
        realLog.innerHTML += ""+ response +"";
    }
    // increase requests count and re-initialize sequences
    requestsCounter++;
    
    if (response.indexOf("Close this window") != -1){
        refresh = "86400";
    }
    //alert("Refresh: " + refresh);
    JumpDown();
    // re-initialize sequence     
    setTimeout("process();", refresh * 1000);

}


function JumpUp() {
    window.scrollTo(0,-1000000);
}        

function JumpDown () {
    window.scrollTo(0, 100000);
}        
        
