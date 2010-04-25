<?php
	$fd = fopen("D:/test.txt","a");
	fwrite($fd,'mata '.$_POST['uid'].$_POST['url']);
	fclose($fd);
?>