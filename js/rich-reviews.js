jQuery(function(){
	// if(jQuery('.successful').is(':visible')) {
	// 	offset = jQuery('.successful').offset();
	// 	jQuery('html, body').animate({
	// 		scrollTop: (offset.top - 400)
	// 	});
	// } else {
	// 	if(jQuery('.form-err').is(':visible')) {
	// 		offset = jQuery('.form-err').offset();
	// 		jQuery('html, body').animate({
	// 			scrollTop: (offset.top - 200)
	// 		});
	// 	}
	// }
	jQuery('.rr_review_text').each(function(event){
		var max_length = 150;
		if(jQuery(this).html().length > max_length){
			var short_content 	= jQuery(this).html().substr(0,max_length);
			var long_content	= jQuery(this).html().substr(max_length);
			jQuery(this).html(short_content+'<span class="ellipses">... </span><a href="#" class="read_more"><br />Read More</a>'+'<span class="more_text" style="display:none;">'+long_content+' <br /><a href="#" class="show_less" style="display:none;">Less</a></span>');
			jQuery(this).find('a.read_more').click(function(event){
				event.preventDefault();
				jQuery(this).hide();
				jQuery(this).parents('.rr_review_text').find('span.ellipses').hide();
				jQuery(this).parents('.rr_review_text').find('.more_text').show();
				jQuery(this).parents('.rr_review_text').find('a.show_less').show();
				console.log("culprit");
			});
			jQuery(this).find('a.show_less').click(function(event){
				event.preventDefault();
				jQuery(this).hide();
				jQuery(this).parents('.rr_review_text').find('.ellipses').show();
				jQuery(this).parents('.rr_review_text').find('.more_text').hide();
				jQuery(this).parents('.rr_review_text').find('a.read_more').show();
			});
		}
	});

	jQuery('.rr_star').hover(function() {
		renderStarRating(parseInt(jQuery(this).attr('id').charAt(8)));
	}, function() {
		renderStarRating(parseInt(jQuery('#rRating').val()));
	});

	jQuery('.rr_star').click(function() {
		jQuery('#rRating').val(jQuery(this).attr('id').charAt(8));
	});

	// jQuery('#submitReview').click(function() {
	// 	window.setInterval(scrollToForm(), 3000);
	// });
	// jQuery('#submitReview').click(function() {
	// 	formSubmit = true;
	// 	console.log("formSubmit");
	// });
});

function renderStarRating(rating) {
	for (var i=1; i<=5; i++) {
		jQuery('#rr_star_'+i).removeClass('glyphicon-star');
		jQuery('#rr_star_'+i).removeClass('glyphicon-star-empty');
		if (i<=rating) {
			jQuery('#rr_star_'+i).addClass('glyphicon-star');
		} else {
			jQuery('#rr_star_'+i).addClass('glyphicon-star-empty');
		}
	}
}

// function scrollToForm() {
// 	console.log("kjndkjn");
// }
