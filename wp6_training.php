<?php

/*
    Plugin Name: Masdudung wp6_training
    Plugin URI: http://jadipesan.com/
    Description: Declares a plugin that will create a custom post type displaying post list.
    Version: 1.0
    Author: Moch Mufiddin
    Author URI: http://jadipesan.com/
    License: GPLv2
*/

class wp6_training {

    private $search_result = null;

    function __construct()
    {
        # register menu to admin page
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_my_frontend_script'] );
        
        #register ajax handler
        add_action( 'wp_ajax_search_post_handler', [$this, 'search_post_handler'] );
        add_action( 'wp_ajax_nopriv_search_post_handler', [$this, 'search_post_handler'] );

        #register sortcode
        add_shortcode( 'wp6_training', [$this, 'search_form_shortcode'] );
    }

    # sortcode design
    function search_form()
    {
        ?>
        <br>
        <form class="form-inline" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] );?>" method="post">
            <div class="form-group mb-2">
                <input type="text" autocomplete="off" class="form-control" id="post_title" name="post_title" value="<?php if( isset( $_POST["post_title"] ) ? esc_attr( $_POST["post_title"] ) : '' ) ?>">
                <button type="submit" name="do_search" class="btn btn-primary">search</button>
            </div>
        </form>
        <?php

        if($this->search_result)
        {
            var_dump($this->search_result);
        }
    }

    function do_search()
    {
        if(isset( $_POST['do_search'] ))
        {
            $post_title = sanitize_text_field( $_POST["post_title"] );

            global $wpdb; 
            $publish_posts = $wpdb->get_results( 
                "
                SELECT * 
                FROM $wpdb->posts
                WHERE post_title like '%". $post_title ."%' 
                AND post_status = 'publish'"
            );

            $this->search_result = $publish_posts;

        }
    }

    public function search_form_shortcode() {
        ob_start();
        $this->do_search();
        $this->search_form();
        return ob_get_clean();
    }


    function enqueue_my_frontend_script() {
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 
            'my-script', 
            plugin_dir_url(__FILE__).'/include/search_post.js', 
            array('jquery'), null, true 
        );
        
        $variables = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        );
        
        wp_localize_script('my-script', "test", $variables);
    }


    # ajax handler function
    function search_post_handler() 
    {
        $keyword = $_POST['keyword'];

        global $wpdb; 
        $publish_posts = $wpdb->get_results( 
            "
            SELECT post_title 
            FROM $wpdb->posts
            WHERE post_title like '%". $keyword ."%' 
            AND post_status = 'publish'"
        );

        $result = array();
        foreach ( $publish_posts as $publish_post ) 
        {
            array_push ($result, $publish_post->post_title);
        }

        echo json_encode(array('data'=>$result));

        wp_die(); // this is required to terminate immediately and return a proper response
    }

}

$plugin = new wp6_training();

