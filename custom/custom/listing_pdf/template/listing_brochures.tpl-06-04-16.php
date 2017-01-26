<?php
	global $user, $base_url;
	module_load_include('inc', 'listing_pdf', 'includes/listing_brochures');
	$brochure = 'pro brochure';
	$template_details = template_details($brochure);
	$template_id = array();
	if($var_name['product_id'] == 1 ){
		$template_id[386] = $template_details[386];
	}
	else{
		$template_id = $template_details;
	}
	magnify_image();
	$result = get_mcpdf_nid();?>
<div class="manage-listing-back">	
	<a href="/manage-listing/<?php print $var_name['listing_nid']?>"><img src="/sites/all/modules/custom/listing_marketing_system/images/back-curved-arrow.png"></a>
</div>
<div class="manage-listing-photos">
	<div class="listing-photos-header">
		<div class="photos-title">Pro Brochures</div>
		<div class="photos-address document-address"><?php print $var_name['listing_address']; ?> <a href="/manage-listing/<?php print $var_name['listing_nid']?>">[return to Listing Tools]</a> <a href="/my-listings">[return to Active Listings]</a></div>		
	</div>
	<div class="listing-pdf">
	<?php
	$output='';
	$x = 1;
	foreach($template_details as $key=>$value) {
		
		$term = taxonomy_term_load($key);
		if(!empty($term) && !empty($value)){
			$gp_name = $term->name;
			if($key == 386){
				$product_img = '<img src="/sites/all/modules/custom/listing_pdf/images/mc-premier-small.png"/>';
			}
			if($key == 387){
				$product_img = '<img src="/sites/all/modules/custom/listing_pdf/images/mc-platinum-small.png""/>';
			}
			if($key == 388){
				$product_img = '<img src="/sites/all/modules/custom/listing_pdf/images/mc-platinum-plus-small.png"/>';
			}
		}
		else if(empty($term) && !empty($value)){
			$gp_name = 'Other';
			$product_img='';
		}
		else{
			$gp_name = '';
			$product_img = '';
		}
		$output.='<div class="product_group"><!--product_group-- -->
		<div class="pro_gp"><span>'.$product_img.'</span> '.$gp_name.'</div>';
		foreach($value as $nid) {
			$destination = drupal_get_destination();
			$path = current_path();
			$node_load = node_load($nid);
			
			$sides = isset($node_load->field_template_sides['und']['0']['value']) ? $node_load->field_template_sides['und']['0']['value']: '';
			$no_of_photos = isset($node_load->field_number_of_images_on_pdf['und']['0']['value']) ? $node_load->field_number_of_images_on_pdf['und']['0']['value']: '';
			
			$results = listin_brochures_options($node_load->nid);
			if( !empty($node_load->field_template_thumbnail) && isset($node_load->field_template_thumbnail) ){
				$config = array(
					"style_name" => "large",
					"path" => $node_load->field_template_thumbnail['und'][0]['uri'],
					"height" => NULL,
					"width" => NULL,
				);
				$image= theme_image_style($config);
			}
			else{
				$image="no image found";
			}
				if( !empty($results) ){
							$mcpdf_node=node_load($results);
							$config = array(
								"style_name" => "listing_brochures_images",
								"path" => $mcpdf_node->field_pdf_preview_image['und'][0]['uri'],
								"height" => NULL,
								"width" => NULL,
							);
							$image= theme_image_style($config);
							$photo_url = file_create_url($mcpdf_node->field_pdf_preview_image['und'][0]['uri']);
				}
				else {
					if( !empty($node_load->field_template_thumbnail) && isset($node_load->field_template_thumbnail) ){
						$config = array(
							"style_name" => "listing_brochures_images",
							"path" => $node_load->field_template_thumbnail['und'][0]['uri'],
							"height" => NULL,
							"width" => NULL,
						);
						$image= theme_image_style($config);
						$photo_url = file_create_url($node_load->field_template_thumbnail['und'][0]['uri']);
					}
					else{
						$image="no image found";
						$photo_url = '';
					}
				}
				$output.='<div class="brochure_wrapper"><div class="brochure_image">';
				$output.= $image;
				$output.='</div> <!--/brochure_image-- -->
					<div class="node_details"> <!--node_details-- -->
						<div class="node-title"><span>'.$node_load->title.'</span></div>';
				
				if($results !='' && !empty($results) && node_load($results)->uid==$user->uid) {
					$output.='<div class="send-pdf"><a href="">
						<img src="/sites/all/modules/custom/listing_marketing_system/images/mail.png"/>
					</a></div>';
				}
				$output.='<div class="node-side_photos">
						<span>Sides: '.$sides.'</span>
						<span>  Photos: '.$no_of_photos.'</span>
					</div>
					<div class="design">
						<span>Design : '.$node_load->nid.'</span>
					</div>';
				
				if($results !='' && !empty($results) && !empty($var_name['order_id'])) {
					$output.='<div class="oder-id">Order: '.$var_name['order_id'].'-'.$results.'</div>';
				}
				$output.='<div class="brochure_options">
				<span class="edit_brochure"><a href="'.$base_url.'/generate-pdf/'.$var_name['listing_nid'].'/'.$node_load->nid.'"><img src="/sites/all/modules/custom/listing_marketing_system/images/pencil-edit.png"/></a></span>';
			
				if($results !='' && !empty($results) && node_load($results)->uid==$user->uid) {
					$output.='<span class="download_brochure"><a href="'.$base_url.'/download_pdf/'.$var_name['listing_nid'].'/'.$node_load->nid.'?destination='.$path.'"><img src="/sites/all/modules/custom/listing_marketing_system/images/download-arrow.png"/></a></span>
				
					<span class="delete_brochure"><a href="/node/'.$results.'/delete"><img src="/sites/all/modules/custom/listing_marketing_system/images/trashcan.png"/></a></span>';
				}
				$output.='</div><!--/brochure_options -- -->';
				
				
				$output.='<ul id="auto-loop" class="gallery'.$x.'"><div class="magnify">';
				if( !empty($results) ){
					$mcpdf_node=node_load($results);
					foreach($mcpdf_node->field_pdf_preview_image['und'] as $value){
						$photo_url = file_create_url($value['uri']);
						$output.='<li><a href="'.$photo_url.'" rel="lightbox[gallery'.$x.']['.$mcpdf_node->title.']" alt="Zoom In" title="Zoom In">
							<img src="/sites/all/modules/custom/listing_marketing_system/images/magnify.png"/></a></li>';
					}	
				}
				else {
					if( !empty($node_load->field_template_thumbnail) && isset($node_load->field_template_thumbnail) ){
						foreach($node_load->field_template_thumbnail['und'] as $value){
							$photo_url = file_create_url($value['uri']);
							$output.='<li><a href="'.$photo_url.'" rel="lightbox[gallery'.$x.']['.$node_load->title.']" alt="Zoom In" title="Zoom In">
							<img src="/sites/all/modules/custom/listing_marketing_system/images/magnify.png"/></a></li>';
						}
					}
				}
				$output.='</div></ul></div> <!--/node_details-- -->';
				$output.='</div> <!--/brochure_wrapper-- -->';
			$x++;
		}
		$output.='</div><!--/product_group-- -->';
	}
	print $output;
	?>
	</div>
</div>
<div>
	<div id="image_popup">
		<span class="button b-close"><span></span></span>
		<div class="image_area"></div>
	</div>
</div>
