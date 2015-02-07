<?php

/*
 * Database functions for Rich Reviews
 */

class RichReviewsDB extends NMDB {

	var $parent;
	var $debug_queries = FALSE;

	function __construct($parent) {
		global $wpdb;
		$this->parent = $parent;
		$this->sqltable = $this->parent->sqltable;
        $tableSearch = $wpdb->get_var("SHOW TABLES LIKE '$this->sqltable'");
        if ($tableSearch != $this->sqltable) {
            $this->create_update_database();
        }
	}

	function create_update_database() {
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
				PRIMARY KEY  (id)
				)
				CHARACTER SET utf8
				COLLATE utf8_general_ci;";
		dbDelta($sql);
	}

	function pending_reviews_count() {
		$this->select('COUNT(*)');
		$this->where('review_status', 0);
		return $this->get_var();
	}

	function approved_reviews_count() {
		$this->select('COUNT(*)');
		$this->where('review_status', 1);
		return $this->get_var();
	}

}
