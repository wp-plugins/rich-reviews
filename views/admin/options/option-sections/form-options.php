<?php

?>
	<h3><strong><?php _e('Form Options', 'rich-reviews'); ?></strong></h3>
	<div style="border: solid 2px black"></div>
	<h4>
		<?php _e('Name Field', 'rich-reviews'); ?>
	</h4>
	<label for="form-name-label">
		<?php _e('Form Label: ', 'rich-reviews'); ?>
	</label>
	<input type="text" name="form-name-label" value="<?php echo $options['form-name-label']; ?>" />
	<br>
	<label for="form-name-display">
		<?php _e('Display Field: ', 'rich-reviews'); ?>
	</label>
	<input type="checkbox" name="form-name-display" value="checked" <?php echo $options['form-name-display'] ?> /><br/>
	<label for="form-name-require">
		<?php _e('Require Field: ', 'rich-reviews'); ?>
	</label>
	<input type="checkbox" name="form-name-require" value="checked" <?php echo $options['form-name-require'] ?> />
	<br>


	<h4><?php _e('Email Field', 'rich-reviews'); ?></h4>
	<label for="email-label">
		<?php _e('Form Label: ', 'rich-reviews'); ?>
	</label>
	<input type="text" name="form-email-label" value="<?php echo $options['form-email-label']; ?>" />
	<br>
	<label for="form-email-display">
		<?php _e('Display Field: ', 'rich-reviews'); ?>
	</label>
	<input type="checkbox" name="form-email-display" value="checked" <?php echo $options['form-email-display'] ?> />
	<br/>
	<label for="form-email-require">
		<?php _e('Require Field: ', 'rich-reviews'); ?>
	</label>
	<input type="checkbox" name="form-email-require" value="checked" <?php echo $options['form-email-require'] ?> />
	<br>
	<h4><?php _e('Title Field', 'rich-reviews'); ?></h4>
	<label for="form-title-label">
		<?php _e('Form Label: ', 'rich-reviews'); ?>
	</label>
	<input type="text" name="form-title-label" value="<?php echo $options['form-title-label']; ?>" />
	<br>
	<label for="form-title-display">
		<?php _e('Display Field: ', 'rich-reviews'); ?>
	</label>
	<input type="checkbox" name="form-title-display" value="checked" <?php echo $options['form-title-display'] ?> />
	<br/>
	<label for="form-title-require">
		<?php _e('Require Field: ', 'rich-reviews'); ?>
	</label>
	<input type="checkbox" name="form-title-require" value="checked" <?php echo $options['form-title-require'] ?> />
	<br>
	<h4><?php _e('Rating Field', 'rich-reviews'); ?></h4>
	<label for="form-rating-label">
		<?php _e('Form Label: ', 'rich-reviews'); ?>
	</label>
	<input type="text" name="form-rating-label" value="<?php echo $options['form-rating-label']; ?>" />
	<br>
	<h4><?php _e('Content Field', 'rich-reviews'); ?></h4>
	<label for="form-content-label">
		<?php _e('Form Label: ', 'rich-reviews'); ?>
	</label>
	<input type="text" name="form-content-label" value="<?php echo $options['form-content-label']; ?>" />
	<br>
	<label for="form-content-display">
		<?php _e('Display Field: ', 'rich-reviews'); ?>
	</label>
	<input type="checkbox" name="form-content-display" value="checked" <?php echo $options['form-content-display'] ?> />
	<br/>
	<label for="form-content-require">
		<?php _e('Require Field: ', 'rich-reviews'); ?>
	</label>
	<input type="checkbox" name="form-content-require" value="checked" <?php echo $options['form-content-require'] ?> />
	<br>

	<h4><?php _e('Submit Button', 'rich-reviews'); ?></h4>
	<label for="form-submit-text">
		<?php _e('Submit Text: ', 'rich-reviews'); ?>
	</label>
	<input type="text" name="form-submit-text" value="<?php echo $options['form-submit-text']; ?>" />
	<br>
	<br>
