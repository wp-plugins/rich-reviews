<?php ?>

<div class="nm-support-box">
	<form action="" method="post" class="credit-option">
	<input type="hidden" name="update_permission" value="rr-update-support" />
	    <div class="nm-support-staff-checkbox">
			<input type="checkbox" name="credit_permission_option" value="checked"' .  $permission_val . ' />
	    </div>
	    <div class="nm-support-staff-label">
			<label for="credit_permission_option"><?php echo  __(' We thank you for choosing to use our plugin! We would appreciate it if you allowed us to put our name on the plugin we work so hard to build. If you would like to support us, please check this box and change your permission settings.', 'rich-reviews'); ?></label>
		</div>
		<input type="submit" value="<?php echo  __('Change Permission Setting', 'rich-reviews'); ?>" form_id="credit_permission_option" class="nm-support-staff-submit button" />
	</form>
</div>
