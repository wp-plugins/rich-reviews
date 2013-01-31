<?php
/*
Plugin Name: Rich Reviews
Plugin URI: http://www.foxytechnology.com/rich-reviews-wordpress-plugin/
Description: Rich Reviews empowers you to easily capture user reviews and display them on your wordpress page or post and in Google Search Results as a Google Rich Snippet.
Version: 1.1
Author: Foxy Technology
Author URI: http://www.foxytechnology.com
License: GPL2
*/

/*  Copyright 2013  Ian Fox Douglas  (email : iandouglas@nuancedmedia.com)

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

class FPRichReviews {
	var $sqltable = 'richreviews';
	var $fp_admin_options = 'rr_admin_options';
	
	function FPRichReviews() {
		global $wpdb;
		define('IN_RICH_REVIEWS', 1);
		$this->sqltable = $wpdb->prefix . $this->sqltable;
		add_action('init', array(&$this, 'fp_init'));
		add_action('admin_menu', array(&$this, 'fp_init_admin_menu'));
		add_action('wp_enqueue_scripts', array(&$this, 'fp_load_scripts_styles'), 100);
		add_action( 'admin_enqueue_scripts', array(&$this, 'fp_load_admin_scripts_styles'), 100);
		add_shortcode('RICH_REVIEWS_FORM', array(&$this, 'fp_shortcode_reviews_form'));
		add_shortcode('RICH_REVIEWS_SHOW', array(&$this, 'fp_shortcode_reviews_show'));
		add_shortcode('RICH_REVIEWS_SHOW_ALL', array(&$this, 'fp_shortcode_reviews_show_all'));
		add_shortcode('RICH_REVIEWS_SNIPPET', array(&$this, 'fp_shortcode_reviews_snippets'));
		add_filter('widget_text', 'do_shortcode');
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'fp_add_plugin_settings_link') );
	}
	
	function fp_init() {
		global $wpdb;
		$tableSearch = $wpdb->get_var("SHOW TABLES LIKE '$this->sqltable'");
		if ($tableSearch != $this->sqltable) { // ...then this is the first installation
			$this->fp_update_database();
		}
		$this->fp_process_plugin_updates();
	}
	
	function fp_init_admin_menu() {
		global $wpdb;
		$pendingReviewsCount = $wpdb->get_var("SELECT COUNT(*) FROM $this->sqltable WHERE review_status=\"0\"");
		$pendingReviewsText = '';
		$menuTitle = '';
		if ($pendingReviewsCount != 0) {
			$pendingReviewsText = ' (' . $pendingReviewsCount . ')';
		}
		add_menu_page(
			'Rich Reviews Settings',
			'Rich Reviews' . $pendingReviewsText,
			'administrator',
			'rich_reviews_settings_main', 
			array(&$this, 'fp_render_settings_main_page'),
			plugin_dir_url( __FILE__ ) . 'fox_logo_16x16.png',
			'25.11111111'
		);
		add_submenu_page(
			'rich_reviews_settings_main', // ID of menu with which to register this submenu
			'Rich Reviews - Settings', //text to display in browser when selected
			'Instructions', // the text for this item
			'administrator', // which type of users can see this menu
			'rich_reviews_settings_main', // unique ID (the slug) for this menu item
			array(&$this, 'fp_render_settings_main_page') // callback function
		);
		add_submenu_page(
			'rich_reviews_settings_main', // ID of menu with which to register this submenu
			'Rich Reviews - Pending Reviews', //text to display in browser when selected
			'Pending Reviews' . $pendingReviewsText, // the text for this item
			'administrator', // which type of users can see this menu
			'fp_admin_pending_reviews_page', // unique ID (the slug) for this menu item
			array(&$this, 'fp_render_pending_reviews_page') // callback function
		);
		add_submenu_page(
			'rich_reviews_settings_main', // ID of menu with which to register this submenu
			'Rich Reviews - Approved Reviews', //text to display in browser when selected
			'Approved Reviews', // the text for this item
			'administrator', // which type of users can see this menu
			'fp_admin_reviews_page', // unique ID (the slug) for this menu item
			array(&$this, 'fp_render_all_reviews_page') // callback function
		);
	}
	
	function fp_process_plugin_updates() {
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
        
        if ($current_version == '1.0') {
        	$wpdb->query("ALTER TABLE $this->sqltable CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
        }
        
        if (($newest_version != $current_version) || ($newest_version == '1.0')) {
	        update_option($this->fp_admin_options, array('version' => $newest_version));
        }
	}
	
	function fp_load_scripts_styles() {
		$pluginDirectory = trailingslashit(plugins_url(basename(dirname(__FILE__))));
        wp_register_script('rich-reviews', $pluginDirectory . 'rich-reviews.js', array('jquery'));
		wp_enqueue_script('rich-reviews');
		wp_register_style('rich-reviews', $pluginDirectory . 'rich-reviews.css');
		wp_enqueue_style('rich-reviews');
	}
	
	function fp_load_admin_scripts_styles() {
		$pluginDirectory = trailingslashit(plugins_url(basename(dirname(__FILE__))));
        wp_register_script('rich-reviews', $pluginDirectory . 'rich-reviews.js', array('jquery'));
		wp_enqueue_script('rich-reviews');
		wp_register_style('rich-reviews', $pluginDirectory . 'rich-reviews.css');
		wp_enqueue_style('rich-reviews');
	}
	
	function fp_add_plugin_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=rich_reviews_settings_main">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	
	function fp_update_database() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE $this->sqltable (
				 id int(11) NOT NULL AUTO_INCREMENT,
				 date_time datetime NOT NULL,
				 reviewer_name varchar(100) DEFAULT NULL,
				 reviewer_email varchar(150) DEFAULT NULL,
				 review_title varchar(100) DEFAULT NULL,
				 review_rating tinyint(2) DEFAULT '0',
				 review_text text,
				 review_status tinyint(1) DEFAULT '0',
				 reviewer_ip varchar(15) DEFAULT NULL,
				PRIMARY KEY (id) CHARACTER SET utf8 COLLATE utf8_general_ci
		);";
		dbDelta($sql);
	}
	
	function fp_render_settings_main_page() {
		$output = '';
		$output .= '<div class="wrap"><h2><img src="' . plugin_dir_url( __FILE__ ) . 'fox_logo_32x32.png" /> Rich Reviews</h2></div>';
		$output .= '<p>Thank you for using Rich Reviews by <a href="http://www.foxytechnology.com">Foxy Technology</a>!</p>
					<p>This plugin is built around shortcodes.
					<br /><b>[RICH_REVIEWS_FORM]</b> will display a form with which your users may submit reviews. These reviews are not accepted automatically, rather the reviews will be sent to this page as a "Pending Review" to await your approval. You will know you have reviews pending because the menu page will have a number next to it.
					<br /><b>[RICH_REVIEWS_SHOW]</b> will show the first three reviews which were submitted to your website. This is meant to be displayed in a horizontal layout.
					<br /><b>[RICH_REVIEWS_SHOW_ALL]</b> will show EVERY review which has been approved. It is recommended that you display this in its own dedicated page, as it will likely take a large amount of room.
					<br /><b>[RICH_REVIEWS_SNIPPET]</b> will display text such as "Overall rating: 4.3 out of 5 based on 10 reviews" in such a way as to show up in a google search, using <a href="http://support.google.com/webmasters/bin/answer.py?hl=en&answer=99170">Google Rich Snippets</a>. This is a separate shortcode so that the rating (and hence the rich snippets) can be placed on many pages, even if the reviews are not also displayed on that page.<br /></p>
					<p>The other two settings pages will allow you view the reviews that have been posted. 
					<br /><b>"Pending Reviews"</b> shows any pending reviews which are awaiting your approval or rejection (if there are any, this will show up as a number next to this menu. For example, if you have 2 pending reviews, the menu name in the sidebar will be "Rich Reviews (2)". 
					<br /><b>"Approved Reviews"</b> shows all the reviews which you have approved. In this way you can change the status of previously-approved reviews to be either a pending review again, or to delete them entirely.';
		echo $output;
	}
	
	function fp_render_pending_reviews_page() {
		global $wpdb;
		$output = '';
		$output .= '<div class="wrap"><h2><img src="' . plugin_dir_url( __FILE__ ) . 'fox_logo_32x32.png" /> Pending Reviews</h2></div>';
		if (isset($_POST['submitted'])) {
			if ($_POST['submitted'] == 'Y') {
				$sql = "SELECT
						id as `idid`,
						reviewer_name as `reviewername`,
						reviewer_ip as `reviewerip` FROM $this->sqltable WHERE review_status=\"0\"";
				$pendingIDs = $wpdb->get_results($sql, ARRAY_A);
				foreach ($pendingIDs as $result) {
					$idid = $result['idid'];
					if (isset($_POST["updateStatus_$idid"])) {
						$status = $_POST["updateStatus_$idid"];
						if ($status != 'limbo') {
							$output .= $this->fp_update_review_status($result, $status);
						}
					}
				}
				$output .= '<br>';
			}
		}
		$pendingReviewsCount = $wpdb->get_var("SELECT COUNT(*) FROM $this->sqltable WHERE review_status=\"0\"");
		if ($pendingReviewsCount == 0) {
			$output .= '<p>There are no reviews pending approval.</p>';
		} else {
			$sql = "SELECT id as `idid`,
					date_time as `datetime`,
					reviewer_name as `reviewername`,
					reviewer_email as `revieweremail`,
					review_title as `reviewtitle`,
					review_rating as `reviewrating`,
					review_text as `reviewtext`,
					review_status as `reviewstatus`,
					reviewer_ip as `reviewerip` FROM $this->sqltable WHERE review_status=\"0\"";
			$pendingReviews = $wpdb->get_results($sql, ARRAY_A);
			
			$output .= '<form method="post" action="">
				  <input type="hidden" name="submitted" value="Y" />
				  <table><tbody><tr><th></th>
				  <th style="text-align: left">Reviewer</th>
				  <th style="text-align: left">Review Content</th></tr>';
			foreach($pendingReviews as $result) {
				$output .= $this->fp_display_admin_review($result);
			}
			$output .= '<tr><td></td><td colspan="2"><input name="submitButton" type="submit" value="Submit Changes" /></td></tr></tbody></table></form>';
		}
		echo $output;
	}
	
	function fp_render_all_reviews_page() {
		global $wpdb;
		$output = '';
		$output .= '<div class="wrap"><h2><img src="' . plugin_dir_url( __FILE__ ) . 'fox_logo_32x32.png" /> Approved Reviews</h2></div>';
		if (isset($_POST['submitted'])) {
			if ($_POST['submitted'] == 'Y') {
				$sql = "SELECT
						id as `idid`,
						reviewer_name as `reviewername`,
						reviewer_ip as `reviewerip` FROM $this->sqltable WHERE review_status=\"1\"";
				$pendingIDs = $wpdb->get_results($sql, ARRAY_A);
				foreach ($pendingIDs as $result) {
					$idid = $result['idid'];
					if (isset($_POST["updateStatus_$idid"])) {
						$status = $_POST["updateStatus_$idid"];
						if ($status != 'approve') {
							$output .= $this->fp_update_review_status($result, $status);
						}
					}
				}
				$output .= '<br>';
			}
		}
		$approvedReviewsCount = $wpdb->get_var("SELECT COUNT(*) FROM $this->sqltable WHERE review_status=\"1\"");
		if ($approvedReviewsCount == 0) {
			$output .= 'There are no reviews which have been marked as "approved."';
		} else {
			$sql = "SELECT id as `idid`,
					date_time as `datetime`,
					reviewer_name as `reviewername`,
					reviewer_email as `revieweremail`,
					review_title as `reviewtitle`,
					review_rating as `reviewrating`,
					review_text as `reviewtext`,
					review_status as `reviewstatus`,
					reviewer_ip as `reviewerip` FROM $this->sqltable WHERE review_status=\"1\"";
			$approvedReviews = $wpdb->get_results($sql, ARRAY_A);
			$output .= '<form method="post" action="">
					    <input type="hidden" name="submitted" value="Y" />
					    <table><tbody><tr><th></th>
					    <th style="text-align: left">Reviewer</th>
					    <th style="text-align: left">Review Content</th></tr>';
			foreach ($approvedReviews as $result) {
				$output .= $this->fp_display_admin_review($result, 'approve');
			}
			$output .= '<tr><td></td><td colspan="2"><input name="submitButton" type="submit" value="Submit Changes" /></td></tr></tbody></table></form>';
		}
		echo $output;
	}
	
	function fp_update_review_status($result, $status) {
		global $wpdb;
		$idid = $result['idid'];
		$rName = $result['reviewername'];
		$rIP = $result['reviewerip'];
		$output = 'Something went wrong! Please report this error.';
		switch ($status) {
			case 'approve':
				$output = 'Review with internal ID ' . $idid . ' from the reviewer ' . $this->fp_nice_output($rName) . ', whose IP is ' . $rIP . ' has been approved.<br>';
				$wpdb->update($this->sqltable, array('review_status' => '1'), array('id' => $idid));
				break;
			case 'limbo':
				$output = 'Review with internal ID ' . $idid . ' from the reviewer ' . $this->fp_nice_output($rName) . ', whose IP is ' . $rIP . ' has been set as a pending review.<br>';
				$wpdb->update($this->sqltable, array('review_status' => '0'), array('id' => $idid));
				break;
			case 'delete':
				$output = 'Review with internal ID ' . $idid . ' from the reviewer ' . $this->fp_nice_output($rName) . ', whose IP is ' . $rIP . ' has been deleted.<br>';
				$wpdb->query("DELETE FROM $this->sqltable WHERE id=\"$idid\"");
				break;
		}
		return $output;
	}
	
	function fp_shortcode_reviews_form() {
		global $wpdb;
		global $post;
		$output = '';
		$rName  = '';
		$rEmail = '';
		$rTitle = '';
		$rText  = '';
		$displayForm = true;
		if (isset($_POST['submitted'])) {
			if ($_POST['submitted'] == 'Y') {
				$rDateTime = date('Y-m-d H:i:s');
				$rName =   $this->fp_sanitize($_POST['rName']);
				$rEmail =  $this->fp_sanitize($_POST['rEmail']);
				$rTitle =  $this->fp_sanitize($_POST['rTitle']);
				$rRating = $this->fp_sanitize($_POST['rRating']);
				$rText =   $this->fp_sanitize($_POST['rText']);
				$rStatus = 0;
				$rIP = $_SERVER['REMOTE_ADDR'];
				
				$newdata = array(
						'date_time'       => $rDateTime,
						'reviewer_name'   => $rName,
						'reviewer_email'  => $rEmail,
						'review_title'    => $rTitle,
						'review_rating'   => intval($rRating),
						'review_text'     => $rText,
						'review_status'   => $rStatus,
						'reviewer_ip'     => $rIP
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
					$output .= 'Your review has been recorded and submitted for approval, ' . $this->fp_nice_output($rName) . '. Thanks!<br />';
					$displayForm = false;
				}
			}
		}
		if ($displayForm) {
			$output .= '<form class="reviewform" id="fprr_review_form" method="post" action="">
			<input type="hidden" name="submitted" value="Y" />
			<input type="hidden" name="rRating" id="rRating" value="0" />
			<div class="rr_form_heading">Name (*)</div><div class="right"><input class="rr_small_input" type="text" name="rName" value="' . $rName . '" /></div>
			<div class="clear"></div>
			<div class="rr_form_heading">Email</div><div class="right"><input class="rr_small_input" type="text" name="rEmail" value="' . $rEmail . '" /></div>
			<div class="clear"></div>
			<div class="rr_form_heading">Review Title (*)</div><div class="right"><input class="rr_small_input" type="text" name="rTitle" value="' . $rTitle . '" /></div>
			<div class="clear"></div>
			<div class="rr_form_heading">Rating (*)</div><div class="rr_input_stars">' . $this->fp_star_rating_input() . '</div>
			<div class="clear"></div>
			<div class="rr_form_heading">Review Text (*)</div><div class="right"><textarea class="rr_large_input" name="rText"></textarea></div>
			<div class="clear"></div>
			<div class="left"><input name="submitButton" type="submit" value="Submit Review" /></div>
			<div class="clear"></div>
			</form>';
		}
		return $output;
	}
	
	function fp_star_rating_input() {
		$output = '
			<span onmouseover="this.style.cursor=\'default\';">
			  <span id="s1" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			  <span id="s2" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			  <span id="s3" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			  <span id="s4" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			  <span id="s5" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			</span>';
		return $output;
	}
	
	function fp_shortcode_reviews_show() {
		global $wpdb;
		$output = '';
		$approvedReviewsCount = $wpdb->get_var("SELECT COUNT(*) FROM $this->sqltable WHERE review_status=\"1\"");
		if($approvedReviewsCount != 0){
			$sql = "SELECT id as `idid`,
					date_time as `datetime`,
					reviewer_name as `reviewername`,
					reviewer_email as `revieweremail`,
					review_title as `reviewtitle`,
					review_rating as `reviewrating`,
					review_text as `reviewtext`,
					review_status as `reviewstatus`,
					reviewer_ip as `reviewerip` FROM $this->sqltable WHERE review_status=\"1\" LIMIT 3";
			$results = $wpdb->get_results($sql, ARRAY_A);
			$output .= '<div class="testimonial_group">';
			foreach($results as $result) {
				$output .= $this->fp_display_review($result);
			}
			$output .= '</div><div class="clear"></div>';
		}
		
		return $output;
	}
	
	function fp_shortcode_reviews_show_all() {
		global $wpdb;
		$output = '';
		$approvedReviewsCount = $wpdb->get_var("SELECT COUNT(*) FROM $this->sqltable WHERE review_status=\"1\"");
		if($approvedReviewsCount != 0){
			$sql = "SELECT id as `idid`,
					date_time as `datetime`,
					reviewer_name as `reviewername`,
					reviewer_email as `revieweremail`,
					review_title as `reviewtitle`,
					review_rating as `reviewrating`,
					review_text as `reviewtext`,
					review_status as `reviewstatus`,
					reviewer_ip as `reviewerip` FROM $this->sqltable WHERE review_status=\"1\"";
			$results = $wpdb->get_results($sql, ARRAY_A);
			$ii = 0;
			$output .= '<div class="testimonial_group">';
			foreach($results as $result) {
				$output .= $this->fp_display_review($result);
				$ii += 1;
				if (($ii % 3) == 0) {
					$output .= '</div><div class="clear"></div><div class="testimonial_group">';
				}
			}
			$output .= '</div><div class="clear"></div>';
		}
		return $output;
	}
	
	function fp_shortcode_reviews_snippets() {
		global $wpdb;
		$approvedReviewsCount = $wpdb->get_var("SELECT COUNT(*) FROM $this->sqltable WHERE review_status=\"1\"");
		$averageRating = 0;
		if ($approvedReviewsCount != 0) {
			$averageRating = $wpdb->get_var("SELECT AVG(review_rating) FROM $this->sqltable WHERE review_status=\"1\"");
			$averageRating = floor(10*floatval($averageRating))/10;
		}

		$output = '<div class="hreview-aggregate">Overall rating: <span class="rating">' . $averageRating . '</span> out of 5 based on <span class="count">' . $approvedReviewsCount . '</span> reviews</div>';
		return $output;
	}

	function fp_display_admin_review($review, $status = 'limbo') {
		$rID        = $review['idid'];
		$rDateTime  = $review['datetime'];
		$rName      = $this->fp_nice_output($review['reviewername']);
		$rEmail     = $this->fp_nice_output($review['revieweremail']);
		$rTitle     = $this->fp_nice_output($review['reviewtitle']);
		$rRatingVal = max(1,intval($review['reviewrating']));
		$rText      = $this->fp_nice_output($review['reviewtext']);
		$rStatus    = $review['reviewstatus'];
		$rIP        = $review['reviewerip'];
		$rRating = '';
		for ($i=1; $i<=$rRatingVal; $i++) {
			$rRating .= '&#9733'; // black star
		}
		for ($i=$rRatingVal+1; $i<=5; $i++) {
			$rRating .= '&#9734'; // white star
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
		$output = '<tr class="rr_admin_review"><td class="rr_action">
				   <input class="radio" type="radio" name="updateStatus_' . $rID . '" value="approve"' . $approveChecked . '/> Approve<br />
				   <input class="radio" type="radio" name="updateStatus_' . $rID . '" value="limbo"' . $limboChecked . '/> Pending<br />
				   <input class="radio" type="radio" name="updateStatus_' . $rID . '" value="delete"' . $deleteChecked . '/> Delete</td>
				   <td class="rr_reviewer"><b>' . $rName . '</b><br />' . $rEmail . '<br />' . $rIP . '<br /></td>
				   <td class="rr_review_content"><span class="rr_title">' . $rTitle . '</span><br />' . $rRating . '<br /><div class="rr_review_text">' . $rText . '</div></tr>';
		return $output;
	}

	function fp_display_review($review) {
		$rID        = $review['idid'];
		$rDateTime  = $review['datetime'];
		$rName      = $this->fp_nice_output($review['reviewername']);
		$rEmail     = $this->fp_nice_output($review['revieweremail']);
		$rTitle     = $this->fp_nice_output($review['reviewtitle']);
		$rRatingVal = max(1,intval($review['reviewrating']));
		$rText      = $this->fp_nice_output($review['reviewtext']);
		$rStatus    = $review['reviewstatus'];
		$rIP        = $review['reviewerip'];
		$rRating = '';
		for ($i=1; $i<=$rRatingVal; $i++) {
			$rRating .= '&#9733'; // black star
		}
		for ($i=$rRatingVal+1; $i<=5; $i++) {
			$rRating .= '&#9734'; // white star
		}
		
		$output = '<div class="testimonial">
			<span class="rr_title">' . $rTitle . '</span>
			<div class="clear"></div>
			<span class="stars">' . $rRating . '</span>
			<div class="clear"></div>';
		$output .= '<div class="rr_review_text"><span class="drop_cap">“</span>' . $rText . '”</div>';
		$output .= '<span class="rr_review_name"> - ' . $rName . '</span>
			<div class="clear"></div>';
		$output .= '</div>';
		return $output;
	}
	
	function fp_nice_output($input) {
		return str_replace(array('\\', '/'), '', $input);
	}
	
	function fp_cleanInput($input) {
		$search = array(
			'@<script[^>]*?>.*?</script>@si',   // strip out javascript
			'@<[\/\!]*?[^<>]*?>@si',            // strip out HTML tags
			'@<style[^>]*?>.*?</style>@siU',    // strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'         // strip multi-line comments
		);
		$output = preg_replace($search, '', $input);
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
				$input = stripslashes($input);
			}
			$input  = $this->fp_cleanInput($input);
			$output = mysql_real_escape_string($input);
		}
		return $output;
	}
}


if (!defined('IN_FPRR')) {
	global $fpRichReviews;
	$fpRichReviews = new FPRichReviews();
}
?>