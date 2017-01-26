<?php
//print "<pre>";print_r($var_name);exit;
$form = drupal_get_form('cbone_listing_email_form');
?>
<div class="manage-listing-back"><a href="/manage-listing/<?php print $var_name['listing_nid']?>" id="listing-modal-back"><img src="/sites/all/modules/custom/listing_marketing_system/images/back-curved-arrow.png"></a></div>
<div class="manage-listing-photos">
	<div class="listing-photos-header">
		<div class="photos-title">Listing Website</div>
		<div class="photos-address"><?php print $var_name['listing_address']; ?> <a href="/manage-listing/<?php print $var_name['listing_nid']?>">[return to Listing Tools]</a> <a href="/my-listings">[return to Active Listings]</a></div>		
	</div>
</div>
<div class="listing-email-body">
	<?php print drupal_render($form); ?>
</div>
