<?php
/*
Plugin Name: Rich Reviews
Plugin URI: http://www.foxytechnology.com/rich-reviews-wordpress-plugin/
Description: Rich Reviews empowers you to easily capture user reviews and display them on your wordpress page or post and in Google Search Results as a Google Rich Snippet.
Version: 1.4
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
		if ($tableSearch != $this->sqltable) {
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
			'25.11'
		);
		add_submenu_page(
			'rich_reviews_settings_main', // ID of menu with which to register this submenu
			'Rich Reviews - Instructions', //text to display in browser when selected
			'Instructions', // the text for this item
			'administrator', // which type of users can see this menu
			'rich_reviews_settings_main', // unique ID (the slug) for this menu item
			array(&$this, 'fp_render_settings_main_page') // callback function
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - Pending Reviews',
			'Pending Reviews' . $pendingReviewsText,
			'administrator',
			'fp_admin_pending_reviews_page',
			array(&$this, 'fp_render_pending_reviews_page')
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - Approved Reviews',
			'Approved Reviews',
			'administrator',
			'fp_admin_approved_reviews_page',
			array(&$this, 'fp_render_approved_reviews_page')
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
        if ($current_version < '1.3') {
	        $this->fp_update_database();
        	add_action('admin_notices', array(&$this, 'fp_admin_notices'));
        }
        if (($newest_version != $current_version) || ($newest_version == '1.0')) {
	        update_option($this->fp_admin_options, array('version' => $newest_version));
        }
	}
	
	function fp_admin_notices() {
		$options = get_option($this->fp_admin_options);
		$current_version = $options['version'];
		if ($current_version == '1.3') {
	        echo '
	        	<div class="updated">
	        		<span style="float: left; margin-right: 5px; margin-top: 2px;"><img src="' . plugin_dir_url( __FILE__ ) . 'fox_logo_32x32.png" /></span><p>Thank you for installing Rich Reviews version 1.3!</p>
	        		<p>If you are a new or old user of Rich Reviews, <b style="font-size: 130%">it is highly recommended that you check out the <a href="admin.php?page=rich_reviews_settings_main">instructions page</a></b>. For new users, this will tell you how to use our plugin. For past users, this will give you important information as to how the plugin has changed from previous versions (which is quite a bit!).</p>
	        		<p>We are very excited, and hope that you enjoy using this plugin as much as we enjoy writing it. If you have any questions, issues, or feature requests, please post them in our <a href="http://wordpress.org/extend/plugins/rich-reviews/">Wordpress support forum</a>. Enjoy!</p>
	        	</div>';
        }
		
	}
	
	function fp_load_scripts_styles() {
		$pluginDirectory = trailingslashit(plugins_url(basename(dirname(__FILE__))));
        wp_register_script('rich-reviews', $pluginDirectory . 'js/rich-reviews.js', array('jquery'));
		wp_enqueue_script('rich-reviews');
		wp_register_style('rich-reviews', $pluginDirectory . 'css/rich-reviews.css');
		wp_enqueue_style('rich-reviews');
	}
	
	function fp_load_admin_scripts_styles() {
		$pluginDirectory = trailingslashit(plugins_url(basename(dirname(__FILE__))));
        wp_register_script('rich-reviews', $pluginDirectory . 'js/rich-reviews.js', array('jquery'));
		wp_enqueue_script('rich-reviews');
		wp_register_style('rich-reviews', $pluginDirectory . 'css/rich-reviews.css');
		wp_enqueue_style('rich-reviews');
	}
	
	function fp_add_plugin_settings_link($links) {
		$settings_link = '<a href="admin.php?page=rich_reviews_settings_main">Settings</a>';
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
				 post_id int(11) DEFAULT '0',
				 review_category varchar(100) DEFAULT 'none',
				PRIMARY KEY (id)
				)
				CHARACTER SET utf8
				COLLATE utf8_general_ci;";
		dbDelta($sql);
	}
	
	function fp_render_settings_main_page() {
		$output = '';
		$output .= '<div class="wrap">
			<h2><img src="' . plugin_dir_url( __FILE__ ) . 'fox_logo_32x32.png" /> Rich Reviews</h2>
			</div>
			<p>
			Thank you for using Rich Reviews by <a href="http://www.foxytechnology.com">Foxy Technology</a>!
			</p>
			
			<span class="rr_admin_sidebar">
			<div class="rr_admin_sidebar_title">Shortcode Cheat Sheet</div>
			<div class="rr_admin_sidebar_text">Make sure you read the detailed descriptions of how these work below, in <span style="font-weight: 600;">Shortcode Usage</span>!</div>
			<ul class="rr_admin_sidebar_list">
			<li class="rr_admin_sidebar_list_item">[RICH_REVIEWS_SHOW]</li>
			<li class="rr_admin_sidebar_list_item">[RICH_REVIEWS_SHOW num="9"]</li>
			<li class="rr_admin_sidebar_list_item">[RICH_REVIEWS_SHOW num="all"]</li>
			<li class="rr_admin_sidebar_list_item">[RICH_REVIEWS_SHOW category="foo"]</li>
			<li class="rr_admin_sidebar_list_item" style="margin: 0px 0px 4px 0px;">[RICH_REVIEWS_SHOW category="page" num="5"]</li>
			<li class="rr_admin_sidebar_list_item">[RICH_REVIEWS_FORM]</li>
			<li class="rr_admin_sidebar_list_item" style="margin: 0px 0px 4px 0px;">[RICH_REVIEWS_FORM category="foo"]</li>
			<li class="rr_admin_sidebar_list_item">[RICH_REVIEWS_SNIPPET]</li>
			<li class="rr_admin_sidebar_list_item">[RICH_REVIEWS_SNIPPET category="foo"]</li>
			</ul>
			</span>';
		$output .= '<p>
			This plugin is based around shortcodes. We think that this is the best way to go, as then YOU control where reviews, forms, and snippets are shown - pages, posts, widgets... wherever!
			</p>
			<p>
			There has been a major overhaul in functionality for this plugin! So if you are a past user, make sure you review the following - if you are a new user - welcome! If you require any assistance, have any questions, have any feature requests, or find any bugs, please post them in the <a href="http://wordpress.org/support/plugin/rich-reviews">Wordpress support forum for this plugin</a>!
			</p>
			<p style="font-size: 70%">
			A note about backwards compatibility: If you have been using this plugin since before version 1.3 (when per-page reviews were implemented) then the reviews which were already submitted will have a default category of "none", and will not be attached to any particular page or post. They will still show up in the global reviews, however. Also, I have taken great pains to ensure that all the "old" shortcodes will work in exactly the same way - so even though [RICH_REVIEWS_SHOW_ALL] is now outdated, I have retained its functionality so that your site won\'t break!
			</p>
			<p style="font-size: 120%">
			Please take a moment to <a href="http://wordpress.org/extend/plugins/rich-reviews/">rate and/or review</a> this plugin, and tell people about it - we love hearing feedback, and depend on you to spread the word!
			</p>';
		$output .= '<p>
			Some terminology so that\'s we are all on the same page (no pun intended):
			<ul style="margin-top: -0.75em;">
			<li>A <b>global review</b> is a review which applies or belongs to the entire Wordpress site, regardless of the current page or post. You might use global reviews if your users are submitting reviews for a business or entire website.</li>
			<li>A <b>per-page review</b> is a review which applies to some specific page or post. You might use per-page reviews if, for example, your Wordpress site has various products with a dedicated page or post for each one. Note that reviews users submit will <i>always</i> record the post from which they were submitted, even if you will end up using global reviews! This is to simplify things, so that we don\'t have a bunch of different, confusing shortcodes.</li>
			</ul>
			</p>
			<hr />
			<h2>Shortcode Usage</h2>
			<div class="rr_shortcode_container">
				<div class="rr_shortcode_name">[RICH_REVIEWS_SHOW]</div>
				<div class="rr_shortcode_description">
					This is the main shortcode for this plugin. By default (if no options are given), it will show the first three global reviews which have been approved. Note that this shortcode on its own will NOT display an average/overall score nor any rich snippet markup. See the "snippet" shortcode for that. Here is the shortcode with all possible options, along with their defaults: [RICH_REVIEWS_SHOW category="none" num="3"]. We will now show some examples of using these options.
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW num="8"]</div>
					<div class="rr_shortcode_option_text">
						This will show the first eight approved global reviews. Any integer greater than or equal to one may be used, and note that (given enough room) reviews are displayed in blocks of three.
					</div>
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW num="all"]</div>
					<div class="rr_shortcode_option_text">
						This will show EVERY approved global review which has been posted to your site. This is the only non-integer value which works as the value for the "num" option.
					</div>
				</div>';
		$output .= '<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW category="page"]</div>
					<div class="rr_shortcode_option_text">
						This will show the first three approved reviews for the page or post on which this shortcode appears. You can also use category="post" and achieve the same results (because sometimes you just can\'t remember if you\'re supposed to say post or page! :-) )
					</div>
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW category="foo"]</div>
					<div class="rr_shortcode_option_text">
						This will show the first three approved reviews which have the category "foo" (you might also use categories of "games" or "iPhone" or "bears" (although everyone knows that the best kind of bear is grizzly) ). The categories here are determined by the categories you specify when presenting the review form to your users.
					</div>
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW category="bar" num="6"]</div>
					<div class="rr_shortcode_option_text">
						This will show the first six approved reviews which have the category "bar". Again, you may use any category, and if you specify that category="page" then the first six approved reviews for that particular page/post will be displayed.
					</div>
				</div>
			</div>';
		$output .= '<div class="rr_shortcode_container">
				<div class="rr_shortcode_name">[RICH_REVIEWS_FORM]</div>
				<div class="rr_shortcode_description">
					This shortcode will insert the form which your users fill out to submit their reviews to you. Note that javascript must be enabled (on both your site and on the user\'s computer) in order for this to work. There is one option, shown here with its default: [RICH_REVIEWS_FORM category="none"]. You do NOT need to specify a category of "page" if you want to use per-page reviews. By default, ALL reviews that users submit will record the page or post from which they were submitted.
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_FORM category="foo"]</div>
					<div class="rr_shortcode_option_text">
						This will create a form for users to submit reviews under the category of "foo". Users will not notice a difference, and the form itself does not change based on the category. Again note that if you wish to have per-page reviews, you do NOT need to specify a category of "page" as you do with the review showing shortcode.
					</div>
				</div>
			</div>';
		$output .= '<div class="rr_shortcode_container">
				<div class="rr_shortcode_name">[RICH_REVIEWS_SNIPPET]</div>
				<div class="rr_shortcode_description">
					This shortcode will insert an aggregate (average) score based on all approved reviews. By default, this aggregate score is based on the global reviews (as you might guess, the shortcode with its one option and corresponding default is [RICH_REVIEWS_SNIPPET category="none"]). More importantly for webmasters and those concerned with SEO is that this shortcode tags the aggregate score with Rich Snippet markup so that Google (and other search engines) will see the average score on that page, and display stars next to that page when it shows up in search results.<br />
					This is given as a seperate shortcode, rather than integrated into the "show" shortcode, so that you may place this in, say, your footer and be able to have Rich Snippets on every page and post, without also having reviews taking up space.<br />
					You can test your page <a href="http://www.google.com/webmasters/tools/richsnippets">here</a>. Note that Google is vague with exactly how exactly they give search results. It might take some time for the stars to show up next to your page, and it might only show up with specific search terms. The best thing you can do is make sure that the Rich Snippets tool, above, recognizes the star rating on your page, and be patient. We are constantly working to make sure we keep up with Google to ensure these ratings are displayed.
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_FORM category="foo"]</div>
					<div class="rr_shortcode_option_text">
						This will display the aggregate (average) score, along with the Rich Snippet markup, for all approved reviews with the category "foo".
					</div>
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_FORM category="page"]</div>
					<div class="rr_shortcode_option_text">
						This will display the aggregate (average) score, along with the Rich Snippet markup, for all approved reviews for the current page/post (again, you may equivalently use category="post").
					</div>
				</div>
			</div>
';
		echo $output;
	}
	
	function fp_render_pending_reviews_page() {
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		require_once(dirname(__FILE__) . '/rich-reviews-admin-tables.php');
		$rich_review_admin_table = new Rich_Reviews_Table();
		$rich_review_admin_table->prepare_items('pending');
		echo '<div class="wrap"><h2><img src="' . plugin_dir_url( __FILE__ ) . 'fox_logo_32x32.png" /> Pending Reviews</h2></div>';
		echo '<form id="form" method="POST">';
		$rich_review_admin_table->display();
		echo '</form>';
	}
	
	function fp_render_approved_reviews_page() {
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		require_once(dirname(__FILE__) . '/rich-reviews-admin-tables.php');
		$rich_review_admin_table = new Rich_Reviews_Table();
		$rich_review_admin_table->prepare_items('approved');
		echo '<div class="wrap"><h2><img src="' . plugin_dir_url( __FILE__ ) . 'fox_logo_32x32.png" /> Approved Reviews</h2></div>';
		echo '<form id="form" method="POST">';
		$rich_review_admin_table->display();
		echo '</form>';
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
	
	function fp_star_rating_input() {
		/*$output = '
			<span onmouseover="this.style.cursor=\'default\';">
			  <span id="s1" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			  <span id="s2" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			  <span id="s3" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			  <span id="s4" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			  <span id="s5" onclick="starSelection(this.id);"onmouseout="outStar(this.id);"onmouseover="overStar(this.id);">&#9734</span>
			</span>';*/
		$output = '<div class="rr_stars_container">
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_1"></span>
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_2"></span>
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_3"></span>
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_4"></span>
			<span class="rr_star glyphicon glyphicon-star-empty" id="rr_star_5"></span>
		</div>';
		return $output;
	}
	
	function fp_shortcode_reviews_form($atts) {
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
				$rStatus   = 0;
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
					$output .= 'Your review has been recorded and submitted for approval, ' . $this->fp_nice_output($rName) . '. Thanks!<br />';
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
			$output .= '			<td class="rr_form_heading rr_required">Review Title</td>';
			$output .= '			<td class="rr_form_input"><input class="rr_small_input" type="text" name="rTitle" value="' . $rTitle . '" /></td>';
			$output .= '		</tr>';
			$output .= '		<tr class="rr_form_row">';
			$output .= '			<td class="rr_form_heading rr_required">Rating</td>';
			$output .= '			<td class="rr_form_input">' . $this->fp_star_rating_input() . '</td>';
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
		return $output;
	}
	
	function fp_shortcode_reviews_show($atts) {
		global $wpdb;
		global $post;
		$output = '';
		extract(shortcode_atts(
			array(
				'category' => 'none',
				'num' => '3',
			)
		,$atts));
		if ($category == 'none') {
			$whereStatement = "WHERE review_status=\"1\"";
		} else if(($category == 'post') || ($category == 'page')) {
			$whereStatement = "WHERE (review_status=\"1\" and post_id=\"$post->ID\")";
		} else {
			$whereStatement = "WHERE (review_status=\"1\" and review_category=\"$category\")";
		}
		if ($num == 'all') {
			$limitStatement = "";
		} else {
			$num = intval($num);
			if($num < 1) {
				$num = 1;
			}
			$limitStatement = "LIMIT $num";
		}
		$approvedReviewsCount = $wpdb->get_var("SELECT COUNT(*) FROM $this->sqltable " . $whereStatement);
		if($approvedReviewsCount != 0){
			$sql = "SELECT id as `idid`,
					date_time as `datetime`,
					reviewer_name as `reviewername`,
					reviewer_email as `revieweremail`,
					review_title as `reviewtitle`,
					review_rating as `reviewrating`,
					review_text as `reviewtext`,
					review_status as `reviewstatus`,
					reviewer_ip as `reviewerip` FROM $this->sqltable " . $whereStatement . " " . $limitStatement;
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
	
	function fp_shortcode_reviews_snippets($atts) {
		global $wpdb;
		global $post;
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

		$output = '<div class="hreview-aggregate">Overall rating: <span class="rating">' . $averageRating . '</span> out of 5 based on <span class="votes">' . $approvedReviewsCount . '</span> reviews</div>';
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
		$rPostID    = $review['postid'];
		$rCategory  = $review['reviewcategory'];
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
		return $output;
	}

	function fp_display_review($review) {
		$rID        = $review['idid'];
		$rDateTime  = $review['datetime'];
		$rName      = $this->fp_nice_output($review['reviewername'], FALSE);
		$rEmail     = $this->fp_nice_output($review['revieweremail'], FALSE);
		$rTitle     = $this->fp_nice_output($review['reviewtitle'], FALSE);
		$rRatingVal = max(1,intval($review['reviewrating']));
		$rText      = $this->fp_nice_output($review['reviewtext']);
		$rStatus    = $review['reviewstatus'];
		$rIP        = $review['reviewerip'];
		$rRating = '';

		for ($i=1; $i<=$rRatingVal; $i++) {
			$rRating .= '&#9733'; // orange star
		}
		for ($i=$rRatingVal+1; $i<=5; $i++) {
			$rRating .= '&#9734'; // white star
		}
		
		$output = '<div class="testimonial">
			<h3 class="rr_title">' . $rTitle . '</h3>
			<div class="clear"></div>
			<div class="stars">' . $rRating . '</div>
			<div class="clear"></div>';
		$output .= '<div class="rr_review_text"><span class="drop_cap">“</span>' . $rText . '”</div>';
		$output .= '<div class="rr_review_name"> - ' . $rName . '</div>
			<div class="clear"></div>';
		$output .= '</div>';
		return $output;
	}
	
	function fp_nice_output($input, $keep_breaks = TRUE) {
		//echo '<pre>' . $input . '</pre>';
		//return str_replace(array('\\', '/'), '', $input);
		if (strpos($input, '\r\n')) {
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
		$input = str_replace(array('\\', '/'), '', $input);

		return $input;
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