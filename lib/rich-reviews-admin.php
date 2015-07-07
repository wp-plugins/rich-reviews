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
			'Rich Reviews'. __('Settings', 'rich-reviews') ,
			'Rich Reviews' . $pendingReviewsText,
			$required_role,
			'rich_reviews_settings_main',
			array(&$this, 'render_settings_main_page'),
			$this->parent->logo_small_url,
			'25.11'
		);
		add_submenu_page(
			'rich_reviews_settings_main', // ID of menu with which to register this submenu
			'Rich Reviews - '. __('Instructions', 'rich-reviews'), //text to display in browser when selected
			__('Instructions', 'rich-reviews'), // the text for this item
			$required_role, // which type of users can see this menu
			'rich_reviews_settings_main', // unique ID (the slug) for this menu item
			array(&$this, 'render_settings_main_page') // callback function
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - '. __('Pending Reviews', 'rich-reviews'),
			 __('Pending Reviews', 'rich-reviews') . $pendingReviewsText,
			$required_role,
			'fp_admin_pending_reviews_page',
			array(&$this, 'render_pending_reviews_page')
		);
		add_submenu_page(
			'rich_reviews_settings_main',

			'Rich Reviews - ' . __('Approved Reviews', 'rich-reviews'),
			__('Approved Reviews', 'rich-reviews'),
			$required_role,
			'fp_admin_approved_reviews_page',
			array(&$this, 'render_approved_reviews_page')
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - '. __('Options', 'rich-reviews'),
			__('Options', 'rich-reviews'),
			$required_role,
			'fp_admin_options_page',
			array(&$this, 'render_options_page')
		);
		add_submenu_page(
			'rich_reviews_settings_main',
			'Rich Reviews - ' . __('Add/Edit','rich-reviews'),
			__('Add New Review', 'rich-reviews'),
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
		$settings_link = '<a href="admin.php?page=rich_reviews_settings_main">' . __('Settings') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	function render_settings_main_page($wrapped = false) {
        if (!$wrapped) {
            $this->wrap_admin_page('main');
            return;
        }

        $supportNM = $this->insert_credit_permission_checkbox();

        ob_start();
        	include $this->parent->path . 'views/admin/dashboard/instructions.php';
        return ob_get_clean();

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
    		include $this->parent->path . 'views/admin/dashboard/rr_show.php';
    }

    function render_rr_form_content() {
    		include $this->parent->path . 'views/admin/dashboard/rr_form.php';
    }

    function render_rr_snippet_content() {
      		include $this->parent->path . 'views/admin/dashboard/rr_snippet.php';
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

		$path = $this->parent->path;
		ob_start();
			include $path . 'views/admin/options/options-index.php';
		return ob_get_clean();
	}

	function render_add_edit_page($wrapped) {
		$options = $this->parent->options->get_option();
		if (!$wrapped) {
			$this->wrap_admin_page('add/edit');
			return;
		}
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.', 'rich-reviews') );
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

		ob_start();
        	include $this->parent->path . 'views/admin/credit-permission.php';
        $output = ob_get_clean();
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
				<?php _e('Recently, we have been dealing with an increase in interest in our plugin development. Our email inboxes have been flooded. We decided that we needed a place to centralize all plugin questions and interests. This lead to the creation of an entirely new site. Please click the link and check out our plugins website, where we can satisfy all of your plugin needs.', 'rich-reviews'); ?>
			</p>
			<a href="http://plugins.nuancedmedia.com/" target="_BLANK" style="width: 20%; float: left; margin-top: 21px;"><button style="padding: 13px; background-color: #049477; border-radius: 5px; color: #ffffff; border: none; width: 100%;"><?php _e('View Plugins Website', 'rich-reviews'); ?></button></a>
			<div style="clear: both; float: none;"></div>
			</div>
		<?php
	}

}
