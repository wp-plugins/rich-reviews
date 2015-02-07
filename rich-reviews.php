<?php
/*
Plugin Name: Rich Reviews
Plugin URI: http://nuancedmedia.com/wordpress-rich-reviews-plugin/
Description: Rich Reviews empowers you to easily capture user reviews and display them on your wordpress page or post and in Google Search Results as a Google Rich Snippet.
Version: 1.6.0
Author: Foxy Technology
Author URI: http://nuancedmedia.com/
Text Domain: rich-reviews
License: GPL2


Copyright 2015  Ian Fox Douglas  (email : iandouglas@nuancedmedia.com)

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


class RichReviews {

	var $sqltable = 'richreviews';
	var $fp_admin_options = 'rr_admin_options';
	var $credit_permission_option = 'rr_credit_permission';

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

		$this->path = trailingslashit(plugins_url(basename(dirname(__FILE__))));
		$this->logo_url = $this->path . 'images/fox_logo_32x32.png';
		$this->logo_small_url = $this->path . 'images/fox_logo_16x16.png';
		$this->options_name = 'rr_options';
		$this->options= new RROptions($this);
		$this->db = new RichReviewsDB($this);
		$this->admin = new RichReviewsAdmin($this);
		$this->plugin_url = trailingslashit(plugins_url(basename(dirname(__FILE__))));

		add_action('plugins_loaded', array(&$this, 'on_load'));
		add_action('init', array(&$this, 'init'));
		add_action('wp_enqueue_scripts', array(&$this, 'load_scripts_styles'), 100);

		add_shortcode('RICH_REVIEWS_FORM', array(&$this, 'shortcode_reviews_form'));
		add_shortcode('RICH_REVIEWS_SHOW', array(&$this, 'shortcode_reviews_show'));
		add_shortcode('RICH_REVIEWS_SHOW_ALL', array(&$this, 'shortcode_reviews_show_all'));
		add_shortcode('RICH_REVIEWS_SNIPPET', array(&$this, 'shortcode_reviews_snippets'));

		add_filter('widget_text', 'do_shortcode');

		add_action( 'widgets_init', array(&$this, 'register_rr_widget') );
	}

	function init() {
		$this->process_plugin_updates();
		$this->options->update_options();
		$this->rr_options = $this->options->get_option();
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

	function update_review_status($result, $status) {
		global $wpdb;
		$idid = $result['idid'];
		$rName = $result['reviewername'];
		$rIP = $result['reviewerip'];
		$output = 'Something went wrong! Please report this error.';
		switch ($status) {
			case 'approve':
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

	function star_rating_input() {
		$output = '<div class="rr_stars_container">
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_1"></span>
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_2"></span>
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_3"></span>
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_4"></span>
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_5"></span>
		</div>';
		return __($output, 'rich-reviews');
	}

	function shortcode_reviews_form($atts) {
		global $wpdb;
		global $post;
		extract(shortcode_atts(
			array(
				'category' => 'none',
			)
		,$atts));
		$output = '';
		$rName  = '';
		$rEmail = '';
		$rTitle = '';
		$rText  = '';
		$displayForm = true;
		if (isset($_POST['submitted'])) {
			if ($_POST['submitted'] == 'Y') {
				$rDateTime = date('Y-m-d H:i:s');
				$rName     = $this->fp_sanitize($_POST['rName']);
				$rEmail    = $this->fp_sanitize($_POST['rEmail']);
				$rTitle    = $this->fp_sanitize($_POST['rTitle']);
				$rRating   = $this->fp_sanitize($_POST['rRating']);
				$rText     = $this->fp_sanitize($_POST['rText']);
				if ($this->rr_options['require_approval']) {$rStatus   = 0;} else {$rStatus   = 1;}
				$rIP       = $_SERVER['REMOTE_ADDR'];
				$rPostID   = $post->ID;
				$rCategory = $this->fp_sanitize($category);

				$newdata = array(
						'date_time'       => $rDateTime,
						'reviewer_name'   => $rName,
						'reviewer_email'  => $rEmail,
						'review_title'    => $rTitle,
						'review_rating'   => intval($rRating),
						'review_text'     => $rText,
						'review_status'   => $rStatus,
						'reviewer_ip'     => $rIP,
						'post_id'		  => $rPostID,
						'review_category' => $rCategory
				);
				$validData = true;
				if ($rName == '') {
					$output .= 'You must include your name.';
					$validData = false;
				} else if ($rTitle == '') {
					$output .= 'You must include a title for your review.';
					$validData = false;
				} else if ($rText == '') {
					$output .= 'You must write some text in your review.';
					$validData = false;
				} else if ($rRating == 0) {
					$output .= 'Please give a rating between 1 and 5 stars.';
					$validData = false;
				} else if ($rEmail != '') {
					$firstAtPos = strpos($rEmail,'@');
					$periodPos  = strpos($rEmail,'.');
					$lastAtPos  = strrpos($rEmail,'@');
					if (($firstAtPos === false) || ($firstAtPos != $lastAtPos) || ($periodPos === false)) {
						$output .= 'You must provide a valid email address.';
						$validData = false;
					}
				}
				if ($validData) {
					if ((strlen($rName) > 100)) {
						$output .= 'The name you entered was too long, and has been shortened.<br />';
					}
					if ((strlen($rTitle) > 150)) {
						$output .= 'The review title you entered was too long, and has been shortened.<br />';
					}
					if ((strlen($rEmail) > 100)) {
						$output .= 'The email you entered was too long, and has been shortened.<br />';
					}
					$wpdb->insert($this->sqltable, $newdata);
					$output .= 'Your review has been recorded and submitted for approval, ' . $this->nice_output($rName) . '. Thanks!<br />';
					$displayForm = false;
				}
			}
		}
		if ($displayForm) {
			$output .= '<form action="" method="post" class="rr_review_form" id="fprr_review_form">';
			$output .= '	<input type="hidden" name="submitted" value="Y" />';
			$output .= '	<input type="hidden" name="rRating" id="rRating" value="0" />';
			$output .= '	<table class="form_table">';
			$output .= '		<tr class="rr_form_row">';
			$output .= '			<td class="rr_form_heading rr_required">Name</td>';
			$output .= '			<td class="rr_form_input"><input class="rr_small_input" type="text" name="rName" value="' . $rName . '" /></td>';
			$output .= '		</tr>';
			$output .= '		<tr class="rr_form_row">';
			$output .= '			<td class="rr_form_heading">Email</td>';
			$output .= '			<td class="rr_form_input"><input class="rr_small_input" type="text" name="rEmail" value="' . $rEmail . '" /></td>';
			$output .= '		</tr>';
			$output .= '		<tr class="rr_form_row">';
			$output .= '			<td class="rr_form_heading rr_required">' . ucwords(strtolower($this->rr_options['review_title'])) . '</td>';
			$output .= '			<td class="rr_form_input"><input class="rr_small_input" type="text" name="rTitle" value="' . $rTitle . '" /></td>';
			$output .= '		</tr>';
			$output .= '		<tr class="rr_form_row">';
			$output .= '			<td class="rr_form_heading rr_required">Rating</td>';
			$output .= '			<td class="rr_form_input">' . $this->star_rating_input() . '</td>';
			$output .= '		</tr>';
			$output .= '		<tr class="rr_form_row">';
			$output .= '			<td class="rr_form_heading rr_required">Review Content</td>';
			$output .= '			<td class="rr_form_input"><textarea class="rr_large_input" name="rText" rows="10">' . $rText . '</textarea></td>';
			$output .= '		</tr>';
			$output .= '		<tr class="rr_form_row">';
			$output .= '			<td></td>';
			$output .= '			<td class="rr_form_input rr_required"><input name="submitButton" type="submit" value="Submit Review" /></td>';
			$output .= '		</tr>';
			$output .= '	</table>';
			$output .= '</form>';
		}
		$this->render_custom_styles();
		return __($output, 'rich-reviews');
	}

	function shortcode_reviews_show($atts) {
		global $wpdb;
		global $post;
		$output = '';
		extract(shortcode_atts(
			array(
				'category' => 'none',
				'num' => '3',
			)
		, $atts));

		// Set up the SQL query


		$this->db->where('review_status', 1);
		if (($category == 'post') || ($category == 'page')) {
			$this->db->where('post_id', $post->ID);
		} else {
			$this->db->where('review_category', $category);
		}
		if ($num != 'all') {
			$num = intval($num);
			if ($num < 1) { $num = 1; }
			$this->db->limit($num);
		}

		// Set up the Order BY
		if ($this->rr_options['reviews_order'] === 'random') {
			$this->db->order_by('rand()');
		}
		else {
			$this->db->order_by('date_time', $this->rr_options['reviews_order']);
		}

		// Show the reviews
		$results = $this->db->get();
		if (count($results)) {
			$total_count = count($results);
			$review_count = 0;
			$output .= '<div class="testimonial_group">';
			foreach($results as $review) {
				$output .= $this->display_review($review);
				$review_count += 1;
				if ($review_count == 3) {
					// end the testimonial_group
					$output .= '</div>';

					// clear the floats
					$output .= '<div class="clear"></div>';

					// do we have more reviews to show?
					if ($review_count < $total_count) {
						$output .= '<div class="testimonial_group">';
					}

					// reset the counter
					$review_count = 0;
					$total_count = $total_count - 3;
				}
			}
			// do we need to close a testimonial_group?
			if ($review_count != 0) {
				$output .= '</div>';
				$output .= '<div class="clear"></div>';
			}

		}
		$output .= $this->print_credit();
		$this->render_custom_styles();
		return __($output, 'rich-reviews');
	}

	function shortcode_reviews_show_all() {
		return $this->shortcode_reviews_show(array('num'=>'all'));
	}

	function shortcode_reviews_snippets($atts) {
		global $wpdb, $post;
		$output = '';
		extract(shortcode_atts(
			array(
				'category' => 'none',
			)
		,$atts));
		if ($category == 'none') {
			$whereStatement = "WHERE review_status=\"1\"";
		} else if(($category == 'post') || ($category == 'page')) {
			$whereStatement = "WHERE (review_status=\"1\" and post_id=\"$post->ID\")";
		} else {
			$whereStatement = "WHERE (review_status=\"1\" and review_category=\"$category\")";
		}

		$approvedReviewsCount = $wpdb->get_var("SELECT COUNT(*) FROM $this->sqltable " . $whereStatement);
		$averageRating = 0;
		if ($approvedReviewsCount != 0) {
			$averageRating = $wpdb->get_var("SELECT AVG(review_rating) FROM $this->sqltable " . $whereStatement);
			$averageRating = floor(10*floatval($averageRating))/10;
		}

		if ($this->options->get_option('snippet_stars')) {
			$stars = '';
			$star_count = 0;
			//dump($averageRating, 'AVE:');
			for ($i=1; $i<=5; $i++) {
				if ($i <= $averageRating) {
					$stars = $stars . '&#9733;';
				}
				else {
					$stars = $stars . '&#9734;';
				}
			}

			// ----- Old Star handling that broke Google Snippets because
			// ----- $average rating was being altered.

			// while($averageRating >= 1) {
			// 	$stars = $stars . '&#9733';
			// 	$star_count++;
			// 	$averageRating--;
			// 	//dump($averageRating, 'AVE in WHILE:');
			// 	//dump($star_count, 'STAR COUNT:');
			// }
			// while ($star_count < 5) {
			// 	$stars = $stars . '&#9734';
			// 	$star_count++;
			// 	//dump($star_count, 'STAR COUNT:');
			// }

		// 	$output = '<div class="hreview-aggregate">Overall rating: <span class="stars">' . $stars . '</span> <span class="rating" style="display: none !important;">' . $averageRating . '</span> based on <span class="votes">' . $approvedReviewsCount . '</span> reviews</div>';
		// 	$this->render_custom_styles();
		// } else {
		// 	$output = '<div class="hreview-aggregate">Overall rating: <span class="rating">' . $averageRating . '</span> out of 5 based on <span class="votes">' . $approvedReviewsCount . '</span> reviews</div>';
		// }
			$output = '<div itemscope itemtype="http://data-vocabulary.org/Review-aggregate">';
			$output .= 'Overall rating: <span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">';
			$output .= '<span class="stars">' . $stars . '</span>';
			$output .= '<span class="rating" itemprop="rating" style="display: none !important;">' . $averageRating . '</span></span>';
			$output .= ' based on <span class="votes" itemprop="votes">' . $approvedReviewsCount . '</span>';
			$output .= ' reviews</div>';
			$this->render_custom_styles();
		} else {
			$output = '<div itemscope itemtype="http://data-vocabulary.org/Review-aggregate">';
			$output .= 'Overall rating: <span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">';
			$output .= '<strong><span class="rating" itemprop="rating">' . $averageRating . '</span></strong> out of <strong>5</strong> ';
			$output .= 'based on <span class="votes" itemprop="votes">' . $approvedReviewsCount . '</span> reviews</div>';
		}

		return __($output, 'rich-reviews');
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

	function display_review($review) {
		$rID        = $review->id;
		$rDateTime  = $review->date_time;
		$date 		= strtotime($rDateTime);
		$rDay		= date("j", $date);
		$rMonth		= date("F", $date);
		$rSuffix	= date("S", $date);
		$rYear		= date("Y", $date);
		$rDate 		= $rMonth . ' ' . $rDay . $rSuffix . ', '  . $rYear;
		$rName      = $this->nice_output($review->reviewer_name, FALSE);
		$rEmail     = $this->nice_output($review->reviewer_email, FALSE);
		$rTitle     = $this->nice_output($review->review_title, FALSE);
		$rRatingVal = max(1,intval($review->review_rating));
		$rText      = $this->nice_output($review->review_text);
		$rStatus    = $review->review_status;
		$rIP        = $review->reviewer_ip;
		$rPostId    = $review->post_id;
		$rRating = '';

		for ($i=1; $i<=$rRatingVal; $i++) {
			$rRating .= '&#9733;'; // orange star
		}
		for ($i=$rRatingVal+1; $i<=5; $i++) {
			$rRating .= '&#9734;'; // white star
		}

		// $output = '<div class="testimonial">
		// 	<h3 class="rr_title">' . $rTitle . '</h3>
		// 	<div class="clear"></div>';
		// if ($this->rr_options['show_form_post_title']) {
		// 	$output .= '<div class="rr_review_post_id"><a href="' . get_the_permalink($rPostId) . '">' . get_the_title($rPostId) . '</a></div><div class="clear"></div>';
		// }
		// $output .= '<div class="stars">' . $rRating . '</div>
		// 	<div class="clear"></div>';
		// $output .= '<div class="rr_review_text"><span class="drop_cap">“</span>' . $rText . '”</div>';
		// $output .= '<div class="rr_review_name"> - ' . $rName . '</div>
		// 	<div class="clear"></div>';
		// $output .= '</div>';

		if($this->rr_options['display_full_width'] != NULL) {
			$output = '<div class="full-testimonial" itemscope itemtype="data-vocabulary.org/Review">
			<h3 class="rr_title" itemprop="summary">' . $rTitle . '</h3>
			<div class="clear"></div>';
		} else {
			$output = '<div class="testimonial" itemscope itemtype="data-vocabulary.org/Review">
				<h3 class="rr_title" itemprop="summary">' . $rTitle . '</h3>
				<div class="clear"></div>';
		}
		if ($this->rr_options['show_form_post_title']) {
			$output .= '<div class="rr_review_post_id" itemprop="itemreviewed"><a href="' . get_the_permalink($rPostId) . '">' . get_the_title($rPostId) . '</a></div><div class="clear"></div>';
		} else {
			$output .= '<div class="rr_review_post_id" itemprop="itemreviewed" style="display:none;"><a href="' . get_the_permalink($rPostId) . '">' . get_the_title($rPostId) . '</a></div><div class="clear"></div>';
		}
		if ($this->rr_options['show_date']) {
			$output .= 'Submitted: <time itemprop="startDate" datetime="' . $rDate . '">' . $rDate . '</time>';
		}
		$output .= '<div class="stars">' . $rRating . '</div><div style="display:none;" itemprop="rating">' . $rRatingVal . '</div>';

		$output .= '<div class="clear"></div>';
		$output .= '<div class="rr_review_text" itemprop="description"><span class="drop_cap">“</span>' . $rText . '”</div>';
		$output .= '<div class="rr_review_name"> - <span itemprop="reviewer">' . $rName . '</span></div>
			<div class="clear"></div>';
		$output .= '</div>';

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
			$output = '<div class="credit-line">Supported By: <a href="http://nuancedmedia.com/" rel="nofollow"> Nuanced Media</a>';
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
	require_once('views/view-helper/admin-view-helper-functions.php');
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
require_once("views/admin-add-edit-view.php");

global $richReviews;
$richReviews = new RichReviews();
