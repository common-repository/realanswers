<?php

// Number of records to show per page:
$page_size = 10;
$location_type = $_GET['type'];
$location_value = $_GET['value'];
$max_linked_pages = 10;

//in this plugin
//note that start is start page
//starts from 0 index == page 1
//not the normal start record number.
if (isset($_GET['start'])) {
    $start = $_GET['start'];
} else {
    $start = 0;
}

//use realanswers rest api class to request xml response
global $rsapi;

$html = '';

$rs_questions = $rsapi->question($location_type, $location_value, $start, $page_size);

if (isset($rs_questions['questions']) && isset($rs_questions['questions']['question']) && is_array($rs_questions['questions']['question']) && count($rs_questions['questions']['question']) > 0) {

    $total_results = (isset($rs_questions['questions']['total_results']) ? $rs_questions['questions']['total_results'] : 0);

    // Calculate the number of pages.
    if ($total_results < $page_size) { // Just 1 page.
        $num_pages = 1;
    } else {
        $num_pages = ceil($total_results / $page_size);// use ceil to round up to nearest number.
    }

    $html = "<div class='realanswers_questions'>";

    //grab the location value of this page for use as <title>
    //to filter wp_title, this function is declared on realanswers_function.php            
    get_question_title($location_value);

    $header = "<h2 class='question_post_title'>Recent Questions in $location_value</h2>";

    $googleApi = trim(get_option('real_google_map_api'));

    if ($googleApi != '') {
        if (isset($rs_questions['links']) && isset($rs_questions['links']['link']) && is_array($rs_questions['links']['link']) && count($rs_questions['links']['link']) > 0) {
            foreach ($rs_questions['links']['link'] as $curLink) {
                if (isset($curLink['id']) && ($curLink['id'] == 'staticmap' || $curLink['id'] == 'streetview') && isset($curLink['href']) && trim($curLink['href']) != '') {
                    $header = "<div id=\"location-view-header\" class=\"realanswers-header\" style=\"background:linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('" . $curLink['href'] . $googleApi . "'); background-size:cover;\">";
                    $header .= "<div style=\"width: 100%; position: absolute; top: 1em; left: 50%; transform: translate(-50%, 0%);\">";
                    $header .= "<div style=\"font-size:2em; font-weight: bold; text-shadow: 1px 1px 1px #000;\">" . $location_value . "</div>";
                    $header .= "<div style=\"font-size:1em; font-weight: bold; text-shadow: 1px 1px 1px #000;\">" . $location_type . "</div>";
                    $header .= "</div>";
                    $header .= "</div>";
                }
            }
        }
    }

    $html .= $header;

    //url structure to answers
    $ans_url_structure = get_bloginfo('url') . "/realanswers/answers";

    $assign_answer_link = array();
    $assign_source_link = array();

    foreach ($rs_questions['questions']['question'] as $question) {
        $id = (isset($question['id']) ? $question['id'] : '');
        $title = (isset($question['title']) ? $question['title'] : '');
        $answers = (isset($question['answers']) ? $question['answers'] : 0);

        $html .= "<h3 class='question_title'><a href='$ans_url_structure?question_id=$id&type=$location_type&value=$location_value'>" . $title . "</a></h3>";

        $links = ((isset($question['links']) && isset($question['links']['link']) && is_array($question['links']['link']) && count($question['links']['link']) > 0) ? $question['links']['link'] : '');

        $assign_answer_link = array();
        $assign_source_link = array();

        for ($i = 0; $i < 4; $i++) {

            if (isset($links[$i]) && is_array($links[$i]) && count($links[$i]) > 0) {
                $link_title = (isset($links[$i]['title']) ? $links[$i]['title'] : '');
                $text = (isset($links[$i]['text']) ? $links[$i]['text'] : '');
                $rel = (isset($links[$i]['rel']) ? $links[$i]['rel'] : '');
                $href = (isset($links[$i]['href']) ? $links[$i]['href'] : '');

                //if rel='response' echo as "Answer this question" link
                if ($rel == 'response') {
                    $assign_answer_link = array($link_title, $text, $rel, $href);
                }//end if

                //if rel='canonical' echo as Source:Example.com
                if ($rel == 'canonical') {
                    $assign_source_link = array($link_title, $text, $rel, $href);
                }//end if
            }


        }//end for loop

        $html .= "<div>Asked about <a href=\"" . get_bloginfo('url') . "/realanswers/questions?type=" . $question['context'] . "&value=" . $question['location'] . "\">" . $question['location'] . "</a></div>";

        $html .= "<div>";
        $html .= "<img src=\"" . WP_PLUGIN_URL . "/realanswers/ic_action_monolog.png\" style=\"width:2em; height:2em; display:inline-block;\" />";
        $html .= "<span style=\"color: #858585; vertical-align: middle;\">" . $answers . "</span>";
        $html .= "<img src=\"" . WP_PLUGIN_URL . "/realanswers/ic_action_heart.png\" style=\"width:2em; height:2em; display:inline-block;\" />";
        $html .= "<span style=\"color: #858585; vertical-align: middle;\">" . $question['likes'] . "</span>";
        $html .= "<img src=\"" . WP_PLUGIN_URL . "/realanswers/ic_action_user.png\" style=\"width:2em; height:2em; display:inline-block;\" />";
        $html .= "<span style=\"color: #858585; vertical-align: middle;\">" . $question['followers'] . "</span>";
        $html .= "</div>";
    }

    //url structure to questions
    $q_url_structure = get_bloginfo('url') . "/realanswers/questions";

    // Make the links to other pages, if necessary.
    if ($num_pages > 1) {

        $html .= "<div class='realanswers-pagination'>";

        // Determine what page the script is on.
        $current_page = $start;

        // If it's not the first page, make a Previous button.
        if ($current_page != 0) {
            $html .= "<span class=\"previous realanswers-page-numbers\" ><a href=\"$q_url_structure?start=" . ($start - 1) . "&type=" . $location_type . "&value=" . $location_value . "\">Previous</a></span> ";
        }

        $first_linked_page = max(0, $current_page - ($max_linked_pages / 2));
        $last_linked_page = min($first_linked_page + $max_linked_pages - 1, $num_pages - 1);

        // Make all the numbered pages.
        for ($i = $first_linked_page; $i <= $last_linked_page; $i++) {
            if ($i != $current_page) {
                $html .= "<span class=\"realanswers-page-numbers\" ><a href=\"$q_url_structure?start=" . $i . "&type=" . $location_type . "&value=" . $location_value . "\">" . ($i + 1) . "</a></span> ";
            } else {
                $html .= '<span class="realanswers-page-numbers current">';
                $html .= ($i + 1);
                $html .= '</span>';
                $html .= ' ';
            }
        }

        // If it's not the last page, make a Next button.
        if (($current_page + 1) != $num_pages) {
            $html .= "<span class=\"next realanswers-page-numbers\" ><a href=\"$q_url_structure?start=" . ($current_page + 1) . "&type=" . $location_type . "&value=" . $location_value . "\">Next</a></span>";
        }

        $html .= "</div>";

    } // End of links section.

    $html .= "</div>";

} else {
    $html = "<div class='sidebar_error'>Questions not found!</div>";
}

return $html;