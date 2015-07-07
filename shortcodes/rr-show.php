<?php

	//
	// This file contains all functions specifically pertinant to the display of Reviews
	// TODO: Modify to use filters

	function handle_show($reviews, $options) {
		global $wpdb;
		global $post;
		$output = '';

		// Set up the SQL query


		// Show the reviews
		if (count($reviews)) {
			$total_count = count($reviews);
			$review_count = 0;
			?> <div class="testimonial_group"> <?php
			foreach($reviews as $review) {
				display_review($review, $options);
				$review_count += 1;
				if ($review_count == 3) {

					// end the testimonial_group
					?> </div>

					<!-- clear the floats -->
					<div class="clear"></div> <?php

					// do we have more reviews to show?
					if ($review_count < $total_count) {
						?> <div class="testimonial_group"> <?php
					}

					// reset the counter
					$review_count = 0;
					$total_count = $total_count - 3;
				}
			}
			// do we need to close a testimonial_group?
			if ($review_count != 0) {
				?>
				</div>
				<div class="clear"></div>
				<?php
			}

		}
		do_action('rr_close_testimonial_group', $options);
	}

	function display_review($review, $options) {

		$date = strtotime($review->date_time);
		$data = array(
			'rID'       => $review->id,
			'rDateTime' => $review->date_time,
			'date' 		=> strtotime($review->date_time),
			'rDay'		=> date("j", $date),
			'rMonth'	=> date("F", $date),
			'rWday'		=> date("l", $date),
			'rYear'		=> date("Y", $date),
			'rDate' 	=> '',
			// 'rDate' 		=> $rMonth . ' ' . $rDay . $rSuffix . ', '  . $rYear,
			'rName'     => $review->reviewer_name,
			'rEmail'    => $review->reviewer_email,
			'rTitle'    => $review->review_title,
			'rRatingVal'=> max(1,intval($review->review_rating)),
			'rText'     => $review->review_text,
			'rStatus'   => $review->review_status,
			'rIP'       => $review->reviewer_ip,
			'rPostId'   => $review->post_id,
			'rRating' 	=> '',
			'rFull'		=> false
		);

		//$rAuthorImage = $review->reviewer_image_id;


		for ($i=1; $i<=$data['rRatingVal']; $i++) {
			$data['rRating'] .= '&#9733;'; // orange star
		}
		for ($i=$data['rRatingVal']+1; $i<=5; $i++) {
			$data['rRating'] .= '&#9734;'; // white star
		}


		$data['rDate'] = $data['rWday'] . ', ' . $data['rMonth'] . ' ' . $data['rDay'] . ', ' . $data['rYear'];

		if($options['display_full_width']) {
			$data['rFull'] = true;
		}

		do_action('rr_do_review_wrapper', $data);

		do_action('rr_do_review_content', $data);
	}

function full_width_wrapper($data) {
	#TODO: Rework output for rich data, image, and up/down vote
	#?>
	<div class="full-testimonial" itemscope itemtype="http://schema.org/Review">
		<div class="review-head">
		<?php //if($data['rAuthorImage']) {
			?>
				<!-- <div class="user-image"> -->
					<?php //wp_get_attachment_image( $data['rAuthorImage'], [70, 70]); ?>
				<!-- </div> -->
			<?php //} ?>
		<div class="review-info">
		<h3 class="rr_title"><?php echo $data['rTitle']; ?></h3>
		<div class="clear"></div>
	<?php
}

function column_wrapper ($data) {
	?>
	<div class="testimonial" itemscope itemtype="http://schema.org/Review">
		<h3 class="rr_title" itemprop="name"><?php echo $data['rTitle']; ?></h3>
		<div class="clear"></div>
	<?php
}

function do_post_title ($data) {
	// ob_start();
	?>
		<span itemprop="itemReviewed" itemscope itemtype="http://schema.org/Product">
			<div class="rr_review_post_id" itemprop="name">
				<a href="<?php echo get_permalink($data['rPostId']); ?>">
					<?php echo get_the_title($data['rPostId']); ?>
				</a>
			</div>
			<div class="clear"></div>
		</span>
	<?php
	// return ob_get_clean();
}

function do_hidden_post_title ($data) {

	?>
	<span itemprop="itemReviewed" itemscope itemtype="http://schema.org/Product">
		<div class="rr_review_post_id" itemprop="itemreviewed" style="display:none;">
			<a href="<?php echo get_permalink($data['rPostId']); ?>">
				<?php echo get_the_title($data['rPostId']); ?>
			</a>
		</div>
		<div class="clear"></div>
	</span>
	<?php
}

function do_the_date ($data) {
	if($data['rDateTime'] != "0000-00-00 00:00:00") {
		// ob_start();
		?>
		<span class="rr_date"><meta itemprop="datePublished" content="<?php echo $data['rDateTime']; ?>">
			<time datetime="<?php echo $data['rDate']; ?>">
				<?php echo $data['rDate']; ?>
			</time>
		</span>
	<?php } else {
		if(current_user_can('edit_posts')) { ?>
		<span class="date-err rr_date">
			<?php echo __('Date improperly formatted, correct in ', 'rich-reviews'); ?>
			<a href="/wp-admin/admin.php?page=fp_admin_approved_reviews_page">
				<?php echo __('Dashboard', 'rich-reviews'); ?>
			</a>
		</span>

	<?php	}
	}
	// return ob_get_clean();
}

function do_the_date_hidden ($data) {
		if($data['rDateTime'] != "0000-00-00 00:00:00") {
		?>
		<span class="rr_date" style="display:none;"><meta itemprop="datePublished" content="<?php echo $data['rDateTime']; ?>">
			<time datetime="<?php echo $data['rDate']; ?>">
				<?php echo $data['rDate']; ?>
			</time>
		</span>
	<?php
	}
}

function do_review_body ($data) {
	?>
		<div class="stars">
			<?php echo $data['rRating']; ?>
		</div>
		<div style="display:none;" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
			<span itemprop="ratingValue">
				<?php echo $data['rRatingVal']; ?>

			</span>
			<span itemprop="bestRating">
				5
			</span>
			<span itemprop="worstRating">
				1
			</span>
		</div>


		<?php if($data['rFull']) {
			?>
				</div> <!-- close .review-info -->
			</div> <!-- close .review-head -->

		<?php } ?>


		<div class="clear"></div>

		<div class="rr_review_text"  ><span class="drop_cap">“</span><span itemprop="reviewBody"><?php echo $data['rText']; ?></span>”</div>
			<div class="rr_review_name" itemprop="author" itemscope itemtype="http://schema.org/Person"> - <span itemprop="name"><?php echo $data['rName']; ?></span></div>
			<div class="clear"></div>
		</div>
	<?php
}

function print_credit() {
	?>
		<div class="credit-line">
			<?php echo __('Supported By: ', 'rich-reviews'); ?>
			<a href="http://nuancedmedia.com/" rel="nofollow">
				<?php echo 'Nuanced Media'; ?>
			</a>
		</div>
		<div class="clear"></div>
	<?php
}
