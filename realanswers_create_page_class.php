<?php
/**
 * Forked the Fakepage class code From Fake Page Plugin 2 by Scott Sherrill-Mix
 * http://scott.sherrillmix.com/blog/blogger/creating-a-better-fake-post-with-a-wordpress-plugin/
 * Modified code to create questions and answers page (fake page create by class and not from database)
 */

//This Class Creates the Questions and Answers Page
class Create_RealAnswers_Page
{
    /**
     * The slug for the fake post.  This is the URL for your plugin, like:
     * http://site.com/about-me or http://site.com/?page_id=about-me
     * @var string
     */
    var $page_slug;

    /**
     * The title for your fake post.
     * @var string
     */
    var $page_title;

    /**
     * Allow pings?
     * @var string
     */
    var $ping_status = 'closed';

    /**
     * Added in by Denzel to determine content
     * Determine Content?
     * @var string
     */
    var $post_content_type;

    /**
     * Class constructor
     */
    function __construct($slug, $title, $con)
    {
        /**
         * We'll wait til WordPress has looked for posts, and then
         * check to see if the requested url matches our target.
         */
        add_filter('the_posts', array(&$this, 'detectPost'));

        /**
         *Modification by Denzel
         *Added in variable constructor to make class reusable
         */

        $this->page_slug = $slug;
        $this->page_title = $title;
        $this->post_content_type = $con;
    }


    /**
     * Called by the 'detectPost' action
     */
    function createPost()
    {

        /**
         * What we are going to do here, is create a fake post.  A post
         * that doesn't actually exist. We're gonna fill it up with
         * whatever values you want.  The content of the post will be
         * the output from your plugin.
         */

        /**
         * Create a fake post.
         */
        $post = new stdClass;

        /**
         * The author ID for the post.  Usually 1 is the sys admin.  Your
         * plugin can find out the real author ID without any trouble.
         */
        $post->post_author = 0;

        /**
         * The safe name for the post.  This is the post slug.
         */
        $post->post_name = $this->page_slug;

        /**
         * Not sure if this is even important.  But gonna fill it up anyway.
         */
        $post->guid = get_bloginfo('wpurl') . '/' . $this->page_slug;


        /**
         * The title of the page.
         */
        $post->post_title = $this->page_title;

        /**
         * This is the content of the post.  This is where the output of
         * your plugin should go.  Just store the output from all your
         * plugin function calls, and put the output into this var.
         */
        $post->post_content = $this->getContent();

        /**
         * Fake post ID to prevent WP from trying to show comments for
         * a post that doesn't really exist.
         */
        $post->ID = -2;

        /**
         * Static means a page, not a post.
         */
        $post->post_status = 'static';

        /**
         * Turning off comments for the post.
         */
        $post->comment_status = 'closed';

        /**
         * Let people ping the post?  Probably doesn't matter since
         * comments are turned off, so not sure if WP would even
         * show the pings.
         */
        $post->ping_status = $this->ping_status;

        $post->comment_count = 0;

        /**
         * You can pretty much fill these up with anything you want.  The
         * current date is fine.  It's a fake post right?  Maybe the date
         * the plugin was activated?
         */
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);

        remove_filter('the_content', 'wpautop'); // keep wordpress from automatically adding unnecessary html, such as <br/> and <p>

        return ($post);
    }

    function getContent()
    {
        /**
         * Added in by Denzel to determine which page to show, when requested http get .
         * This individual pages will use realanswersapi class to request xml data and parse response.
         */
        $con = $this->post_content_type;
        if ($con == "question") {
            //include questions page if questions api invoked by http get
            $question = include(WP_PLUGIN_DIR . '/realanswers/questions.php');
            return $question;
        } elseif ($con == 'answer') {
            //include answers page if answers api invoked by http get
            $answer = include(WP_PLUGIN_DIR . '/realanswers/answers.php');
            return $answer;
        } elseif ($con == 'newquestion') {
            //include newquestions page if newquestions api invoked by http get
            $newquestions = include(WP_PLUGIN_DIR . '/realanswers/newquestions.php');
            return $newquestions;
        } elseif ($con == 'redo') {
            //include redo page if redo api invoked by http get
            $redo = include(WP_PLUGIN_DIR . '/realanswers/redo.php');
            return $redo;
        };
    }

    function detectPost($posts)
    {
        global $wp;
        global $wp_query;

        /**
         * Check if the requested page matches our target
         */
        if (strtolower($wp->request) == strtolower($this->page_slug) || (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == $this->page_slug)) {
            //Add the fake post
            $posts = NULL;
            $posts[] = $this->createPost();

            /**
             * Trick wp_query into thinking this is a page (necessary for wp_title() at least)
             * Not sure if it's cheating or not to modify global variables in a filter
             * but it appears to work and the codex doesn't directly say not to.
             */
            $wp_query->is_page = true;
            //Not sure if this one is necessary but might as well set it like a true page
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            //Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
            unset($wp_query->query["error"]);
            $wp_query->query_vars["error"] = "";
            $wp_query->is_404 = false;

        }
        return $posts;
    }
}

/**
 * Create an instance of our class.
 */
new Create_RealAnswers_Page('realanswers/questions', '', 'question');
new Create_RealAnswers_Page('realanswers/answers', '', 'answer');
new Create_RealAnswers_Page('realanswers/newquestions', '', 'newquestion');
new Create_RealAnswers_Page('realanswers/redo', '', 'redo');
?>