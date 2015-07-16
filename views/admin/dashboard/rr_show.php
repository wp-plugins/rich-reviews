<?php

?>

<div class="rr_shortcode_container">
	<div class="rr_shortcode_name">[RICH_REVIEWS_SHOW]</div>
	<div class="rr_shortcode_description">
		<?php echo __('This is the main shortcode for this plugin. By default (if no options are given), it will show the first three global reviews which have been approved. Note that this shortcode on its own will NOT display an average/overall score nor any rich snippet markup. See the "snippet" shortcode for that. Here is the shortcode with all possible options, along with their defaults: ', 'rich-reviews'); ?>[RICH_REVIEWS_SHOW category="none" num="3"].<?php echo __(' We will now show some examples of using these options.', 'rich-reviews'); ?>
	</div>
	<div class="rr_shortcode_option_container">
		<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW num="8"]</div>
		<div class="rr_shortcode_option_text">
			<?php echo __('This will show the first eight approved global reviews. Any integer greater than or equal to one may be used, and note that (given enough room) reviews are displayed in blocks of three.', 'rich-reviews'); ?>
		</div>
	</div>
	<div class="rr_shortcode_option_container">
		<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW num="all"]</div>
		<div class="rr_shortcode_option_text">
			<?php echo __('This will show EVERY approved global review which has been posted to your site. This is the only non-integer value which works as the value for the "num" option.', 'rich-reviews'); ?>
		</div>
	</div>
	<div class="rr_shortcode_option_container">
		<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW category="page"]</div>
		<div class="rr_shortcode_option_text">
			<?php echo __('This will show the first three approved reviews for the page or post on which this shortcode appears. You can also use category="post" and achieve the same results (because sometimes you just can\'t remember if you\'re supposed to say post or page! :-) )', 'rich-reviews'); ?>
		</div>
	</div>
	<div class="rr_shortcode_option_container">
		<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW category="foo"]</div>
		<div class="rr_shortcode_option_text">
			<?php echo __('This will show the first three approved reviews which have the category "foo" (you might also use categories of "games" or "iPhone" or "bears" (although everyone knows that the best kind of bear is grizzly) ). The categories here are determined by the categories you specify when presenting the review form to your users.', 'rich-reviews'); ?>
		</div>
	</div>
	<div class="rr_shortcode_option_container">
		<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW category="all"]</div>
		<div class="rr_shortcode_option_text">
			<?php echo __('This will show the first three approved reviews regardless of category. The "all" value submitted for the category parameter will remove the category filter from the reviews query returning reviews of all categories. Values passed to the "num" parameter ill obviously still have it\'s standard effect modifiying the quantity of reviews displayed.', 'rich-reviews'); ?>
		</div>
	</div>
	<div class="rr_shortcode_option_container">
		<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW category="bar" num="6"]</div>
		<div class="rr_shortcode_option_text">
			<?php echo __('This will show the first six approved reviews which have the category "bar". Again, you may use any category, and if you specify that category="page" then the first six approved reviews for that particular page/post will be displayed.', 'rich-reviews'); ?>
		</div>
	</div>
	<div class="rr_shortcode_option_container">
		<div class="rr_shortcode_option_name">[RICH_REVIEWS_SHOW category="all" num="all"]</div>
		<div class="rr_shortcode_option_text">
			<?php echo __('This will show all of the approved reviews from your website regardless of category. Any review that is approved will be retured by this shortcode.', 'rich-reviews'); ?>
		</div>
	</div>
</div>
