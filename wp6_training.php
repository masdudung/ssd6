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

        // The Loop
        if($this->search_result)
        {
            if ( $this->search_result->have_posts() ) {
                echo '<ul>';
                while ( $this->search_result->have_posts() ) {
                    $this->search_result->the_post();
                    echo '<li>' . get_the_title() . '</li>';
                }
                echo '</ul>';
                $this->pagination($this->search_result);
            } else {
                echo "no posts found";
            }
        }
    }

    function do_search()
    {
        if(isset( $_POST['do_search'] ))
        {
            // $get_post_title = isset($_GET['post_title']) ? sanitize_text_field( $_GET['post_title'] ) : ''; 
            $post_title = isset($_POST['post_title']) ? sanitize_text_field( $_POST['post_title'] ) : '';  
 
            $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
            $args = array(
                'posts_per_page' => 3,
                'post_type' => 'post',
                'post_status' => 'publish',
                'search_post_title' => $post_title,
                'paged' => $paged,
            );
            
            add_filter( 'posts_where', [$this, 'title_filter'], 10, 2 );
            $the_query = new WP_Query( $args );
            remove_filter( 'posts_where', [$this, 'title_filter'], 10, 2 );
            
            $this->search_result = $the_query; 
            // wp_reset_postdata();

        }
    }

    function pagination($wp_query)
    {
        // global $wp_query;
        $big = 999999999; // need an unlikely integer
        echo paginate_links( array(
            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format' => '?paged=%#%&sasa=lala',
            'current' => max( 1, get_query_var('paged') ),
            'total' => $wp_query->max_num_pages
        ) );
    }

    function title_filter( $where, $wp_query )
    {
        global $wpdb;
        if ( $search_term = $wp_query->get( 'search_post_title' ) ) {
            $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like( $search_term )  . '%\'';
        }
        return $where;
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
        $keyword = isset($_POST['keyword']) ? sanitize_text_field( $_POST['keyword'] ) : '';  

        global $wpdb; 
        $publish_posts = $wpdb->get_results( 
            "
            SELECT post_title 
            FROM $wpdb->posts
            WHERE post_title like '%". $keyword ."%' 
            AND post_type = 'post'
            AND post_status = 'publish'"
        );

        $result = array();
        foreach ( $publish_posts as $publish_post ) 
        {
            array_push ($result, $publish_post->post_title);
        }

        wp_send_json( $result );
    }

}

$plugin = new wp6_training();

