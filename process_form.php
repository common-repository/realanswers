<?php
//post data to realtybaron realanswers post api using PHP CURL extension.
//process response from realtybaron api to determine status of form submission
//whether it is success or bad request!

//include wp-config
$root = dirname(dirname(dirname(dirname(__FILE__))));
if (file_exists($root.'/wp-load.php')) {
// WP 2.6
require_once($root.'/wp-load.php');
} else {
// Before 2.6
require_once($root.'/wp-config.php');
}

/*******************The below checks post from form********************/

if (isset($_POST['process']) && $_POST['process'] == 'yes_process') {

    //check wp_nonce first!
    //nonce pass from webpage
    $nonce_value = (isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '');
    //security check using nonce created by wp_create_nonce('realanswers-nonce');
    //so as to determine data come from webpage
    if (!wp_verify_nonce($nonce_value, 'realanswers-nonce')) die('Failed Security check');

    //data posted from form assigned to variables to be posted to realtybaron post api
    $context = (isset($_POST['location_type']) ? $_POST['location_type'] : '');
    $location = (isset($_POST['location_name']) ? $_POST['location_name'] : '');
    $subject = (isset($_POST['subject']) ? $_POST['subject'] : '');
    $body = (isset($_POST['question_detail']) ? $_POST['question_detail'] : '');

    $notify_me = (isset($_POST['notify_me']) ? 'true' : 'false');
    $open_days = 3;
    $siteUrl = get_bloginfo('url');
    $construct_redo_url = $siteUrl . "/realanswers/redo?question_id={0}";
    $redo_url = $construct_redo_url;
    $construct_answer_url = $siteUrl . "/realanswers/answers?question_id={0}";
    $answer_url = $construct_answer_url;
    $construct_append_url = $siteUrl . "/realanswers/answers?question_id={0}";
    $append_url = $construct_append_url;
    $api_key = get_option('real_apikey');
    $fname = (isset($_POST['fname']) ? $_POST['fname'] : '');
    $lname = (isset($_POST['lname']) ? $_POST['lname'] : '');
    $email = (isset($_POST['email']) ? $_POST['email'] : '');
    $tou = (isset($_POST['tou']) ? $_POST['tou'] : '');

    //construct post data to be posted to realtybaron post api
    $postdata = array(
        "location" => $location,
        "subject" => $subject,
        "body" => $body,
        "notify_me" => $notify_me,
        "open_days" => $open_days,
        "redo_url" => $redo_url,
        "answer_url" => $answer_url,
        "append_url" => $append_url,
        "fname" => $fname,
        "lname" => $lname,
        "email" => $email,
        "tou" => $tou
    );

    $url_content = "context=$context&location=$location&subject=$subject&body=$body";
    $url_content .= "&notify_me=$notify_me&fname=$fname&lname=$lname&email=$email";


    global $rsapi;

    $post_response = $rsapi->post($context, $postdata);
    $post_response = json_decode($post_response, true);

    if (isset($post_response['status']) && $post_response['status'] == '200') {

        if (isset($post_response['response']) && $post_response['response'] != '') {
            $arrResponse = json_decode($post_response['response'], true);

            $redirectUrl = get_bloginfo('url') . "/realanswers/newquestions/?status=redo&status_message=Service Unavailable&$url_content";
            if (is_array($arrResponse['links']) && is_array($arrResponse['links']['link']) && count($arrResponse['links']['link']) > 0) {
                foreach ($arrResponse['links']['link'] as $link) {
                    if (isset($link['rel']) && $link['rel'] == 'answers' && isset($link['href']) && trim($link['href']) != '') {
                        $redirectUrl = $link['href'];
                    }
                }
            }

            wp_redirect($redirectUrl);
            die;
        }

    } else {
        if (isset($post_response['response']) && $post_response['response'] != '') {
            $redo_url = get_bloginfo('url') . "/realanswers/newquestions";
            header("location:$redo_url/?status=redo&status_message=" . $post_response['response'] . "&$url_content");
            exit;
        } else {
            $redo_url = get_bloginfo('url') . "/realanswers/newquestions";
            header("location:$redo_url/?status=redo&status_message=Service Unavailable&$url_content");
            exit;
        }
    }

}//end if(isset($_POST['process'])== 'yes_process')


/**Since version 2.1 process API registration form!*********************/

if(isset($_POST['process_register_form'])== 'yes_process'){

	$nonce_value = $_POST['_wpnonce'];
	
	if (!wp_verify_nonce($nonce_value, 'realanswers_ajax_nonce') ) die('Failed Security check');
	$fname = $_POST['fname'];
	$lname = $_POST['lname'];
	$email = $_POST['email'];
	$role = $_POST['role'];

    $postdata = array("fname" => $fname, "lname" => $lname, "email" => $email);

	global $rsapi;
	 
	$post_response = $rsapi->register_api_key($role,$postdata);

    $post_response = json_decode($post_response, true);

    //check if empty response from api response with service unavailable message
    if (empty($post_response)) {
        echo '<div id="register_error_message" class="updated fade"><p><strong>Sorry, Service Unavailable.';
        echo ' Please try again later</p></strong></div>';
        die();
    }

    $apiResponse = (isset($post_response['response']) ? $post_response['response'] : '');
    $apiResponseStatus = (isset($post_response['status']) ? $post_response['status'] : '');

    if (in_array($apiResponseStatus, array('200', '201'))) {
        $apiResponse = json_decode($apiResponse, true);

        $apiKey = (isset($apiResponse['id']) ? trim($apiResponse['id']) : '');

        if ($apiKey != '') {
            update_option('real_apikey', $apiKey);

            //echo script to hide admin warning
            echo "<script type='text/javascript'>$('#realanswers-warning').hide();$('#register_form_back_link').hide();</script>";

            $admin_setting_url = admin_url() . "options-general.php?page=realanswers_admin.php";

            //echo success message in header!
            echo "<div id=\"message\" class=\"updated fade\">";
            echo "<strong><p>Your registration was successful! <a style=\"text-decoration: none;\" href=\"$admin_setting_url\">Please click here to setup remaining options.</a></p></strong>";
            echo "</div>";
        } else {
            echo '<div id="register_error_message" class="updated fade"><p><strong>Error generating API Key.</p></strong></div>';
        }
    } else {
        echo '<div id="register_error_message" class="updated fade"><p><strong>' . $apiResponse . '</p></strong></div>';
    }
    die;
}//end if(isset($_POST['process_register_form'])== 'yes_process')











































?>