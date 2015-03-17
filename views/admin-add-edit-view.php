<?php

class RRAdminAddEdit {

	var $core;

	var $rr_id;
	var $date_time;
	var $reviewer_name;
	var $reviewer_email;
	var $review_title;
	var $review_rating;
	var $review_text;
	var $review_status;
	var $reviewer_ip;
	var $postId;
	var $review_category;

	function __construct($core) {
		$this->core = $core;
		if (isset($_GET['rr_id'])) {
			$this->rr_id = $_GET['rr_id'];
		}
		$this->date_time = $rDateTime = date('Y-m-d H:i:s');
		$this->reviewer_ip = $_SERVER['REMOTE_ADDR'];
		//dump($this->rr_id, 'ID');
		$data = $this->check_add_update();
		$this->display_form($data);
	}

	function check_add_update() {
		$output = '';
		$data = array();
		$form_name = '';
		$form_title = '';
		$form_rating = '';
		$form_text = '';
		if (isset($_POST['rr_save_review'])) {
			if ($_POST['rr_save_review'] == 'admin-save-review') {
				$this->date_time     = $this->core->fp_sanitize($_POST['date_time']);
				$this->reviewer_name     = $this->core->fp_sanitize($_POST['reviewer_name']);
				$this->reviewer_email    = $this->core->fp_sanitize($_POST['reviewer_email']);
				$this->review_title    = $this->core->fp_sanitize($_POST['review_title']);
				$this->review_rating   = $this->core->fp_sanitize($_POST['review_rating']);
				$this->review_text     = $this->core->fp_sanitize($_POST['review_text']);
				$this->review_status   = 1;
				$this->postId   = $this->core->fp_sanitize($_POST['review_parent']);
				$this->review_category = $this->core->fp_sanitize($_POST['review_category']);



				$newdata = array(
						'date_time'       => $this->date_time,
						'reviewer_name'   => $this->reviewer_name,
						'reviewer_email'  => $this->reviewer_email,
						'review_title'    => $this->review_title,
						'review_rating'   => intval($this->review_rating),
						'review_text'     => $this->review_text,
						'review_status'   => $this->review_status,
						'reviewer_ip'     => $this->reviewer_ip,
						'post_id'		  => $this->postId,
						'review_category' => $this->review_category
				);
				//dump($newdata, 'NEW DATA');
				$validData = true;
				if ($this->reviewer_name  == '') {
					$form_name = '<span class="err">You must include your name.</span><br>';
					$validData = false;
				}
				if ($this->review_title == '') {
					$form_title = '<span class="err">You must include a title for your review.</span><br>';
					$validData = false;
				}
				if ($this->review_text== '') {
					$form_text = '<span class="err">You must write some text in your review.</span><br>';
					$validData = false;
				}
				if ($this->review_rating == 0) {
					$form_rating = '<span class="err">Please give a rating between 1 and 5 stars.</span><br>';
					$validData = false;
				}
				if ($this->reviewer_email != '') {
					$firstAtPos = strpos($this->reviewer_email,'@');
					$periodPos  = strpos($this->reviewer_email,'.');
					$lastAtPos  = strrpos($this->reviewer_email,'@');
					if (($firstAtPos === false) || ($firstAtPos != $lastAtPos) || ($periodPos === false)) {
						$output .= '<span class="err">You must provide a valid email address.';
						$validData = false;
					}
				}
				if ($validData) {
					if ((strlen($this->reviewer_name) > 100)) {
						$output .= 'The name you entered was too long, and has been shortened.<br />';
					}
					if ((strlen($this->review_title) > 150)) {
						$output .= 'The review title you entered was too long, and has been shortened.<br />';
					}
					if ((strlen($this->reviewer_email) > 100)) {
						$output .= 'The email you entered was too long, and has been shortened.<br />';
					}
					$this->core->db->save($newdata, $this->rr_id);
					$output .= '<span class="message">The review has been saved.</span><br />';

				}
			}
		}
		$data['output'] = $output;
		$data['name_err'] = $form_name;
		$data['title_err'] = $form_title;
		$data['text_err'] = $form_text;
		$data['rating_err'] = $form_rating;

		return $data;
	}

	function display_form($data = NULL, $review = NULL) {
		if ($this->rr_id && $review == NULL) {
			$review =(array) $this->core->db->get($this->rr_id, TRUE);
			//dump($review, 'REVIEW');
			$this->display_form($data, $review);
			return;
		}
		if (is_null($review)) {
			$review = array(
				'date_time'       => NULL,
				'reviewer_name'   => NULL,
				'reviewer_email'  => NULL,
				'review_title'    => NULL,
				'review_rating'   => NULL,
				'review_text'     => NULL,
				'review_status'   => NULL,
				'reviewer_ip'     => NULL,
				'post_id'		  => NULL,
				'review_category' => NULL,
			);
		}
		foreach ($review as $key=>$value) {
			$review[$key] = $this->core->nice_output($value);
		}
		if( $data != NULL) {
			extract($data);
		} else {
			$name_err = '';
			$title_err = '';
			$text_err = '';
			$rating_err = '';
			$output = '';
		}
echo $output;
		?>
		<br/>
<div class="clear"></div>
<form method="post" action="">
	<input type="hidden" name="rr_save_review" value="admin-save-review" />

	<table class="form_table">
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required">Date</td>
			<td class="rr_form_input"><input type="text" name="date_time" value="<?php echo $review['date_time']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required">Name</td>
			<td class="rr_form_input"><?php echo $name_err; ?><input class="rr_small_input" type="text" name="reviewer_name" value="<?php echo $review['reviewer_name']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading">Email</td>
			<td class="rr_form_input"><input class="rr_small_input" type="text" name="reviewer_email" value="<?php echo $review['reviewer_email']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required"><?php echo ucwords(strtolower($this->core->rr_options['review_title'])); ?></td>
			<td class="rr_form_input"><?php echo $title_err; ?><input class="rr_small_input" type="text" name="review_title" value="<?php echo $review['review_title']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required">Rating</td>
			<td class="rr_form_input"><?php echo $rating_err; ?><input type="number" name="review_rating" value="<?php echo $review['review_rating']; ?>" min="1" max="5"/></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required">Review Content</td>
			<td class="rr_form_input"><?php echo $text_err; ?><textarea class="rr_large_input" name="review_text" rows="10"><?php echo $review['review_text']; ?></textarea></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required">Review Parent Post Id</td>
			<td class="rr_form_input"><input type="number" name="review_parent" value="<?php echo $review['post_id']; ?>"/></td>
		</tr>
		<tr class="rr_form_row">
			<td class="rr_form_heading rr_required">Review Category</td>
			<td class="rr_form_input"><input class="rr_small_input" type="text" name="review_category" value="<?php echo $review['review_category']; ?>" /></td>
		</tr>
		<tr class="rr_form_row">
			<td></td>
			<td class="rr_form_input"><input name="submitButton" type="submit" value="Submit Review" /></td>
		</tr>
	</table>
</form>
		<?php
	}
}
