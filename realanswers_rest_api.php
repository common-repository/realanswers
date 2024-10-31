<?php
/**
 * Simple PHP class for REST API calls to RealtyBaron Real Answers API
 */

class realanswersapi
{

    //api key
    var $rs_apikey;

    //max result from wordpress admin option
    var $rs_max_results;

    //location type selected in wordpress admin
    var $rs_location_type;

    //location value entered in wordpress admin
    var $rs_location_valve;

    //location value2 entered in wordpress admin
    var $rs_location_valve2;

    //class constructor
    function __construct()
    {

        //get api key from wordpress admin options
        $this->rs_apikey = get_option('real_apikey');

        //get max_results from wordpress admin options
        $this->rs_max_results = get_option('real_max_results');


        //to get location type from wordpress option
        //$this->rs_location_type = get_option('real_Location_type');


        //get location value from wordpress admin option
        //$raw_location_value = get_option('real_location_value');

        //replace string to lowercase
        //$lowercase_location_value = strtolower($raw_location_value);

        //$this->rs_location_value = $lowercase_location_value;


    }

    //default sidebar question api call
    function sidebar_widget_question($location_type, $location_value)
    {

        $request_url = "http://api.realtybaron.com/answers/questions/find/";
        $request_url .= "$location_type?location=$location_value";
        $request_url .= "&start_index=0&max_results=$this->rs_max_results";

        //$request_url = urlencode($request_url);

        $arrContents = array();
        $contents = wp_remote_retrieve_body(wp_remote_get($request_url, array('headers' => array('x-api-key' => $this->rs_apikey))));

        if ($contents != '') {
            $arrContents = json_decode($contents, true);
        }

        return $arrContents;
    }

    //normal question api call
    function question($location_type, $location_value, $start_no, $max_res)
    {

        $request_url = "http://api.realtybaron.com/answers/questions/find/";
        $request_url .= "$location_type?location=$location_value";
        $request_url .= "&start_index=$start_no&max_results=$max_res";

        $arrContents = array();
        $contents = wp_remote_retrieve_body(wp_remote_get($request_url, array('headers' => array('x-api-key' => $this->rs_apikey))));

        if ($contents != '') {
            $arrContents = json_decode($contents, true);
        }

        return $arrContents;
    }

    //normal answer api call
    function answer($question_id)
    {

        $ip = urlencode($_SERVER["REMOTE_ADDR"]);
        $user_agent = urlencode($_SERVER["HTTP_USER_AGENT"]);

        $request_url = "http://api.realtybaron.com/answers/answers/$question_id?head_size=thumbnail&ip_address=$ip&user_agent=$user_agent";

        $arrContents = array();
        $response = wp_remote_get($request_url, array('headers' => array('x-api-key' => $this->rs_apikey)));
        $content = wp_remote_retrieve_body($response);
        $responseCode = wp_remote_retrieve_response_code($response);

        if ($responseCode == 200) {
            $content = json_decode($content, true);
        }

        return array('status_code' => $responseCode, 'content' => $content);
    }


    //normal answer api call with sort
    function answer_sort($question_id, $order)
    {

        $ip = $_SERVER["REMOTE_ADDR"];
        $user_agent = $_SERVER["HTTP_USER_AGENT"];
        $user_agent = urlencode($user_agent);

        $request_url = "http://api.realtybaron.com/answers/answers/$question_id?payload=html&user_agent=$user_agent&ip_address=$ip&order=$order";

        $arrContents = array();
        $response = wp_remote_get($request_url, array('headers' => array('x-api-key' => $this->rs_apikey)));
        $content = wp_remote_retrieve_body($response);
        $responseCode = wp_remote_retrieve_response_code($response);

        if ($responseCode == 200) {
            $content = json_decode($content, true);
        }

        return array('status_code' => $responseCode, 'content' => $content);
    }

    //redo api call
    function redo($sid)
    {

        $request_url = "http://api.realtybaron.com/answers/question/edit/$sid";

        $arrContents = array();
        $response = wp_remote_get($request_url, array('headers' => array('x-api-key' => $this->rs_apikey)));
        $content = wp_remote_retrieve_body($response);

        $responseCode = wp_remote_retrieve_response_code($response);

        if ($responseCode == 200) {
            $content = json_decode($content, true);
        }

        return array('status_code' => $responseCode, 'content' => $content);
    }

    //use php curl extension to post data to realtybaron post api and parse xml response.
    function post($context, $postdata)
    {

        $response = wp_remote_post("http://api.realtybaron.com/answers/question/$context", array('body' => $postdata, 'method' => 'POST', 'headers' => array('x-api-key' => $this->rs_apikey)));
        $arr = json_encode(array('status' => wp_remote_retrieve_response_code($response), 'response' => wp_remote_retrieve_body($response)));

        return $arr;
    }


    //use php curl extension to post data to realtybaron add agent api and parse xml response.
    function register_api_key($role, $postdata)
    {
        $response = wp_remote_post("http://api.realtybaron.com/answers/user/add/$role", array('body' => $postdata, 'method' => 'POST'));
        return json_encode(array('status' => wp_remote_retrieve_response_code($response), 'response' => wp_remote_retrieve_body($response)));
    }

    function get_location($type, $q)
    {
        $url = "http://api.realtybaron.com/answers/locations/find/$type?name=$q";

        $arrContents = array();
        $contents = wp_remote_retrieve_body(wp_remote_get($url, array('headers' => array('x-api-key' => $this->rs_apikey))));

        if ($contents != '') {
            $arrContents = json_decode($contents, true);
        }

        return $arrContents;

    }


}//end of class

//set new realanswersapi class in $rsapi variable for later use
//example usage;
//global $rsapi
//$response = $rsapi->question('metro','austin-san+marcos,%20tx',0,2);

if (!isset($rsapi)) {

    $rsapi = new realanswersapi;
}
?>