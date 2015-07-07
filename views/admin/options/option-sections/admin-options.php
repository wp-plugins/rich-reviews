<?php

?>

<h3><strong><?php _e('Admin Options', 'rich-reviews'); ?></strong></h3>
	<div style="border: solid 2px black"></div>

	<h4><strong><?php _e('Approval Options', 'rich-reviews'); ?></strong></h4>

	<input type="checkbox" name="require_approval" value="checked" <?php echo $options['require_approval'] ?> />
	<label for="require_approval">
		<?php _e('Require Approval - this sends all new reviews to the pending review page. Unchecking this will automatically publish all reviews as they are submitted.', 'rich-reviews'); ?>
	</label>
	<br />

	<select name="approve_authority">
		<?php
		if ($options['approve_authority']==="manage_options"){ ?><option value="manage_options" selected="selected"><?php _e('Admin', 'rich-reviews'); ?></option><?php }else {?><option value="manage_options" ><?php _e('Admin', 'rich-reviews'); ?></option><?php }
		if ($options['approve_authority']==="moderate_comments"){ ?><option value="moderate_comments" selected="selected"><?php _e('Editor', 'rich-reviews'); ?></option><?php }else {?><option value="moderate_comments" ><?php _e('Editor', 'rich-reviews'); ?></option><?php }
		if ($options['approve_authority']==="edit_published_posts"){ ?><option value="edit_published_posts" selected="selected"><?php _e('Author', 'rich-reviews'); ?></option><?php }else {?><option value="edit_published_posts" ><?php _e('Author', 'rich-reviews'); ?></option><?php }
		if ($options['approve_authority']==="edit_posts"){ ?><option value="edit_posts" selected="selected"><?php _e('Contributor', 'rich-reviews'); ?></option><?php }else {?><option value="edit_posts" ><?php _e('Contributor', 'rich-reviews'); ?></option><?php }
		if ($options['approve_authority']==="read"){ ?><option value="read" selected="selected"><?php _e('Subscriber', 'rich-reviews'); ?></option><?php }else {?><option value="read" ><?php _e('Subscriber', 'rich-reviews'); ?></option><?php }
		?>
	</select>
	<label for="approve_authority">
		<?php _e('Authority level required to Approve Pending Posts', 'rich-reviews'); ?>
	</label>
	<br />

	<input type="checkbox" name="send-email-notifications" value="checked" <?php echo $options['send-email-notifications'] ?> />
	<label for="require_approval">
		<?php _e('Send Notification Emails - This will send an automatic email to the admin every time a new pending review is submitted.', 'rich-reviews'); ?>
	</label>
	<br />
	<br />

	<label for "admin-email">
		<?php _e('Admin Email - The email to which notifications are sent.', 'rich-reviews'); ?>
	</label>
	<input type="text" name="admin-email" style="width:100%;" value="<?php echo $options['admin-email'] ?>" />
	<br />
