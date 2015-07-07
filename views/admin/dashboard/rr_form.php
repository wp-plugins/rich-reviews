<?php
 ?>

<div class="rr_shortcode_container">
	<div class="rr_shortcode_name">[RICH_REVIEWS_FORM]</div>
	<div class="rr_shortcode_description">
		<?php echo __('This shortcode will insert the form which your users fill out to submit their reviews to you. Note that javascript must be enabled (on both your site and on the user\'s computer) in order for this to work. There is one option, shown here with its default: [RICH_REVIEWS_FORM category="none"]. You do NOT need to specify a category of "page" if you want to use per-page reviews. By default, ALL reviews that users submit will record the page or post from which they were submitted.', 'rich-reviews'); ?>
	</div>
	<div class="rr_shortcode_option_container">
		<div class="rr_shortcode_option_name">[RICH_REVIEWS_FORM category="foo"]</div>
		<div class="rr_shortcode_option_text">
			<?php echo __('This will create a form for users to submit reviews under the category of "foo". Users will not notice a difference, and the form itself does not change based on the category. Again note that if you wish to have per-page reviews, you do NOT need to specify a category of "page" as you do with the review showing shortcode.', 'rich-reviews'); ?>
		</div>
	</div>
</div>
