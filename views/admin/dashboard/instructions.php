<?php
?>
<div class="wrap">
			</div>


			<div class="rr_admin_sidebar">
				<div class="rr_admin_sidebar_title">
					<?php echo __('Shortcode Cheat Sheet', 'rich-reviews'); ?>
				</div>
				<div class="rr_admin_sidebar_text">
					<?php echo __('Make sure you read the detailed descriptions of how these work below, in ', 'rich-reviews'); ?> <span style="font-weight: 600;"><?php echo __('Shortcode Usage', 'rich-reviews'); ?></span>!
				</div>
				<ul class="rr_admin_sidebar_list">
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_SHOW]
					</li>
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_SHOW num="9"]
					</li>
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_SHOW num="all"]
					</li>
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_SHOW category="foo"]
					</li>
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_SHOW category="all"]
					</li>
					<li class="rr_admin_sidebar_list_item" style="margin: 0px 0px 4px 	0px;">[RICH_REVIEWS_SHOW category="page" num="5"]
					</li>
					<li class="rr_admin_sidebar_list_item" style="margin: 0px 0px 4px 	0px;">[RICH_REVIEWS_SHOW category="all" num="all"]
					</li>
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_FORM]
					</li>
					<li class="rr_admin_sidebar_list_item" style="margin: 0px 0px 4px 	0px;">[RICH_REVIEWS_FORM category="foo"]
					</li>
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_SNIPPET]
					</li>
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_SNIPPET category="foo"]
					</li>
					<li class="rr_admin_sidebar_list_item">
						[RICH_REVIEWS_SNIPPET category="all"]
					</li>
				</ul>
			</div>

			<div class="rr_admin_sidebar">
				<div class="rr_admin_sidebar_title"><?php echo __('Support the developers!', 'rich-reviews'); ?> </div>
				<?php echo $supportNM ?>
			</div>
			<p><?php echo __('Thank you for using Rich Reviews by Foxy Technology and ', 'rich-reviews'); ?> <a href="http://nuancedmedia.com">Nuanced Media</a>!
			</p>
			<p>
			<?php echo __('This plugin is based around shortcodes. We think that this is the best way to go, as then YOU control where reviews, forms, and snippets are shown - pages, posts, widgets... wherever!', 'rich-reviews');
			?> </p>
			<p style="font-size: 120%">
			<?php echo __('Please take a moment to <a href="http://wordpress.org/extend/plugins/rich-reviews/">rate and/or review</a> this plugin, and tell people about it - we love hearing feedback, and depend on you to spread the word!', 'rich-reviews');
			 ?> </p>
			 <p><?php
			echo __('Some terminology so that we are all on the same page:', 'rich-reviews');?>
				<ul style="">
					<li><?php echo __('A <b>global review</b> is a review which applies or belongs to the entire Wordpress site, regardless of the current page or post. You might use global reviews if your users are submitting reviews for a business or entire website.', 'rich-reviews'); ?>
					</li>
					<li><?php echo __('A <b>per-page review</b> is a review which applies to some specific page or post. You might use per-page reviews if, for example, your Wordpress site has various products with a dedicated page or post for each one. Note that reviews users submit will <i>always</i> record the post from which they were submitted, even if you will end up using global reviews! This is to simplify things, so that we don\'t have a bunch of different, confusing shortcodes.', 'rich-reviews'); ?>
					</li>
				</ul>
			</p>
			<div class="clear"></div>
