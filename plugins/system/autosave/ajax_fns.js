/**************************************
*  @author: NiGhTCrAwLeR
*  @copyright: Powered by NiGhTCrAwLeR
*  @copyright: GPL
***************************************/

/**
* Create an AJAX Request
*/
function createREQ() {
	try {
		req = new XMLHttpRequest();
	} catch(e) {
		try {
			req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(e) {
			try {
				req = new ActiveXObject("Micosoft.XMLHTTP");
			} catch(e) {
				req = false;
			}
		}
	}
	return req;
}

/**
* Send data using GET
*/
function requestGET(url, query, req) {
	rand = parseInt(Math.random()*99999999);
	req.open("GET",url+'?'+query+'&rand='+rand,true);
	req.send(null);
}

/**
* Send data using POST
*/
function requestPOST(url, query, req) {
	req.open("POST",url,true);
	req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	req.send(query);
}

/**
* Process the response
*/
function doCallback(fct, param) {
	if (!fct)
		return;
	eval(fct + '(param)');	
}

/**
* Call AJAX
*/
function callAjax(url, method, query, callback) {
	var req = createREQ();
	
	req.onreadystatechange = function() {
		if (req.readyState == 4) {
			if (req.status == 200) {
				var param = req.responseText;
				doCallback(callback, param);
			}
		}
	}
	
	if (method == "POST")
		requestPOST(url,query,req);
	else if (method == "GET")
		requestGET(url,query,req);
	else return false;
}