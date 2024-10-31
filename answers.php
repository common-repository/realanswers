<?php

$rs_question_id = (isset($_GET['question_id']) ? $_GET['question_id'] : '');
//for mini form
$rs_location_type = (isset($_GET['type']) ? $_GET['type'] : '');
$rs_location_value = (isset($_GET['value']) ? $_GET['value'] : '');
$rs_order = '';
if (isset($_GET['order'])) {
    $rs_order = $_GET['order'];
    $sort = 'true';
} else {
    $sort = 'false';
}
//verified $rs_question_id is a number
if (is_numeric($rs_question_id)) {
    global $rsapi;
    if ($sort == 'true') {
        $rs_answers = $rsapi->answer_sort($rs_question_id, $rs_order);
    } elseif ($sort == 'false') {
        $rs_answers = $rsapi->answer($rs_question_id);
    }

    if (isset($rs_answers['status_code']) && $rs_answers['status_code'] == '200') {
        if (isset($rs_answers['content']) && is_array($rs_answers['content']) && count($rs_answers['content']) > 0) {

            $question = ((isset($rs_answers['content']['question']) && is_array($rs_answers['content']['question'])) ? $rs_answers['content']['question'] : array());

            $title = (isset($question['title']) ? $question['title'] : '');

            grab_title_for_wp_title($title);

            $body = (isset($question['body']) ? $question['body'] : '');

            $html = "<div class='realanswers_answers'>";
            $header = "<h2 class=\"answers_title\">" . $title . "</h2>";

            $googleApi = trim(get_option('real_google_map_api'));

            if ($googleApi != '') {
                if (isset($rs_answers['content']['links']) && isset($rs_answers['content']['links']['link']) && is_array($rs_answers['content']['links']['link']) && count($rs_answers['content']['links']['link']) > 0) {
                    foreach ($rs_answers['content']['links']['link'] as $curLink) {
                        if (isset($curLink['id']) && ($curLink['id'] == 'staticmap' || $curLink['id'] == 'streetview') && isset($curLink['href']) && trim($curLink['href']) != '') {
                            $header = "<div id=\"location-view-header\" class=\"realanswers-header\" style=\"background:linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('" . $curLink['href'] . $googleApi . "'); background-size:cover;\">";
                            $header .= "<div style=\"width: 100%; position: absolute; top: 1em; left: 50%; transform: translate(-50%, 0%);\">";
                            $header .= "<div style=\"font-size:2em; font-weight: bold; text-shadow: 1px 1px 1px #000;\">" . $title . "</div>";
                            $header .= "<div style=\"font-size:1em; font-weight: bold; text-shadow: 1px 1px 1px #000;\">" . $rs_location_value . "</div>";
                            $header .= "</div>";
                            $header .= "</div>";
                        }
                    }
                }
            }

            $html .= $header;
            $html .= "<div class=\"answers_body\" style=\"margin-top:1em; font-style:italic;\">" . $body . "</div>";

            if (isset($rs_answers['content']['links']) && isset($rs_answers['content']['links']['link']) && is_array($rs_answers['content']['links']['link']) && count($rs_answers['content']['links']['link']) > 0) {
                foreach ($rs_answers['content']['links']['link'] as $link) {
                    if (isset($link['rel'])) {
                        if ($link['rel'] == 'canonical') {
                            $html .= "<div style=\"clear:both; margin-top:1em;\">";
                            $html .= "<a href=\"" . (isset($link['href']) ? $link['href'] : "") . "\" rel=\"" . $link['rel'] . '\" class=\"answers_link\">' . (isset($link['text']) ? $link['text'] : "") . "</a>";
                            $html .= "</div>";
                        } else if ($link['rel'] == 'response') {
                            $html .= "<div style=\"clear:both; margin-top:1em; text-align:center;\">";
                            $html .= "<button onclick=\"window.location.href='" . (isset($link['href']) ? $link['href'] : '') . "'\">" . (isset($link['text']) ? $link['text'] : '') . "</button>";
                            $html .= "</div>";
                        }
                    }
                }
            }

            $ans_url_structure = get_bloginfo('url') . "/realanswers/answers";

            $arrAnswers = ((isset($rs_answers['content']['answers']) && isset($rs_answers['content']['answers']['answer']) && is_array($rs_answers['content']['answers']['answer'])) ? $rs_answers['content']['answers']['answer'] : array());

            if (count($arrAnswers) > 0) {
                $html .= "<div class=\"realanswers_sort\" style=\"clear:both; margin-top:1em;\>";
                $html .= "Sort by:  ";
                if ($rs_order != 'revenue') {
                    $html .= "<a href='$ans_url_structure?question_id=$rs_question_id&type=$rs_location_type&value=$rs_location_value&order=revenue' class='answers_sort_link'>Default</a> |";
                } else {
                    $html .= "Default |";
                }
                if ($rs_order != 'ranking') {
                    $html .= " <a href='$ans_url_structure?question_id=$rs_question_id&type=$rs_location_type&value=$rs_location_value&order=ranking' class='answers_sort_link'>Ranking</a> |";
                } else {
                    $html .= " Ranking |";
                }
                if ($rs_order != 'rating') {
                    $html .= " <a href='$ans_url_structure?question_id=$rs_question_id&type=$rs_location_type&value=$rs_location_value&order=rating' class='answers_sort_link'>Rating</a> |";
                } else {
                    $html .= " Rating |";
                }
                if ($rs_order != 'recent') {
                    $html .= " <a href='$ans_url_structure?question_id=$rs_question_id&type=$rs_location_type&value=$rs_location_value&order=recent' class='answers_sort_link'>Recent</a> |";
                } else {
                    $html .= " Recent |";
                }
                if ($rs_order != 'oldest') {
                    $html .= " <a href='$ans_url_structure?question_id=$rs_question_id&type=$rs_location_type&value=$rs_location_value&order=oldest' class='answers_sort_link'>Oldest</a> ";
                } else {
                    $html .= " Oldest ";
                }
                $html .= "</div><br/>";

                foreach ($arrAnswers as $answer) {
                    if (isset($answer['content']) && is_array($answer['content']) && count($answer['content']) > 0) {
                        foreach ($answer['content'] as $answerContent) {
                            $html .= (isset($answerContent['value']) ? $answerContent['value'] : '');
                        }
                    }
                }
            } else {
                $html .= "<div style=\"clear:both; margin-top:1em; text-align:center;\"> This question is still awaiting answers</div>";
            }

            $html .= "</div>";

        } else {
            $html = "<div class='sidebar_error'>Service is Unavailable</div>";
        }
    } else {
        $html = "<div class='sidebar_error'>" . ((isset($rs_answers['content']) && $rs_answers['content'] != '') ? $rs_answers['content'] : 'Service is Unavailable') . "</div>";
    }

    return $html;
}
?>