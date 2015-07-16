<?php

class RichReviewsShowWidget extends WP_Widget {

    var $core;

    var $slug = 'rr_show_all';

    var $name = 'Rich Reviews Show All';

    var $classname = 'rich-reviews-show';

    var $description = 'Widget display for the Rich Reviews Show All Shortcode.';

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        if (is_null($this->name) || is_null($this->classname) || is_null($this->description) || is_null($this->slug)) {
            echo '<div class="error">' . __('At least one of the four widget variables was not set.', 'rich-reviews') . '</div>';
        }
        $widget_ops = array(
            'classname' => __($this->classname),
            'description' => __($this->description),
        );
        parent::__construct($this->slug, __($this->name), $widget_ops);
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        global $richReviews;
        extract($args);

        $atts = array(
            'category' => 'none',
            'num'      => 'all'
        );
        echo $before_widget;
        echo '<div class="rr-widget-display">';
		if ( $instance['title'] ) {
			echo $before_title . $instance['title'] . $after_title;
        }
        echo $richReviews->shortcode_reviews_show_control($atts);
        echo '</div>';
        echo $after_widget;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
    }
}

