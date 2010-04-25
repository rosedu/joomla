/**
* Open a SqueezeBox and set the contents to the asLinks div
*/
function loadAsPopup(text) {
	// init the popup
	SqueezeBox.initialize();
	
	// set the content
	SqueezeBox.open($('asLinks'), {
		handler: 'adopt',
		//onClose:function(){alert('mama')},
		size: {x: 300, y: document.getElementById('asLinks').height} // a trick to set height to auto
	});
}