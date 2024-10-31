<?php
//The following is codes for redo form
//create form function found in realanswers_function.php
//$rsapi from realanswers_rest_api.php

//populate form according to question_id which is a secret string id!
$sid = $_GET['question_id'];
//check form submit status
//if not submitted, means new redo query, proceed to populate form using redo get api
if (!isset($_POST['process'])) {

    //get data from apit
    global $rsapi;
    $redo_ques = $rsapi->redo($sid);

    if (isset($redo_ques['status_code']) && $redo_ques['status_code'] == '200') {
        if (isset($redo_ques['content']) && is_array($redo_ques['content']) && count($redo_ques['content']) > 0) {
            $title = (isset($redo_ques['content']['title']) ? $redo_ques['content']['title'] : '');
            $body = (isset($redo_ques['content']['body']) ? $redo_ques['content']['body'] : '');
            $context = (isset($redo_ques['content']['context']) ? $redo_ques['content']['context'] : '');
            $location = (isset($redo_ques['content']['location']) ? $redo_ques['content']['location'] : '');
            $fname = (isset($redo_ques['content']['fname']) ? $redo_ques['content']['fname'] : '');
            $lname = (isset($redo_ques['content']['lname']) ? $redo_ques['content']['lname'] : '');
            $email = (isset($redo_ques['content']['email']) ? $redo_ques['content']['email'] : '');

            $real_edit_form = create_realanswers_question_form('Edit Question', $context, $location, $title, $body, '', $fname, $lname, $email, '');
            return $real_edit_form;
        } else {
            $htm = "<div class='sidebar_error'>Service is Unavailable</div>";
        }
    } else {
        if (isset($redo_ques['content']) && $redo_ques['content'] != '') {
            $htm = "<div class='sidebar_error'>" . $redo_ques['content'] . "</div>";
        } else {
            $htm = "<div class='sidebar_error'>Service is Unavailable</div>";
        }
    }

}
?>