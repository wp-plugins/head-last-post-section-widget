<?php

/*
Plugin Name: Head Last Post Section Widget
Plugin URI: https://wordpress.org/plugins/head-last-post-section-widget/
Description: Select a post or page and display the first "section"(as you put the delimiters) in a widget.
Version: 1.01
Author: lion2486
Author URI: http://codescar.eu
License: GPLv2
*/

/**
 * Class HeadLastPostSection_Widget
 * @description Wordpress Widget plugin to display "sections" of a post/page.
 * @property String $text_domain The plugin's translation text domain.
 */
class HeadLastPostSection_Widget extends WP_Widget {

    private $text_domain = "head-last-post-section-widget";

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        // widget actual processes
        parent::__construct(
            'HeadLastPostSection', // Base ID
            __( 'Last Post Section Widget', $this->text_domain ), // Name
            array( 'description' => __( 'Head Last Post Section of a post/page in a widget.', $this->text_domain ), ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance )
    {
        // outputs the content of the widget
        echo $args['before_widget'];

        //display the title of the widget
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        //display #number section of the post/page
        $post = get_post($instance['post']);
        $content = trim( strip_tags( $post->post_content ) );

        $sections = preg_split( '/(' . $instance['pattern'] . ')/', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

        echo "<ul>\n";
        for ($i = 0; $i < 2 * $instance['number']; $i += 2) {

            if( ! empty($sections[$i])  )
                echo "<li>" . $sections[$i] . " " . str_replace( "\n", "<br/>", trim( $sections[$i+1] ) ) . "</li>\n";

        }

        echo "</ul>";

        //display a "read more" link if it's enabled
        if( $instance['link_to_post'] )
            echo "<a href=\"" . get_permalink( $instance['post'] ) . "\">" . $instance['link_title'] . "</a>";

        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     *
     * @return void
     */
    public function form( $instance ) {
        // outputs the options form on admin
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', $this->text_domain );
        $post = ! empty( $instance['post'] ) ? $instance['post'] : 0;
        $pattern = ! empty( $instance['pattern'] ) ? $instance['pattern'] : '';
        $number = ! empty( $instance['number'] ) ? $instance['number'] : 1;
        $link_to_post = ! empty( $instance['link_to_post'] ) ? $instance['link_to_post'] : 0;
        $link_title = ! empty( $instance['link_title'] ) ? $instance['link_title'] : 'Read more...';

        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        );
        $pages = get_pages($args);

        $args = array(
            'posts_per_page'   => 50,
            'offset'           => 0,
            'category'         => '',
            'category_name'    => '',
            'orderby'          => 'post_date',
            'order'            => 'DESC',
            'include'          => '',
            'exclude'          => '',
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => 'post',
            'post_mime_type'   => '',
            'post_parent'      => '',
            'post_status'      => 'publish',
            'suppress_filters' => true
        );
        $posts = get_posts( $args );
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            <br/>

            <label for=""><?php _e( 'Select Post/Page:', $this->text_domain ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'post' ); ?>" name="<?php echo $this->get_field_name( 'post' ); ?>">
                <option> -- SELECT -- </option>
                <?php
                echo "<optgroup label=\"" . __( 'Pages', $this->text_domain ) . "\">";

                foreach( $pages as $p )
                    echo "<option value=\"" . $p->ID . "\" " . ( $p->ID == $post ? "selected=\"selected\"" : "" ) .">" . $p->post_title . "</option>\n";

                echo "</optgroup>";

                echo "<optgroup label=\"" . __( 'Posts', $this->text_domain ) . "\">";

                foreach( $posts as $p )
                    echo "<option value=\"" . $p->ID . "\" " . ( $p->ID == $post ? "selected=\"selected\"" : "" ) .">" . $p->post_title . "</option>\n";

                echo "</optgroup>";

                ?>
            </select>
            <br/>

            <label for=""><?php _e( 'Text section pattern:', $this->text_domain ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'pattern' ); ?>" name="<?php echo $this->get_field_name( 'pattern' ); ?>" type="text" value="<?php echo esc_attr( $pattern ); ?>">
            <span>
                <h4>Pattern Help</h4>
                Use a pattern to apply to function <a href="http://php.net/manual/fr/function.preg-split.php">preg_split</a> without the starting and ending '/' Like:
                <ul>
                    <li>"[0-9]{1,2}\/[0-9]{1,2}:" - Matches, 1 or 2 numbers following by a (/) following by 1 or 2 numbers following by a (:)</li>
                    <li>"[a-zA-Z]{3,} - Matches any string with more or 3 at least lower or uppercase letters"</li>
                </ul>
                <br/>
                Note that the matching string will be displayed too and all text formatting will be escaped.
            </span>
            <br/>

            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of sections to show:', $this->text_domain ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" value="<?php echo esc_attr( $number ); ?>">
            <br/>

            <label for="<?php echo $this->get_field_id( 'link_to_post' ); ?>"><?php _e( 'Link to Post/Page ?', $this->text_domain ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'link_to_post' ); ?>" name="<?php echo $this->get_field_name( 'link_to_post' ); ?>" type="checkbox" <?php if( $link_to_post ) echo "checked=\"checked\""; ?> >
            <br/>

            <label for="<?php echo $this->get_field_id( 'link_title' ); ?>"><?php _e( 'Link Title:', $this->text_domain ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'link_title' ); ?>" name="<?php echo $this->get_field_name( 'link_title' ); ?>" type="text" value="<?php echo esc_attr( $link_title ); ?>">
            <br/>

        </p>
        <?php

        return;
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        // processes widget options to be saved
        $instance = array();
        $instance['title']      = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['post']       = ( ! empty( $new_instance['post'] ) ) ? strip_tags( $new_instance['post'] ) : '';
        $instance['pattern']    = ( ! empty( $new_instance['pattern'] ) ) ? strip_tags( $new_instance['pattern'] ) : '';
        $instance['number']    = ( ! empty( $new_instance['number'] ) ) ? ( ( $new_instance['number'] > 0 && $new_instance['number'] < 100 ) ? $new_instance['number'] : $old_instance['number'] ) : 1;
        $instance['link_to_post']    = ( isset( $new_instance['link_to_post'] ) && ! empty( $new_instance['link_to_post'] ) ) ? $new_instance['link_to_post'] : 0;
        $instance['link_title']    = ( ! empty( $new_instance['link_title'] ) ) ? strip_tags( $new_instance['link_title'] ) : "Read more...";

        return $instance;
    }

    public static function register_widget() {
        register_widget( 'HeadLastPostSection_Widget' );
    }
}


add_action( 'widgets_init', array( 'HeadLastPostSection_Widget', 'register_widget' ) );