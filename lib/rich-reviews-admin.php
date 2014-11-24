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
		$required_role = $this->parent->options->get_option('approve_authority');
		add_menu_page(
			'Rich Reviews Settings',
			'Rich Reviews' . $pendingReviewsText,
			$required_role,
			'rich_reviews_settings_main',
			array(&$this, 'render_settings_main_page'),
			$this->parent->logo_small_url,
			'25.11'
		);
		add_submenu_page(
			'rich_reviews_settings_main', // ID of menu with which to register this submenu
			'Rich Reviews - Instructions', //text to display in browser when selected
			'Instructions', // the text for this item
			$required_role, // which type of users can see this menu
			'rich_reviews_settings_main', // unique ID (the slug) for this menu item
			array(&$this, 'render_settings_main_page') // callback function
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - Pending Reviews',
			'Pending Reviews' . $pendingReviewsText,
			$required_role,
			'fp_admin_pending_reviews_page',
			array(&$this, 'render_pending_reviews_page')
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - Approved Reviews',
			'Approved Reviews',
			$required_role,
			'fp_admin_approved_reviews_page',
			array(&$this, 'render_approved_reviews_page')
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - Options',
			'Options',
			$required_role,
			'fp_admin_options_page',
			array(&$this, 'render_options_page')
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - Add/Edit',
			'Add New Review',
			$required_role,
			'fp_admin_add_edit',
			array(&$this, 'render_add_edit_page')
		);
	}

	function load_admin_scripts_styles() {
        wp_register_script('rich-reviews', trailingslashit($this->parent->plugin_url) . 'js/rich-reviews.js', array('jquery'));
		wp_enqueue_script('rich-reviews');
        wp_register_script('rich-reviews-dashboard', trailingslashit($this->parent->plugin_url) . 'views/view-helper/js/nm-dashboard-script.js', array('jquery'));
		wp_enqueue_script('rich-reviews-dashboard');
		wp_register_style('rich-reviews', trailingslashit($this->parent->plugin_url) . 'css/rich-reviews.css');
		wp_enqueue_style('rich-reviews');
		//wp_register_style('rich-reviews2', trailingslashit($this->parent->plugin_url) . 'css/rr-old.css');
		//wp_enqueue_style('rich-reviews2');
	}

    function wrap_admin_page($page = null) {
        echo '<div class="nm-admin-page wrap"><h2><img src="' . $this->parent->logo_url . '" /> Pending Reviews</h2></div>';
        NMRichReviewsAdminHelper::render_tabs();
        NMRichReviewsAdminHelper::render_container_open('content-container');
        if ($page == 'main') {

			// NMRichReviewsAdminHelper::render_postbox_open('We Have A New Website');
			// echo $this->render_new_site_banner();
			// NMRichReviewsAdminHelper::render_postbox_close();

            NMRichReviewsAdminHelper::render_postbox_open('Instructions');
            echo $this->render_settings_main_page(TRUE);
            NMRichReviewsAdminHelper::render_postbox_close();
            $this->render_shortcode_usage();
        }
        if ($page == 'pending') {
            NMRichReviewsAdminHelper::render_postbox_open('Pending Reviews');
            echo $this->render_pending_reviews_page(TRUE);
            NMRichReviewsAdminHelper::render_postbox_close();
        }
        if ($page == 'approved') {
            NMRichReviewsAdminHelper::render_postbox_open('Approved Reviews');
            echo $this->render_approved_reviews_page(TRUE);
            NMRichReviewsAdminHelper::render_postbox_close();
        }
		if ($page == 'options') {
			NMRichReviewsAdminHelper::render_postbox_open('Options');
			echo $this->render_options_page(TRUE);
			NMRichReviewsAdminHelper::render_postbox_close();
		}
		if ($page == 'add/edit') {
			NMRichReviewsAdminHelper::render_postbox_open('Add/Edit');
			echo $this->render_add_edit_page(TRUE);
			NMRichReviewsAdminHelper::render_postbox_close();
		}
        NMRichReviewsAdminHelper::render_container_close();
        NMRichReviewsAdminHelper::render_container_open('sidebar-container');
        $permission = $this->get_option('permission');
        $this->update_credit_permission();
        if (!$permission == 'checked') {
            NMRichReviewsAdminHelper::render_postbox_open("Support the Staff");
            echo $this->insert_credit_permission_checkbox();
            NMRichReviewsAdminHelper::render_postbox_close();
        }
        NMRichReviewsAdminHelper::render_sidebar();
        NMRichReviewsAdminHelper::render_container_close();
        echo '<div class="clear"></div>';
    }

	function add_plugin_settings_link($links) {
		$settings_link = '<a href="admin.php?page=rich_reviews_settings_main">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	function render_settings_main_page($wrapped = false) {
        if (!$wrapped) {
            $this->wrap_admin_page('main');
            return;
        }
		$output = '';
		$output .= '<div class="wrap">
			</div>


			<div class="rr_admin_sidebar">
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
			</div>';
		$output .= '<div class="rr_admin_sidebar">';
		$output .= '<div class="rr_admin_sidebar_title">Support the developers!</div>';
		$output .= $this->insert_credit_permission_checkbox();
		$output .= '</div>';
        $output .= '<p>Thank you for using Rich Reviews by Foxy Technology and <a href="http://nuancedmedia.com">Nuanced Media</a>!</p>';
		$output .= '<p>
			This plugin is based around shortcodes. We think that this is the best way to go, as then YOU control where reviews, forms, and snippets are shown - pages, posts, widgets... wherever!
			</p>
			<p style="font-size: 120%">
			Please take a moment to <a href="http://wordpress.org/extend/plugins/rich-reviews/">rate and/or review</a> this plugin, and tell people about it - we love hearing feedback, and depend on you to spread the word!
			</p>';
		$output .= '<p>
			Some terminology so that we are all on the same page:
			<ul style="">
			<li>A <b>global review</b> is a review which applies or belongs to the entire Wordpress site, regardless of the current page or post. You might use global reviews if your users are submitting reviews for a business or entire website.</li>
			<li>A <b>per-page review</b> is a review which applies to some specific page or post. You might use per-page reviews if, for example, your Wordpress site has various products with a dedicated page or post for each one. Note that reviews users submit will <i>always</i> record the post from which they were submitted, even if you will end up using global reviews! This is to simplify things, so that we don\'t have a bunch of different, confusing shortcodes.</li>
			</ul>
			</p>';
		/*$output .= '<h2>Shortcode Usage</h2>
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
			</div>';*/
		/*$output .= '<div class="rr_shortcode_container">
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
			</div>';*/
		/*$output .= '<div class="rr_shortcode_container">
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
		';*/

		$output .= '<div class="clear"></div>';
		echo $output;
        //NMMeetupAdminHelper::render_postbox_close();


	}

    function render_shortcode_usage() {
        //NMMeetupAdminHelper::render_postbox_open('Shortcode Usage');

        NMRichReviewsAdminHelper::render_postbox_open('[RICH_REVIEWS_SHOW]');
        $this->render_rr_show_content();
        NMRichReviewsAdminHelper::render_postbox_close();

        NMRichReviewsAdminHelper::render_postbox_open('[RICH_REVIEWS_FORM]');
        $this->render_rr_form_content();
        NMRichReviewsAdminHelper::render_postbox_close();

        NMRichReviewsAdminHelper::render_postbox_open('[RICH_REVIEWS_SNIPPET]');
        $this->render_rr_snippet_content();
        NMRichReviewsAdminHelper::render_postbox_close();

        //NMMeetupAdminHelper::render_postbox_close();
    }

    function render_rr_show_content() {
        $output = '<div class="rr_shortcode_container">
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
        echo $output;
    }

    function render_rr_form_content() {
        $output = '<div class="rr_shortcode_container">
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
        echo $output;
    }

    function render_rr_snippet_content() {
        $output = '<div class="rr_shortcode_container">
				<div class="rr_shortcode_name">[RICH_REVIEWS_SNIPPET]</div>
				<div class="rr_shortcode_description">
					This shortcode will insert an aggregate (average) score based on all approved reviews. By default, this aggregate score is based on the global reviews (as you might guess, the shortcode with its one option and corresponding default is [RICH_REVIEWS_SNIPPET category="none"]). More importantly for webmasters and those concerned with SEO is that this shortcode tags the aggregate score with Rich Snippet markup so that Google (and other search engines) will see the average score on that page, and display stars next to that page when it shows up in search results.<br />
					This is given as a seperate shortcode, rather than integrated into the "show" shortcode, so that you may place this in, say, your footer and be able to have Rich Snippets on every page and post, without also having reviews taking up space.<br />
					You can test your page <a href="http://www.google.com/webmasters/tools/richsnippets">here</a>. Note that Google is vague with exactly how exactly they give search results. It might take some time for the stars to show up next to your page, and it might only show up with specific search terms. The best thing you can do is make sure that the Rich Snippets tool, above, recognizes the star rating on your page, and be patient. We are constantly working to make sure we keep up with Google to ensure these ratings are displayed.
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_SNIPPET category="foo"]</div>
					<div class="rr_shortcode_option_text">
						This will display the aggregate (average) score, along with the Rich Snippet markup, for all approved reviews with the category "foo".
					</div>
				</div>
				<div class="rr_shortcode_option_container">
					<div class="rr_shortcode_option_name">[RICH_REVIEWS_SNIPPET category="page"]</div>
					<div class="rr_shortcode_option_text">
						This will display the aggregate (average) score, along with the Rich Snippet markup, for all approved reviews for the current page/post (again, you may equivalently use category="post").
					</div>
				</div>
			</div>
		';
        echo $output;
    }

	function render_pending_reviews_page($wrapped = null) {
        if (!$wrapped) {
            $this->wrap_admin_page('pending');
            return;
        }
		require_once('rich-reviews-admin-tables.php');
		$rich_review_admin_table = new Rich_Reviews_Table();
		$rich_review_admin_table->prepare_items('pending');
		echo '<form id="form" method="POST">';
		$rich_review_admin_table->display();
		echo '</form>';
	}

	function render_approved_reviews_page($wrapped) {
        if (!$wrapped) {
            $this->wrap_admin_page('approved');
            return;
        }
		require_once('rich-reviews-admin-tables.php');
		$rich_review_admin_table = new Rich_Reviews_Table();
		$rich_review_admin_table->prepare_items('approved');
		echo '<form id="form" method="POST">';
		$rich_review_admin_table->display();
		echo '</form>';
	}

	function render_options_page($wrapped) {
		$options = $this->parent->options->get_option();
		if (!$wrapped) {
			$this->wrap_admin_page('options');
			return;
		}
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		?>
		<form id="rr-admin-options-form" action="" method="post">
			<input type="hidden" name="update" value="rr-update-options">

			<input type="checkbox" name="snippet_stars" value="checked" <?php echo $options['snippet_stars'] ?> />
			<label for="snippet_stars">Star Snippets - this will change the averge rating displayed in the snippet shortcodeto be stars instead of numerical values.</label>
			<br />
			<input type="checkbox" name="show_form_post_title" value="checked" <?php echo $options['show_form_post_title'] ?> />
			<label for="show_form_post_title">Include Post Titles - this will include the title and a link to the form page for every reviews.</label>
			<br />
			<input type="checkbox" name="credit_permission" value="checked" <?php echo $options['credit_permission'] ?> />
			<label for="credit_permission">Give Credit to Nuanced Media - this option will add a small credit line and a link to Nuanced Media's website to the bottom of your reviews page</label>
			<br />
			<input type="checkbox" name="require_approval" value="checked" <?php echo $options['require_approval'] ?> />
			<label for="require_approval">Require Approval - this sends all new reviews to the pending review page. Unchecking this will automatically publish all reviews as they are submitted.</label>
			<br />
			<input type="checkbox" name="show_date" value="checked" <?php echo $options['show_date'] ?> />
			<label for="show_date">Display the date that the review was submitted inside the review.</label>
			<br />
			<input type="color" name="star_color" value="<?php echo $options['star_color'] ?>">
			<label>Star Color - the color of the stars on reviews</label>
			<br />

			<select name="reviews_order" value="<?php echo $options['reviews_order'] ?>">
				<?php
				if ($options['reviews_order']==="ASC"){ ?><option value="ASC" selected="selected">Oldest First</option><?php }else {?><option value="ASC" >Oldest First</option><?php }
				if ($options['reviews_order']==="DESC"){ ?><option value="DESC" selected="selected">Newest First</option><?php }else {?><option value="DESC" >Newest First</option><?php }
				if ($options['reviews_order']==="random"){ ?><option value="random" selected="selected">Randomize</option><?php }else {?><option value="random" >Randomize</option><?php }
				?>
			</select>
			<label for="reviews_order"> Review Display Order</label>
			<br />
			<select name="approve_authority">
				<?php
				if ($options['approve_authority']==="manage_options"){ ?><option value="manage_options" selected="selected">Admin</option><?php }else {?><option value="manage_options" >Admin</option><?php }
				if ($options['approve_authority']==="moderate_comments"){ ?><option value="moderate_comments" selected="selected">Editor</option><?php }else {?><option value="moderate_comments" >Editor</option><?php }
				if ($options['approve_authority']==="edit_published_posts"){ ?><option value="edit_published_posts" selected="selected">author</option><?php }else {?><option value="edit_published_posts" >Author</option><?php }
				if ($options['approve_authority']==="edit_posts"){ ?><option value="edit_posts" selected="selected">Contributor</option><?php }else {?><option value="edit_posts" >Contributor</option><?php }
				if ($options['approve_authority']==="read"){ ?><option value="read" selected="selected">Subscriber</option><?php }else {?><option value="read" >Subscriber</option><?php }
				?>
			</select>
			<label for="approve_authority"> Authority level required to Approve Pending Posts</label>
			<br />
			<input type="text" name="review_title" value="<?php echo $options['review_title'] ?>">
			<label>Review Title Text - Upon user request, the ability to change the text on the form from "Review Title" to whatever you would like.</label>
			<br />
			<br />
			<input type="submit" class="button" value="Save Options">
		</form>
		<?php

	}

	function render_add_edit_page($wrapped) {
		$options = $this->parent->options->get_option();
		if (!$wrapped) {
			$this->wrap_admin_page('add/edit');
			return;
		}
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$view = new RRAdminAddEdit($this->parent);
	}

	function insert_credit_permission_checkbox() {

		$this->update_credit_permission();
		$permission = $this->get_option('permission');
		$permission_val = '';
		if ($permission == 'checked') {
			$permission_val = ' checked';
		}
        $output = '<div class="nm-support-box">';
		$output .= '<form action="" method="post" class="credit-option">';
		$output .= '<input type="hidden" name="update_permission" value="rr-update-support" />';
        $output .= '<div class="nm-support-staff-checkbox">';
		$output .= '<input type="checkbox" name="credit_permission_option" value="checked"' .  $permission_val . ' />';
        $output .= '</div>';
        $output .= '<div class="nm-support-staff-label">';
		$output .= '<label for="credit_permission_option">We thank you for choosing to use our plugin! We would appreciate it if you allowed us to put our name on the plugin we work so hard to build. If you would like to support us, please check this box and change your permission settings.</label>';
		$output .= '</div>';
		$output .= '<input type="submit" value="Change Permission Setting" form_id="credit_permission_option" class="nm-support-staff-submit button" />';
		$output .= '</form>';
        $output .= '</div>';

		return $output;
	}

	function update_credit_permission() {

		if (isset($_POST['update_permission']) && $_POST['update_permission'] == 'permissionupdate') {
			$current_permission = $this->get_option('permission');
			if (!isset($_POST['credit_permission_option'])) {
				$permission = '';
			}
			else {
				$permission = 'checked';
			}
			$this->update_option('permission', $permission);
			$_POST['update_permission'] = NULL;
		}
	}

	function get_option($opt_name = '') {
		$options = get_option($this->parent->fp_admin_options);

		// maybe return the whole options array?
		if ($opt_name == '') {
			return $options;
		}

		// are the options already set at all?
		if ($options == FALSE) {
			return $options;
		}

		// the options are set, let's see if the specific one exists
		if (! isset($options[$opt_name])) {
			return FALSE;
		}

		// the options are set, that specific option exists. return it
		return $options[$opt_name];
	}

	function update_option($opt_name, $opt_val = '') {
		// allow a function override where we just use a key/val array
		if (is_array($opt_name) && $opt_val == '') {
			foreach ($opt_name as $real_opt_name => $real_opt_value) {
				$this->update_option($real_opt_name, $real_opt_value);
			}
		} else {
			$current_options = $this->get_option();

			// make sure we at least start with blank options
			if ($current_options == FALSE) {
				$current_options = array();
			}

			$new_option = array($opt_name => $opt_val);
			update_option($this->parent->fp_admin_options, array_merge($current_options, $new_option));
		}
	}

	function render_new_site_banner() {
		?>
			<div class="website-link">
			<p style="width: 80%; float: left;">
				Recently, we have been dealing with an increase in interest in our plugin development. Our email inboxes have been flooded. We decided that we needed a place to centralize all plugin questions and interests. This lead to the creation of an entirely new site. Please click the link and check out our plugins website, where we can satisfy all of your plugin needs.
			</p>
			<a href="http://plugins.nuancedmedia.com/" target="_BLANK" style="width: 20%; float: left; margin-top: 21px;"><button style="padding: 13px; background-color: #049477; border-radius: 5px; color: #ffffff; border: none; width: 100%;">View Plugins Website</button></a>
			<div style="clear: both; float: none;"></div>
			</div>
		<?php
	}

}
