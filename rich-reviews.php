<?php
/*
Plugin Name: Rich Reviews
Plugin URI: http://nuancedmedia.com/wordpress-rich-reviews-plugin/
Description: Rich Reviews empowers you to easily capture user reviews and display them on your wordpress page or post and in Google Search Results as a Google Rich Snippet.
Version: 1.6.3
Author: Nuanced Media
Author URI: http://nuancedmedia.com/
Text Domain: rich-reviews
License: GPL2



Copyright 2015  Nuanced Media  (email : plugins@nuancedmedia.com)


	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once ABSPATH . 'wp-admin/includes/media.php';
include_once ABSPATH . 'wp-admin/includes/file.php';
include_once ABSPATH . 'wp-admin/includes/image.php';

require_once 'shortcodes/rr-form.php';
require_once 'shortcodes/rr-show.php';
require_once 'shortcodes/rr-snippet.php';


class RichReviews {

	var $sqltable = 'richreviews';
	var $fp_admin_options = 'rr_admin_options';
	//var $credit_permission_option = 'rr_credit_permission';

	var $admin;
	var $db;

	/**
	* @var RROptions
	*/
	var $options;

	/**
	* The variable that stores all current options
	*/
	var $rr_options;

	var $plugin_url;
	var $plugin_path;

	var $logo_url;
	var $logo_small_url;

	function __construct() {
		global $wpdb;
		$this->sqltable = $wpdb->prefix . $this->sqltable;
		$this->path = trailingslashit(plugin_dir_path(__FILE__));

		$this->plugin_url = trailingslashit(plugins_url(basename(dirname(__FILE__))));
		$this->logo_url = $this->plugin_url . 'images/fox_logo_32x32.png';
		$this->logo_small_url = $this->plugin_url . 'images/fox_logo_16x16.png';
		$this->options_name = 'rr_options';
		$this->options= new RROptions($this);
		$this->db = new RichReviewsDB($this);
		$this->admin = new RichReviewsAdmin($this);


		add_action('plugins_loaded', array(&$this, 'on_load'));
		add_action('init', array(&$this, 'init'));
		add_action('wp_enqueue_scripts', array(&$this, 'load_scripts_styles'), 100);

		add_shortcode('RICH_REVIEWS_FORM', array(&$this, 'shortcode_reviews_form_control'));
		add_shortcode('RICH_REVIEWS_SHOW', array(&$this, 'shortcode_reviews_show_control'));
		add_shortcode('RICH_REVIEWS_SHOW_ALL', array(&$this, 'shortcode_reviews_show_all_control'));
		add_shortcode('RICH_REVIEWS_SNIPPET', array(&$this, 'shortcode_reviews_snippets_control'));

		add_filter('widget_text', 'do_shortcode');

		add_action( 'widgets_init', array(&$this, 'register_rr_widget') );
	}

	function init() {
		$this->process_plugin_updates();
		$this->options->update_options();
		$this->rr_options = $this->options->get_option();
		$this->set_display_filters();
		$this->set_form_filters();
		// dump($this->rr_options);
	}

	function process_plugin_updates() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/plugin.php');
		$plugin_data    = get_plugin_data( __FILE__ );
		$newest_version = $plugin_data['Version'];
		$options = get_option($this->fp_admin_options);
		if (isset($options['version'])) {
			$current_version = $options['version'];
		} else { //we were in version 1.0, now we updated
			$current_version = '1.0';
		}

		// Legacy checks
		if ($current_version == '1.0') {
			$wpdb->query("ALTER TABLE " . $this->db->sqltable . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
		}
		if ($current_version == '1.0' || $current_version == '1.1' || $current_version == '1.2') {
			$this->db->create_update_database();
		}

		// New checks. perhaps inefficient but more future-proof
		if ($current_version != $newest_version) {
			$sanitycheck = 0;
			$current_version_string = $current_version;
			$newest_version_string = $newest_version;
			while ($sanitycheck < 100 && strlen($current_version_string) != 5) { $current_version_string .= '.0'; $sanitycheck++; }
			while ($sanitycheck < 100 && strlen($newest_version_string) != 5) { $newest_version_string .= '.0'; $sanitycheck++; }
			if ($sanitycheck >= 100) { // something has gone horribly wrong, let's just quit.
				return FALSE;
			}

			// Okay let's start the version comparing
			$curr_version = str_replace('.', '', $current_version);
			$new_version = str_replace('.', '', $newest_version);

			if (($new_version != $curr_version) || ($newest_version == '1.0')) {
				$this->admin->update_option(array('version' => $newest_version));
			}
		}
	}

	function load_scripts_styles() {
		$pluginDirectory = trailingslashit(plugins_url(basename(dirname(__FILE__))));
		wp_register_script('rich-reviews', $pluginDirectory . 'js/rich-reviews.js', array('jquery'));
		wp_enqueue_script('rich-reviews');
		wp_register_style('rich-reviews', $pluginDirectory . 'css/rich-reviews.css');
		wp_enqueue_style('rich-reviews');
	}

	function set_display_filters() {
		add_action('rr_do_review_content', 'do_review_body', 3);
		if($this->rr_options['display_full_width']) {
			add_action('rr_do_review_wrapper', 'full_width_wrapper');
		}	else {
			add_action('rr_do_review_wrapper', 'column_wrapper');
		}
		if($this->rr_options['show_form_post_title']) {
			add_action('rr_do_review_content', 'do_post_title', 1);
		} else {
			add_action('rr_do_review_content', 'do_hidden_post_title', 1);
		}
		if($this->rr_options['show_date']) {
			add_action('rr_do_review_content', 'do_the_date', 2);
		} else {
			add_action('rr_do_review_content', 'do_the_date_hidden', 2);
		}
		if($this->rr_options['credit_permission']) {
			add_action('rr_close_testimonial_group', 'print_credit');
		}
		add_action('rr_close_testimonial_group', 'render_custom_styles');
	}

	function set_form_filters() {

		add_filter('rr_process_form_data', 'sanitize_incoming_data');
		add_filter('rr_check_required', 'rr_require_rating_field');
		//More work than it's worth to abstract this.
		//add_filter('rr_process_form_data', 'fill_auto_data', 1);

		add_action('rr_on_valid_data', 'rr_insert_new_review', 1, 3);
		if($this->rr_options['send-email-notifications']) {
			add_action('rr_on_valid_data', 'rr_send_admin_email', 1, 3);
		}
		add_action('rr_on_valid_data', 'rr_output_response_message', 1, 3);
		if($this->rr_options['form-name-display']) {
			add_action('rr_do_form_fields', 'rr_do_name_field', 1, 4);
			add_filter('rr_misc_validation', 'rr_validate_name_length');
			if($this->rr_options['form-name-require']) {
				add_filter('rr_check_required', 'rr_require_name_field');
			}
		}
		if($this->rr_options['form-email-display']) {
			add_action('rr_do_form_fields', 'rr_do_email_field', 2, 4);
			add_filter('rr_misc_validation', 'rr_validate_email');

			if($this->rr_options['form-email-require']) {
				add_filter('rr_check_required', 'rr_require_email_field');
			}
		}
		if($this->rr_options['form-title-display']) {
			add_action('rr_do_form_fields', 'rr_do_title_field', 3, 4);
			add_filter('rr_misc_validation', 'rr_validate_title_length');

			if($this->rr_options['form-title-require']) {
				add_filter('rr_check_required', 'rr_require_title_field');
			}
		}
		//TODO: Maybe add min/max rating validation
		add_action('rr_do_form_fields', 'rr_do_rating_field', 4, 4);
		if($this->rr_options['form-content-display']) {
			add_action('rr_do_form_fields', 'rr_do_content_field', 5, 4);
			add_filter('rr_misc_validation', 'rr_validate_content_length');

			if($this->rr_options['form-content-require']) {
				add_filter('rr_check_required', 'rr_require_content_field');
			}
		}
		if($this->rr_options['return-to-form']) {
			add_action('rr_set_local_scripts','rr_output_scroll_script');
		}
		// if($this->rr_options['form-reviewer-display']) {
		// 	add_action('rr_do_form_fields', 'rr_do_reviewerImg_field', 6, 3);
		// 	if($this->rr_options['form-reviewer-display']) {
		// 		//add require validate filter
		// 	}
		// }
		// if($this->rr_options['form-reviewed-display']) {
		// 	add_action('rr_do_form_fields', 'rr_do_reviewedImg_field', 7, 3);
		// 	if($this->rr_options['form-reviewed-display']) {
		// 		//add require validate filter
		// 	}
		// }
	}

	function update_review_status($result, $status) {
		global $wpdb;
		$idid = $result['idid'];
		$rName = $result['reviewername'];
		$rIP = $result['reviewerip'];

		$output = __('Something went wrong! Please report this error.', 'rich-reviews');
		switch ($status) {
			case 'approve':
				//TODO: come back to this for formatting for i18n
				$output = 'Review with internal ID ' . $idid . ' from the reviewer ' . $this->nice_output($rName) . ', whose IP is ' . $rIP . ' has been approved.<br>';
				$wpdb->update($this->sqltable, array('review_status' => '1'), array('id' => $idid));
				break;
			case 'limbo':
				$output = 'Review with internal ID ' . $idid . ' from the reviewer ' . $this->nice_output($rName) . ', whose IP is ' . $rIP . ' has been set as a pending review.<br>';
				$wpdb->update($this->sqltable, array('review_status' => '0'), array('id' => $idid));
				break;
			case 'delete':
				$output = 'Review with internal ID ' . $idid . ' from the reviewer ' . $this->nice_output($rName) . ', whose IP is ' . $rIP . ' has been deleted.<br>';
				$wpdb->query("DELETE FROM $this->sqltable WHERE id=\"$idid\"");
				break;
		}
		return __($output, 'rich-reviews');
	}

	function shortcode_reviews_form_control($atts) {
		// Move primary functoionality elsewhere
		// class data:
		// 	- $options

		ob_start();

			handle_form($atts, $this->rr_options, $this->sqltable, $this->path);

		return ob_get_clean();
	}

	function shortcode_reviews_show_control($atts) {
		global $post;
		extract(shortcode_atts(
			array(
				'category' => 'none',
				'num' => '3',
			)
		, $atts));
		$reviews = $this->db->get_reviews($category, $num, $post);
		ob_start();
			handle_show($reviews, $this->rr_options);
		return ob_get_clean();
	}

	function shortcode_reviews_show_all_control() {
		ob_start();
			$this->shortcode_reviews_show_control(array('num'=>'all'));
		return ob_get_clean();
	}

	function shortcode_reviews_snippets_control($atts) {
		global $wpdb, $post;
		$output = '';
		extract(shortcode_atts(
			array(
				'category' => 'none',
			)
		,$atts));
		$data = $this->db->get_average_rating($category);
		ob_start();
			handle_snippet($data, $this->rr_options, $this->path);
		return ob_get_clean();



	}

	function display_admin_review($review, $status = 'limbo') {

		$rID        = $review['idid'];
		$rDateTime  = $review['datetime'];
		$rName      = $this->nice_output($review['reviewername']);
		$rEmail     = $this->nice_output($review['revieweremail']);
		$rTitle     = $this->nice_output($review['reviewtitle']);
		$rRatingVal = max(1,intval($review['reviewrating']));
		$rText      = $this->nice_output($review['reviewtext']);
		$rStatus    = $review['reviewstatus'];
		$rIP        = $review['reviewerip'];
		$rPostID    = $review['postid'];
		$rCategory  = $review['reviewcategory'];
		$rRating = '';
		for ($i=1; $i<=$rRatingVal; $i++) {
			$rRating .= '&#9733;'; // black star
		}
		for ($i=$rRatingVal+1; $i<=5; $i++) {
			$rRating .= '&#9734;'; // white star
		}
		$approveChecked = '';
		$limboChecked   = '';
		$deleteChecked  = '';
		switch ($status) {
			case 'approve':
				$approveChecked = ' checked';
				break;
			case 'limbo':
				$limboChecked = ' checked';
				break;
			case 'delete':
				$deleteChecked = ' checked';
				break;
		}
		$output = '';
		$output .= '<tr class="rr_admin_review_container">
				<td class="rr_admin_review_actions_container">
					<div class="rr_admin_review_action"><input class="radio" type="radio" name="updateStatus_' . $rID . '" value="approve"' . $approveChecked . '/> Approve</div>
					<div class="rr_admin_review_action"><input class="radio" type="radio" name="updateStatus_' . $rID . '" value="limbo"' . $limboChecked . '/> Pending</div>
					<div class="rr_admin_review_action"><input class="radio" type="radio" name="updateStatus_' . $rID . '" value="delete"' . $deleteChecked . '/> Delete</div>
				</td>
				<td class="rr_admin_review_info_container">
					<div class="rr_reviewer">' . $rName . '</div>
					<div>' . $rEmail . '</div>
					<div>' . $rIP . '</div>
					<div>Category: ' . $rCategory . '</div>
					<div>Page/Post ID: ' . $rPostID . '</div>
				</td>
				<td class="rr_admin_review_content_container">
					<div class="rr_title">' . $rTitle . '</div>
					<div class="rr_admin_review_stars">' . $rRating . '</div>
					<div class="rr_review_text">' . $rText . '</div>
				</td>
			</tr>';
		return __($output, 'rich-reviews');
	}


	function nice_output($input, $keep_breaks = TRUE) {
		//echo '<pre>' . $input . '</pre>';
		//return str_replace(array('\\', '/'), '', $input);
		/*if (strpos($input, '\r\n')) {
			if ($keep_breaks) {
				while (strpos($input, '\r\n\r\n\r\n')) {
					// get rid of everything but single line breaks and pretend-paragraphs
					$input = str_replace(array('\r\n\r\n\r\n'), '\r\n\r\n', $input);
				}
				$input = str_replace(array('\r\n'), '<br />', $input);
			} else {
				$input = str_replace(array('\r\n'), '', $input);
			}
		}
		$input = str_replace(array('\\', '/'), '', $input);*/

		//$input = $this->clean_input($input);

		return $input;
	}

	function clean_input($input) {
		/*$search = array(
			'@<script[^>]*?>.*?</script>@si',   // strip out javascript
			'@<[\/\!]*?[^<>]*?>@si',            // strip out HTML tags
			'@<style[^>]*?>.*?</style>@siU',    // strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'         // strip multi-line comments
		);
		$output = preg_replace($search, '', $input);*/
		$handling = $input;

		/*$handling = strip_tags($handling);
		$handling = stripslashes($handling);
		$handling = esc_html($handling);
		$handling = mysql_real_escape_string($handling);*/

		$handling = sanitize_text_field($handling);
		$handling = stripslashes($handling);

		$output = $handling;
		return $output;
	}

	function fp_sanitize($input) {
		if (is_array($input)) {
			foreach($input as $var=>$val) {
				$output[$var] = $this->fp_sanitize($val);
			}
		}
		else {
			if (get_magic_quotes_gpc()) {
				//$input = stripslashes($input);
			}
			$input  = $this->clean_input($input);
			//$output = mysql_real_escape_string($input);
			$output = $input;
		}
		return $output;
	}

	function render_custom_styles() {
		$options = $this->options->get_option();
		?>
			<style>
				.stars, .rr_star {
					color: <?php echo $options['star_color']?>;
				}
			</style>
		<?php
	}

	function print_credit() {
		$permission = $this->rr_options['credit_permission'];
		$output = "";
		if ($permission) {
			$output = '<div class="credit-line">' . __('Supported By: ', 'rich-reviews') . '<a href="http://nuancedmedia.com/" rel="nofollow">' . 'Nuanced Media'. '</a>';
			$output .= '</div>' . PHP_EOL;
			$output .= '<div class="clear"></div>' . PHP_EOL;
		}
		return __($output, 'rich-reviews');
	}

	function on_load() {
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( 'rich-reviews', false, $plugin_dir );
	}

	function register_rr_widget() {
		register_widget( 'RichReviewsShowWidget' );
	}
}




// Define the "dump" function, a debug helper.
if (!function_exists('dump')) {function dump ($var, $label = 'Dump', $echo = TRUE){ob_start();var_dump($var);$output = ob_get_clean();$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);$output = '<pre style="background: #FFFEEF; color: #000; border: 1px dotted #000; padding: 10px; margin: 10px 0; text-align: left;">' . $label . ' => ' . $output . '</pre>';if ($echo == TRUE) {echo $output;}else {return $output;}}}
if (!function_exists('dump_exit')) {function dump_exit($var, $label = 'Dump', $echo = TRUE) {dump ($var, $label, $echo);exit;}}



if (!class_exists('NMRichReviewsAdminHelper')) {
	require_once('views/admin/view-helper/admin-view-helper-functions.php');
}

if (!class_exists('NMDB')) {
	require_once('lib/nmdb.php');
}
if (!class_exists('RROptions')) {
	require_once('lib/rich-reviews-options.php');
}
require_once('lib/rich-reviews-admin.php');
require_once('lib/rich-reviews-db.php');
require_once('lib/rich-reviews-widget.php');
require_once("views/admin/admin-add-edit-view.php");


global $richReviews;
$richReviews = new RichReviews();
