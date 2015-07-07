<?php

?>

<h3><strong><?php _e('Review Display Options', 'rich-reviews'); ?></strong></h3>
<div style="border: solid 2px black"></div>

<h4><strong><?php _e('Title Options', 'rich-reviews'); ?></strong></h4>
<input type="checkbox" name="show_form_post_title" value="checked" <?php echo $options['show_form_post_title'] ?> />
<label for="show_form_post_title">
	<?php _e('Include Post Titles - this will include the title and a link to the form page for every review.', 'rich-reviews'); ?>
</label>
<br />

<h4><strong><?php _e('Rating Options', 'rich-reviews'); ?></strong></h4>
<input type="checkbox" name="snippet_stars" value="checked" <?php echo $options['snippet_stars'] ?> />
<label for="snippet_stars">
	<?php _e('Star Snippets - this will change the averge rating displayed in the snippet shortcode to be stars instead of numerical values.', 'rich-reviews'); ?>
</label>
<br />

<input type="color" name="star_color" value="<?php echo $options['star_color'] ?>">
<label for="star_color">
	<?php _e('Star Color - the color of the stars on reviews', 'rich-reviews'); ?>
</label>
<br />

<h4><strong><?php _e('General Display Options', 'rich-reviews'); ?></strong></h4>
<input type="checkbox" name="display_full_width" value="checked" <?php echo $options['display_full_width'] ?> />
<label for="display_full_width">
	<?php _e('Display Full width - This option will display the reviews in full width block format. Default will dsplay the reviews in blocks of three.', 'rich-reviews'); ?>
</label>
<br />

<input type="checkbox" name="show_date" value="checked" <?php echo $options['show_date'] ?> />
<label for="show_date">
	<?php _e('Display the date that the review was submitted inside the review.', 'rich-reviews'); ?>
</label>
<br />

<input type="checkbox" name="credit_permission" value="checked" <?php echo $options['credit_permission'] ?> />
<label for="credit_permission">
	<?php _e('Give Credit to Nuanced Media - this option will add a small credit line and a link to Nuanced Media\'s website to the bottom of your reviews page', 'rich-reviews'); ?>
</label>
<br />

<input type="checkbox" name="return-to-form" value="checked" <?php echo $options['return-to-form'] ?> />
<label for="return-form">
	<?php _e('Upon submission of the review form, the page will automatically scroll back to the location of the form.', 'rich-reviews'); ?>
</label>
<br />

<label for="reviews_order"><strong><?php _e('Review Display Order: ', 'rich-reviews'); ?></strong></label>
<select name="reviews_order" value="<?php echo $options['reviews_order'] ?>">
	<?php
	if ($options['reviews_order']==="ASC"){ ?><option value="ASC" selected="selected"><?php _e('Oldest First', 'rich-reviews'); ?></option><?php }else {?><option value="ASC" ><?php _e('Oldest First', 'rich-reviews'); ?></option><?php }
	if ($options['reviews_order']==="DESC"){ ?><option value="DESC" selected="selected"><?php _e('Newest First', 'rich-reviews'); ?></option><?php }else {?><option value="DESC" ><?php _e('Newest First', 'rich-reviews'); ?></option><?php }
	if ($options['reviews_order']==="random"){ ?><option value="random" selected="selected"><?php _e('Randomize', 'rich-reviews'); ?></option><?php }else {?><option value="random" ><?php _e('Randomize', 'rich-reviews'); ?></option><?php }
	?>
</select>
<br />
