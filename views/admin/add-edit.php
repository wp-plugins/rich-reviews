<?php

?>

<br/>
<div class="clear"></div>
<form method="post" action="">
	<input type="hidden" name="rr_save_review" value="admin-save-review" />

	<table class="form_table">
		<tr class="rr_form_row">
			<td class="rr_form_heading">Date</td>
			<td class="rr_form_input"><input type="text" name="date_time" value="<?php echo $review['date_time']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading <?php if($options['form-name-require']) { echo 'rr_required'; } ?>"><?php echo $options['form-name-label']; ?></td>
			<td class="rr_form_input"><?php echo $name_err; ?><input class="rr_small_input" type="text" name="reviewer_name" value="<?php echo $review['reviewer_name']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading <?php if($options['form-email-require']) { echo 'rr_required'; } ?>"><?php echo $options['form-email-label']; ?></td>
			<td class="rr_form_input"><?php echo $email_err; ?><input class="rr_small_input" type="text" name="reviewer_email" value="<?php echo $review['reviewer_email']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading <?php if($options['form-title-require']) { echo 'rr_required'; } ?>"><?php echo $options['form-title-label']; ?></td>
			<td class="rr_form_input"><?php echo $title_err; ?><input class="rr_small_input" type="text" name="review_title" value="<?php echo $review['review_title']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required">Rating</td>
			<td class="rr_form_input"><?php echo $rating_err; ?><input type="number" name="review_rating" value="<?php echo $review['review_rating']; ?>" min="1" max="5"/></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading <?php if($options['form-content-require']) { echo 'rr_required'; } ?>"><?php echo $options['form-content-label']; ?></td>
			<td class="rr_form_input"><?php echo $text_err; ?><textarea class="rr_large_input" name="review_text" rows="10"><?php echo $review['review_text']; ?></textarea></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading">Review Parent Post Id</td>
			<td class="rr_form_input"><input type="number" name="review_parent" value="<?php echo $review['post_id']; ?>"/></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required">Review Category</td>
			<td class="rr_form_input"><input class="rr_small_input" type="text" name="review_category" value="<?php if(isset($review['review_category'])) { echo $review['review_category']; } else { echo "none"; } ?>" /></td>
		</tr>
	</table>
	<div class="clear"></div>
	<td class="rr_form_input"><input name="submitButton" type="submit" value="Submit Review" /></td>
</form>
