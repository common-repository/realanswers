<?php
/*
*This file uses curl to get a JSON response of location values from the Realtybaron API
*The JSON response is parsed into HTML List for scriptaculous autocompleter.
*/
	//include wp-config
	$root = dirname(dirname(dirname(dirname(__FILE__))));
	if (file_exists($root.'/wp-load.php')) {
	// WP 2.6
	require_once($root.'/wp-load.php');
	} else {
	// Before 2.6
	require_once($root.'/wp-config.php');
	}

	//type parameter, excepts metro, city, zipcode, address.
	$type =  $_GET["type"];
	
	$q = $_GET['q'];

	//get api key from options table
	$real_api_keyy = get_option('real_apikey');
	
	//check api key
	if(empty($real_api_keyy)){
	//if empty stop doing api request!
	//show error message!
	echo"<script type=\"text/javascript\">";
	echo"$('#error_dialog').empty();";
	echo"$('#error_dialog').dialog('destroy');";
	echo"$('#error_dialog').html('<p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 20px 0;\"></span>Please update API Keys before filling up location values!</p><p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 20px 0;\"></span>Location value Auto Completer will not work without API Key!</p>');";
	echo"$('#error_dialog').dialog({modal: true,title: 'Attention!',buttons: { Ok: function() {									$(this).dialog('close');}}});";
	echo"</script>";
	echo"Required API Key!";
	die();
	}

$contents = $rsapi->get_location($type, $q);

if (isset($contents['location']) && is_array($contents['location']) && count($contents['location']) > 0) {
    foreach ($contents['location'] as $location) {
        if (isset($location['name']) && $location['name'] != '') {
            echo $location['name'] . PHP_EOL;
        }
    }
}

?>  