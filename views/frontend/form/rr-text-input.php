<?php
?>

	<tr class="rr_form_row">
		<td class="rr_form_heading <?php if($require){ echo 'rr_required'; } ?>" >
			<?php echo $label; ?>
		</td>
		<td class="rr_form_input">
			<?php echo '<span class="form-err">' . $error . '</span>'; ?>
			<input class="rr_small_input" type="text" name="r<?php echo $inputId; ?>" value="<?php echo $rFieldValue ; ?>" />
		</td>
	</tr>
