function addslashes(str) {
	str=str.replace(/\\/g,'\\\\');
	str=str.replace(/\'/g,'\\\'');
	str=str.replace(/\"/g,'\\"');
	str=str.replace(/\0/g,'\\0');
	return str;
}

function stripslashes(str) {
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\0/g,'\0');
	str=str.replace(/\\\\/g,'\\');
	return str;
}

function displayBlackDiv(text) {
	text = stripslashes(text);
	/**
	* Get the window width and height
	*/
	if( document.body && ( document.body.scrollWidth || document.body.scrollHeight ) ) {
        var pageWidth = document.body.scrollWidth;
        var pageHeight = document.body.scrollHeight;
    } else if( document.body.offsetWidth ) {
      var pageWidth = document.body.offsetWidth;
      var pageHeight = document.body.offsetHeight;
    } /*else {
       var pageWidth='100%';
       var pageHeight='100%';
    }*/
	
	
	/**
	* Create the divs
	*/
	var tbody = document.getElementsByTagName("body")[0];
    var tnode = document.createElement('div');
	var info = document.createElement('div');
	
	
	/*
	*
	* Set the style for the black div
	*/
	tnode.style.position='absolute';                 // Position absolutely
	tnode.style.top='0px';                           // In the top
	tnode.style.left='0px';                          // Left corner of the page
	tnode.style.overflow='hidden';                   // Try to avoid making scroll bars            
	tnode.style.width='100%';
	tnode.style.height='100%';
	tnode.style.background='#000000';
	tnode.style.opacity=0.3;
	tnode.id='darkenScreenObject';
	
	
	/**
	* Set the style for the div containing the info that needs to be displayed
	*/
	info.style.width = '600px';
	info.style.position = 'absolute';
	info.style.left = (pageWidth - 600) / 2 + 'px';
	info.style.top = "100px";
	info.style.zindex = '9999';
	info.style.background = '#FFFFFF';
	info.id = 'links';
    info.innerHTML = text;
	info.innerHTML += '<p align="right" style="padding:5px"><a href="javascript:;" onClick="document.getElementById(\'darkenScreenObject\').style.display=\'none\'; document.getElementById(\'links\').style.display=\'none\';">Close</a></p>';
	
	/**
	* Add the 2 divs to the DOM
	*/
	tbody.appendChild(tnode);
	tbody.appendChild(info);
}