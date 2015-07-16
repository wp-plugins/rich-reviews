<?php

?>

<h3><strong><?php _e('Rich Markup Options', 'rich-reviews'); ?></strong></h3>
	<div style="border: solid 2px black"></div>

	<h4><strong><?php _e('Subject Fallback Options', 'rich-reviews'); ?></strong></h4>
	<p><small><?php _e('In order to output complete markup for for recognizable "Review" rich schema, there must be a value set for the item reviewed itemprop. Rich Reviews does this by using the category for which reviews are set. If this is not set, Rich Reviews will use the Page Title of the page from which the review was submited. However, if neither of these items are set, there needs to be a fallback set. You can do this, and adjust it\'s use case below. (category="page" or category="post" is considered a category being set, and will use the page title if available, or the fallback if not)', 'rich-reviews'); ?>
	</small></p>
	<label for="subject-fallback">
		<strong><?php _e('Fallback Value: ', 'rich-reviews'); ?></strong>
	</label>
	<input type="text" name="rich_itemReviewed_fallback" value="<?php echo $options['rich_itemReviewed_fallback']; ?>" />
	<br />
	<label for="rich_itemReviewed_fallback_case" style="width:20%;">
		<strong><?php _e('Use fallback:', 'rich-reviews'); ?></strong>
	</label>
	<select name="rich_itemReviewed_fallback_case" style="width:80%;">
		<?php
		if ($options['rich_itemReviewed_fallback_case']==="always"){ ?><option value="always" selected="selected"><?php _e('Always, regardless of category.', 'rich-reviews'); ?></option><?php }else {?><option value="always" ><?php _e('Always, regardless of category.', 'rich-reviews'); ?></option><?php }
		if ($options['rich_itemReviewed_fallback_case']==="category_missing"){ ?><option value="category_missing" selected="selected"><?php _e('When no category is specified.', 'rich-reviews'); ?></option><?php }else {?><option value="category_missing" ><?php _e('When no category is specified.', 'rich-reviews'); ?></option><?php }
		if ($options['rich_itemReviewed_fallback_case']==="both_missing"){ ?><option value="both_missing" selected="selected"><?php _e('When no category is specified, and parent page has no title.', 'rich-reviews'); ?></option><?php }else {?><option value="both_missing" ><?php _e('When category isnt\'t specified, and parent page has no title.', 'rich-reviews'); ?></option><?php }
		?>
	</select>
	<h4><strong><?php _e('Author Fallback Options', 'rich-reviews'); ?></strong></h4>
	<p><small><?php _e('The Author field is an optional data value in rich formatting for a "Review", however the more information provided, the better one\'s reviews will appear. For this reason Rich Reviews has an Author fallback as well for the case that the "Name" field is either not used or not required. You can set that below.', 'rich-reviews'); ?>
	</small></p>
	<br />
	<label for="author-fallback">
		<strong><?php _e('Fallback Value: ', 'rich-reviews'); ?></strong>
	</label>
	<input type="text" name="rich_author_fallback" value="<?php echo $options['rich_author_fallback']; ?>" />

	<h4><strong><?php _e('Rich URL Options', 'rich-reviews'); ?></strong></h4>
	<input type="checkbox" name="rich_include_url" value="checked" <?php echo $options['rich_include_url'] ?> />
	<label for="rich_include_url">
		<?php _e('Include URL rich data - This will add a block of markup to the reviews output to communicate a URL value for rich schema.', 'rich-reviews'); ?>
	</label>
	<br />
	<br />
	<label for="rich_url_value">
		<?php _e('The URL to use in your Rich Markup.', 'rich-reviews'); ?>
	</label>
	<br />
	<strong>http://</strong><input type="text" name="rich_url_value" style="width:90%;" value="<?php echo $options['rich_url_value'] ?>" />
	<br />
