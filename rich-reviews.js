jQuery(function(){
	jQuery('.rr_review_text').each(function(event){
		var max_length = 150;
		if(jQuery(this).html().length > max_length){
			var short_content 	= jQuery(this).html().substr(0,max_length);
			var long_content	= jQuery(this).html().substr(max_length);
			jQuery(this).html(short_content+'<span class="ellipses">... </span><a href="#" class="read_more"><br />Read More</a>'+'<span class="more_text" style="display:none;">'+long_content+' <br /><a href="#" class="show_less">Less</a></span>');
			jQuery(this).find('a.read_more').click(function(event){ 
				event.preventDefault();
				jQuery(this).hide();
				jQuery(this).parents('.rr_review_text').find('span.ellipses').hide();
				jQuery(this).parents('.rr_review_text').find('.more_text').show();
				jQuery(this).parents('.rr_review_text').find('a.show_less').show();
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
});
function overStar(starID){
	var starNo = starID.charAt(1);
	for(var i=1; i<=5; i++) {
		jQuery("#s"+i).css("color","#666666");
		if(i<=starNo) {
			jQuery('#s'+i).html("&#9733"); // black star
		}
		if(i>starNo) {
			jQuery('#s'+i).html("&#9734"); // white star
		}
	}
}

function outStar(starID){
	var starNo = starID.charAt(1);
	var rating = jQuery('#rRating').val();
	for(var i=1; i<=5; i++){
		if(i<=rating){
			jQuery('#s'+i).html("&#9733"); // black star
			jQuery('#s'+i).css("color","#FFAF00");
		}
		if(i>rating) {
			jQuery('#s'+i).html("&#9734"); // white star
		}
	}
}

function starSelection(starID){
	var starNo = starID.charAt(1);
	jQuery('#rRating').val(starNo);
	for(var i=1; i<=5; i++){
		if(i<=starNo) {
			jQuery('#s'+i).html("&#9733"); // black star
			jQuery('#s'+i).css("color","#FFAF00");
		}
		if(i>starNo) {
			jQuery('#s'+i).html("&#9734"); // white star
		}
	}
}