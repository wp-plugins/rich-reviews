<?php

?>

<form id="rr-admin-options-form" action="" method="post">
	<input type="hidden" name="update" value="rr-update-options">
		<div style="float:left;width:30%">
			<?php include $path . 'views/admin/options/option-sections/form-options.php'; ?>
		</div>
		<div style="float:right;width:65%">
			<?php include $path . 'views/admin/options/option-sections/display-options.php'; ?>
			<?php include $path . 'views/admin/options/option-sections/admin-options.php'; ?>
		</div>
	<div class="clear"></div>
	<br/>
	<input type="submit" class="button" value="<?php _e('Save Options', 'rich-reviews'); ?>">
</form>
