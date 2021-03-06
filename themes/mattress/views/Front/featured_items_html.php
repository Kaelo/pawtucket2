<?php
/** ---------------------------------------------------------------------
 * themes/mf/Front/featured_items.php : Front page of site 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * @package CollectiveAccess
 * @subpackage Core
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */

?>

<div id="featuredSlideshow">

	<div class='blockTitle'>Featured Items</div>
	<div class='blockFeatured scrollBlock' >
		<div class='scrollingDiv' > 
<?php
		$t_set = new ca_sets();
		$va_featured_set = $t_set->load(array('set_code' => $this->request->config->get('featured_set')));
		
		if ($va_featured_set) {
			$va_set_items = $t_set->getItems();
		
			$va_set_object_ids = array();
			foreach ($va_set_items as $va_set_key => $va_set_item) {
				foreach ($va_set_item as $va_set_item_key => $va_set_item_info) {
					$va_set_object_ids[] = $va_set_item_info['row_id'];
				}
			}
			$qr_res = caMakeSearchResult('ca_objects', $va_set_object_ids);
			while ($qr_res->nextHit()) {
				$va_image_width = $qr_res->get('ca_object_representations.media.mediumlarge.width');

				print "<div class='featuredObject' style='width:{$va_image_width}px;'>";
			
				print "<div class='featuredObjectImg'>";
				print $qr_res->getWithTemplate('<l>^ca_object_representations.media.mediumlarge</l>');
				print "</div>";
			
				print "<div class='artwork'>";
				if($qr_res->get('ca_objects.nonpreferred_labels.type_id') == '515') {
					print $qr_res->getWithTemplate('<l>^ca_objects.nonpreferred_labels</l>');
				} else {
					print $qr_res->getWithTemplate('<l>^ca_objects.preferred_labels</l>');
				}
				if ($qr_res->get('ca_objects.date.dates_value') && ($qr_res->get('ca_objects.type_id') != 28)) {
					print ", ".$qr_res->get('ca_objects.date.dates_value');
				}
				if ($qr_res->get('ca_objects.idno')) {
					print " (".$qr_res->get('ca_objects.idno').")";
				}
				print "</div>";
			
				print "<div class='artist'>";
				print $qr_res->get('ca_entities.preferred_labels', array('restrictToRelationshipTypes' => 'artist'));
				print "</div>";
			
				print "</div>";
			}
		}
?>
		</div><!-- scrollingdiv -->
	</div><!-- block Featured -->
	
	<div class='clearfix'></div>
</div>