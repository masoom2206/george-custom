<?php
	global $user, $base_url;
	module_load_include('inc', 'listing_pdf', 'includes/listing_brochures');
	$brochure = 'print and go';
	$template_details = template_details($brochure);
	magnify_image();
	$result = get_mcpdf_nid();
	$path = current_path();
	if(user_has_role(9, $user)){
		$alisting_url = "/office-listings-agent/".$var_name['listing_uid'];
	}else{
		$alisting_url = "/my-listings";
	}
?>
<div class="manage-listing-back">	<a href="/manage-listing/<?php print $var_name['listing_nid']?>">		<img src="/sites/all/modules/custom/listing_marketing_system/images/back-curved-arrow.png">	</a></div>
<div class="manage-listing-photos">
	<div class="listing-photos-header">
		<div class="photos-title">Flyers</div>
		<div class="instruction_line">Step 1 | Select Your Template and Click the Pencil Icon to Edit</div>
		<div class="photos-address document-address"><?php print $var_name['listing_address']; ?> <a href="/manage-listing/<?php print $var_name['listing_nid']?>">[return to Listing Tools]</a> <a href="<?php print $alisting_url; ?>">[return to Active Listings]</a></div>		
	</div>
	<div class="listing-pdf">

	<?php
	$output='';
	$x = 1;
	foreach($template_details as $key=>$value) {
		foreach($value as $nid) {
			$destination = drupal_get_destination();
			$path = current_path();
			$node_load = node_load($nid);
			$sides = isset($node_load->field_template_sides['und']['0']['value']) ? $node_load->field_template_sides['und']['0']['value']: '';
			
			$no_of_photos = isset($node_load->field_number_of_images_on_pdf['und']['0']['value']) ? $node_load->field_number_of_images_on_pdf['und']['0']['value']: '';
			
			$results = get_mcpdf_nid('' , $node_load->nid);
			
				if( !empty($results) ){
						$mcpdf_node=node_load($results);
						if(isset($mcpdf_node->field_pdf_preview_image['und']) ){
							$config = array(
								"style_name" => "large",
								"path" => $mcpdf_node->field_pdf_preview_image['und'][0]['uri'],
								"height" => NULL,
								"width" => NULL,
							);
							$image= theme_image_style($config);
							$photo_url = file_create_url($mcpdf_node->field_pdf_preview_image['und'][0]['uri']);
						}
						else{
							$image="no image found";
							$photo_url = '';
						}
				}
				else {
					if(isset($node_load->field_template_thumbnail['und']) ){
						$config = array(
							"style_name" => "large",
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
			$output.='<div class="brochure_wrapper">
			<div class="brochure_image">';
			$output.= $image;
			$output.='</div> <!--/brochure_image-- -->
				<div class="node_details"> <!--node_details-- -->
				<div class="node-title">
					<span>'.$node_load->title.'</span>
				</div>
				<div class="node-side_photos">
					<span>Sides: '.$sides.'</span>
					<span>  Photos: '.$no_of_photos.'</span>
				</div>
				<div class="design">
					<span>Design : '.$node_load->nid.'</span>
				</div>
				
				<div class="brochure_options">
				<span class="edit_brochure"><a href="'.$base_url.'/generate-pdf/'.$var_name['listing_nid'].'/'.$node_load->nid.'" alt="Edit" title="Edit"><img src="/sites/all/modules/custom/listing_marketing_system/images/pencil-edit.png"/></a></span>';
		
		
			if($results !='') {
				$output.='<span class="download_brochure"><a href="'.$base_url.'/download_pdf/'.$var_name['listing_nid'].'/'.$node_load->nid.'?destination='.$path.'" alt="Download" title="Download"><img src="/sites/all/modules/custom/listing_marketing_system/images/download-arrow.png"/></a></span>
			
				<span class="delete_brochure"><a href="/node/'.$results.'/delete?design_id='.$node_load->nid.'&destpath='.$path.'" alt="Delete" title="Delete"><img src="/sites/all/modules/custom/listing_marketing_system/images/trashcan.png"/></a></span>';
			}
			$output.='</div><!--/brochure_options -- -->';
			$output.='<ul id="auto-loop" class="gallery'.$x.'"><div class="magnify">';
			if( !empty($results) ){
				$mcpdf_node=node_load($results);
				if(isset($mcpdf_node->field_pdf_preview_image['und']) ){
					foreach($mcpdf_node->field_pdf_preview_image['und'] as $value){
						$photo_url = file_create_url($value['uri']);
						$output.='<li><a href="'.$photo_url.'" rel="lightbox[gallery'.$x.']['.$mcpdf_node->title.']" alt="Zoom In" title="Zoom In">
							<img src="/sites/all/modules/custom/listing_marketing_system/images/magnify.png"/></a></li>';
					}	
				}
			}
			else {
				if(isset($node_load->field_template_thumbnail['und']) ){
					foreach($node_load->field_template_thumbnail['und'] as $value){
						$photo_url = file_create_url($value['uri']);
						$output.='<li><a href="'.$photo_url.'" rel="lightbox[gallery'.$x.']['.$node_load->title.']" alt="Zoom In" title="Zoom In">
						<img src="/sites/all/modules/custom/listing_marketing_system/images/magnify.png"/></a></li>';
					}
				}
			}
			$output.='</div></ul>';			
			if($results !='') { 
				if( $var_name['web_page_active'] == 1){
					$brochure_disabled = '';
				}
				else{
					$brochure_disabled = '';
				}
				if( $var_name['pdf_brochures'] == $results){
					$brochure_checked = 'checked';
				}
				else{
					$brochure_checked = '';
				}
				if( $var_name['shared_pdf_brochures'] == $node_load->nid){
					$shared_check = 'checked';
				}
				else{
					$shared_check = '';
				}
				$output.='<div class="online_marketing">';
				$output.= '<input id="mcpdf-nid" type="radio" '.$brochure_checked.' name="online_marketing" value ='.$results.' listing_nid='.$var_name['listing_nid'].' '.$brochure_disabled.'>  Add link on Web Page<br>';
				
				$output.= '<input id="mcpdf-nid" type="radio" '.$shared_check.' name="online_marketing_shared_listing" value ='.$node_load->nid.' listing_nid='.$var_name['listing_nid'].' '.$brochure_disabled.'> Use for Shared Listing<br>';
				$output.='</div><!--------/online_marketing---- -->';
			}
			$output.='</div> <!--/node_details-- --></div> <!--/brochure_wrapper-- -->';
		$x++;
		}
	}
	print $output;
	?>
	</div>
	<!-- Submit and update Listing Brochures -->
	<div class="update-listing-brochures"><a href="#" id="update-listing-brochures-id">Submit</a></div>
	<!-- update-listing-brochures -->
</div>
<div>
	<div id="image_popup">
		<span class="button b-close"><span></span></span>
		<div class="image_area"></div>
	</div>
</div> 
