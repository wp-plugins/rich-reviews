<?php

/*
 * This contains all the admin stuff so
 * that the main file can be nice and clean
 */

class RichReviewsAdmin {

	var $parent;
	var $db;
	
	function __construct($parent) {
		$this->parent = $parent;
		$this->db = $this->parent->db;
		add_action('admin_menu', array(&$this, 'init_admin_menu'));
		add_action( 'admin_enqueue_scripts', array(&$this, 'load_admin_scripts_styles'), 100);
		add_filter('plugin_action_links_rich-reviews/rich-reviews.php', array(&$this, 'add_plugin_settings_link'));
	}
	
	function init_admin_menu() {
		global $wpdb;
		$pendingReviewsCount = $this->db->pending_reviews_count();
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
			array(&$this, 'render_settings_main_page'),
			$this->parent->logo_small_url,
			'25.11'
		);
		add_submenu_page(
			'rich_reviews_settings_main', // ID of menu with which to register this submenu
			'Rich Reviews - Instructions', //text to display in browser when selected
			'Instructions', // the text for this item
			'administrator', // which type of users can see this menu
			'rich_reviews_settings_main', // unique ID (the slug) for this menu item
			array(&$this, 'render_settings_main_page') // callback function
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - Pending Reviews',
			'Pending Reviews' . $pendingReviewsText,
			'administrator',
			'fp_admin_pending_reviews_page',
			array(&$this, 'render_pending_reviews_page')
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - Approved Reviews',
			'Approved Reviews',
			'administrator',
			'fp_admin_approved_reviews_page',
			array(&$this, 'render_approved_reviews_page')
		);
	}
	
	function load_admin_scripts_styles() {
        wp_register_script('rich-reviews', trailingslashit($this->parent->plugin_url) . 'js/rich-reviews.js', array('jquery'));
		wp_enqueue_script('rich-reviews');
		wp_register_style('rich-reviews', trailingslashit($this->parent->plugin_url) . 'css/rich-reviews.css');
		wp_enqueue_style('rich-reviews');
		wp_register_style('rich-reviews2', trailingslashit($this->parent->plugin_url) . 'css/rr-old.css');
		wp_enqueue_style('rich-reviews2');
	}
	
	function add_plugin_settings_link($links) {
		$settings_link = '<a href="admin.php?page=rich_reviews_settings_main">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	
	function render_settings_main_page() {
		$output = '';
		$output .= '<div class="wrap">
			<h2><img src="' . $this->parent->logo_url . '" /> Rich Reviews</h2>
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
	
	function render_pending_reviews_page() {
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		require_once('rich-reviews-admin-tables.php');
		$rich_review_admin_table = new Rich_Reviews_Table();
		$rich_review_admin_table->prepare_items('pending');
		echo '<div class="wrap"><h2><img src="' . $this->parent->logo_url . '" /> Pending Reviews</h2></div>';
		echo '<form id="form" method="POST">';
		$rich_review_admin_table->display();
		echo '</form>';
	}
	
	function render_approved_reviews_page() {
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		require_once('rich-reviews-admin-tables.php');
		$rich_review_admin_table = new Rich_Reviews_Table();
		$rich_review_admin_table->prepare_items('approved');
		echo '<div class="wrap"><h2><img src="' . $this->parent->logo_url . '" /> Approved Reviews</h2></div>';
		echo '<form id="form" method="POST">';
		$rich_review_admin_table->display();
		echo '</form>';
	}

}
