<?php
	$t_item = $this->getVar('item');
	#$t_item->dump();
?>
<div id='detail' class='occurrences'>
	<div id='pageTitle'>
		<?php print ucwords($t_item->get('ca_occurrences.type_id', array('convertCodesToDisplayText' => true))); ?>
	</div>
	<div id='contentArea'>
		<div id='detailHeader'>

			<h2>
				{{{<unit>^ca_occurrences.preferred_labels.name</unit>}}}
				<!--{{{<unit delimiter=""><ifdef code="ca_entities.preferred_labels"><span class='artist'> / ^ca_entities.preferred_labels</span></ifdef></unit>}}}-->
			</h2>
<?php			
			print "<div class='detailSubtitle'>".$t_item->get('ca_occurrences.event_dates', array('delimiter' => '<br/>'))."</div>"; 
?>
		</div>
		<div id='mediaArea'>
<?php		

		$va_collections = $t_item->get('ca_collections', array('returnAsArray' => true, 'checkAccess' => $va_access_values));
		if (sizeof($va_collections) > 0) {
			print "<div class='mediaThumbs scrollBlock'>";
					print "<div style='width:70000px'>";
					$vn_i = 0;
					foreach ($va_collections as $collection_id => $va_collection) {
					
						$va_collection_id = $va_collection['collection_id'];
						$t_collection = new ca_collections($va_collection_id);
						
						$va_related_objects = $t_collection->get('ca_objects.object_id', array('returnAsArray' => true));
						$va_object_reps = caGetPrimaryRepresentationsForIDs($va_related_objects, array('versions' => array('widepreview'), 'return' => array('tags')));			
						
						$va_artwork_title = $t_collection->get('ca_collections.preferred_labels');
						if ($t_collection->get('ca_collections.date.dc_dates_types') == 188) {
							$va_artwork_date = ", ".$t_collection->get('ca_collections.date.dates_value');
						}
						$va_artwork_materials = '<div class="materials">'.$t_collection->get('ca_collections.mat_tech_display').'</div>';
						$va_artwork_display = $va_artwork_title.$va_artwork_date."<br/>".$va_artwork_materials;
						
						
						foreach ($va_object_reps as $object_key => $va_artwork_rep) {	
							if ($vn_i == 0){print "<div class='imageSet'>";}
							print "<div class='rep' onmouseover='$(\".title{$object_key}\").show();' onmouseout='$(\".title{$object_key}\").hide();'>";
							print caNavLink($this->request, "<div class='rep rep{$object_key}'>".$va_artwork_rep."</div>", '', '', 'Detail', 'Collections/'.$va_collection['collection_id']);
							print caNavLink($this->request, "<div style='display:none' class='title title{$object_key}'>".$va_artwork_display."</div>", '', '', 'Detail', 'Collections/'.$va_collection['collection_id']);
							print "</div>";
							$vn_i++;
							if ($vn_i == 3) {
								print "</div><!-- end imageSet-->";
								$vn_i = 0;
							}
							
						}
					}
					if ((end($va_collections) == $va_collection) && ($vn_i < 3) && ($vn_i != 0)){print "</div>";} 

					print "</div>";
			print "</div><!-- end mediaThumbs -->";
		}
		
?>
			
		</div><!-- end mediaArea-->
		<div id='infoArea'>
<?php
		if (($vs_collection = $t_item->get('ca_occurrences.description', array('convertCodesToDisplayText' => true, 'template' => '^description_text'))) != "") {
			print "<div class='description'><div class='metatitle'>"._t('Description')."</div>".$vs_collection."</div>";
		}
		if (($vs_statement = $t_item->get('ca_occurrences.statement.statement_text', array('template' => '^statement_text'))) != "") {
			print "<div class='description'><div class='metatitle'>"._t('Artist Statement')."</div>".$vs_statement."</div>";
		}		
?>	

		</div><!-- end infoArea-->
	</div><!-- end contentArea-->
<?php
	$va_occurrences = $t_item->get('ca_occurrences', array('restrictToTypes' => array('mf_exhibition'), 'returnAsArray' => true, 'checkAccess' => $va_access_values));
	$va_events = $t_item->get('ca_occurrences', array('restrictToTypes' => array('exhibition_event', 'educational', 'fundraising', 'admin_event', 'community_event'), 'returnAsArray' => true, 'checkAccess' => $va_access_values));
	$va_entities = $t_item->get('ca_entities', array('returnAsArray' => true, 'restrictToRelationshipTypes' => array('curator', 'contributor', 'artist'), 'checkAccess' => $va_access_values));
	$va_funders = $t_item->get('ca_entities', array('returnAsArray' => true, 'restrictToRelationshipTypes' => array('funder'), 'checkAccess' => $va_access_values));
	$va_collections = $t_item->get('ca_collections', array('restrictToTypes' => array('installation'), 'returnAsArray' => true, 'checkAccess' => $va_access_values));


	if ((sizeof($va_occurrences) > 0) | (sizeof($va_entities) > 0) | (sizeof($va_events) > 0) | (sizeof($va_collections) > 0) | (sizeof($va_funders) > 0)) {
?>	
	<div id='relatedInfo'>
<?php

	# Related Exhibitions Block
	if (sizeof($va_occurrences) > 0) {
		print "<div id='occurrencesBlock'>";
		print "<div class='blockTitle related'>"._t('Related Exhibitions')."</div>";
			print "<div class='blockResults exhibitions'>";
				print "<div>";

				foreach ($va_occurrences as $occurrence_id => $va_occurrence) {
					$vn_occurrence_id = $va_occurrence['occurrence_id'];
					$t_occurrence = new ca_occurrences($vn_occurrence_id);
					$va_artworks = $t_occurrence->get('ca_collections.collection_id', array('returnAsArray' => true));
					
					
					print "<div class='occurrencesResult'>";
					$vn_ii = 0;
					if (sizeof($va_artworks) >= 4) {
						foreach ($va_artworks as $key => $vn_artwork_id) {
							$t_collection = new ca_collections($vn_artwork_id);
							$va_related_objects = $t_collection->get('ca_objects.object_id', array('returnAsArray' => true));
							$va_object_reps = caGetPrimaryRepresentationsForIDs($va_related_objects, array('versions' => array('widepreview'), 'return' => array('tags')));
						
							if ($vn_ii % 2 == 0){$vs_style = "style='margin-right:10px;'";} else {$vs_style = "";}

							if ($va_primary_rep = array_shift(array_values($va_object_reps))){
								print "<div class='exImage' {$vs_style}>".caNavLink($this->request, $va_primary_rep, '', '', 'Detail', 'Occurrences/'.$va_occurrence['occurrence_id'])."</div>";
								$vn_i++;
								$vn_ii++;
							}
							if($vn_i == 4) {break;}
						}
					} else {
						$va_artwork_id = $va_artworks[0];
						$t_collection = new ca_collections($va_artwork_id);
						$va_related_objects = $t_collection->get('ca_objects.object_id', array('returnAsArray' => true));
						$va_object_reps = caGetPrimaryRepresentationsForIDs($va_related_objects, array('versions' => array('exsingle'), 'return' => array('tags')));
					
						if ($va_primary_rep = array_shift(array_values($va_object_reps))){
							print "<div class='exImageSingle' {$vs_style}>".caNavLink($this->request, $va_primary_rep, '', '', 'Detail', 'Occurrences/'.$va_occurrence['occurrence_id'])."</div>";

						}
						
					}
					print "<div class='exTitle'>".caNavLink($this->request, $va_occurrence['name'], '', '', 'Detail', 'Occurrences/'.$va_occurrence['occurrence_id'])."</div>";
					print "<div class='exDate'>".$t_occurrence->get('ca_occurrences.event_dates')."</div>";	
					print "</div><!-- end occurrenceResult -->";
				}
				print "</div>";
			print "</div><!-- end blockResults -->";	
		print "</div><!-- end entitiesBlock -->";
	}
	

		
	# Related Events Block
	if (sizeof($va_events) > 0) {
		print "<div id='occurrencesBlock'>";
		print "<div class='blockTitle related'>"._t('Related Events')."</div>";
			print "<div class='blockResults'>";
				print "<div>";
					$vn_i = 0;
					foreach ($va_events as $event_id => $va_event) {
						$vn_event_idno = $va_event['idno'];
						$vn_event_id = $va_event['occurrence_id'];
						$t_occurrence = new ca_occurrences($vn_event_id);

						if ($vn_i == 0) {print "<div class='eventSet'>";}
						print "<div class='eventsResult'>";
						print "<div>".caNavLink($this->request, $va_event['label'], '', '', 'Detail', 'Occurrences/'.$vn_event_id)."</div>";
						print "<div class='exDate'>".$t_occurrence->get('ca_occurrences.event_dates')."</div>";	

						print "</div>";
						$vn_i++;
						if ($vn_i == 5) {
							print "</div>";
							$vn_i = 0;
						}
					}
					if ((end($va_events) == $va_event) && ($vn_i < 5) && ($vn_i != 0)){print "</div>";}								

				print "</div>";	
			print "</div><!-- end blockResults -->";
		print "</div><!-- end occurrencesBlock-->";
	}
	# Related Installation Block
	if (sizeof($va_collections) > 0) {
		print "<div id='collectionsBlock'>";
		print "<div class='blockTitle related'>"._t('Related Artworks')."</div>";
			print "<div class='blockResults'>";
			print "<div class='scrollBlock'>";
				print "<div style='width:5000px'>";
					$vn_i = 0;
					foreach ($va_collections as $collection_id => $va_collection) {
						$vn_collection_id = $va_collection['collection_id'];
						$t_collection = new ca_collections($vn_collection_id);

						if ($vn_i == 0) {print "<div class='collectionSet'>";}
						print "<div class='artworkResult'>";
						print "<div>".caNavLink($this->request, $va_collection['label'], '', '', 'Detail', 'Collections/'.$vn_collection_id)."</div>";
						print "</div>";
						$vn_i++;
						if ($vn_i == 5) {
							print "</div>";
							$vn_i = 0;
						}
					}
					if ((end($va_collections) == $va_collection) && ($vn_i < 5) && ($vn_i != 0)){print "</div>";}								

				print "</div>";	
				print "</div>";	
			print "</div><!-- end blockResults -->";
		print "</div><!-- end collectionsBlock-->";
	}	
	# Related Entities Block
	if (sizeof($va_entities) > 0) {
		print "<div id='entitiesBlock'>";
		print "<div class='blockTitle related'>"._t('Related Artists + Curators')."</div>";
			print "<div class='blockResults'>";
				print "<div>";
				$vn_i = 0;
				foreach ($va_entities as $entity_id => $va_entity) {
					$vn_entity_id = $va_entity['entity_id'];
					if ($vn_i == 0) {print "<div class='entitySet'>";}
					print caNavLink($this->request, "<div class='entitiesResult'>".$va_entity['displayname']."</div>", '', '','Detail', 'Entities/'.$va_entity['entity_id']);
					$vn_i++;
					if ($vn_i == 5) {
						print "</div>";
						$vn_i = 0;
					}
				}
				if ((end($va_entities) == $va_entity) && ($vn_i < 5)){print "</div>";}								
				print "</div>";
			print "</div><!-- end blockResults -->";	
		print "</div><!-- end entitiesBlock -->";
	}
	# Related Funders Block
	if (sizeof($va_funders) > 0) {
		print "<div id='fundersBlock'>";
		print "<div class='blockTitle related'>"._t('Related Funders')."</div>";
			print "<div class='blockResults'>";
				print "<div>";
				$vn_i = 0;
				foreach ($va_funders as $funder_id => $va_funder) {
					$vn_funder_id = $va_funder['entity_id'];
					if ($vn_i == 0) {print "<div class='entitySet'>";}
					print caNavLink($this->request, "<div class='entitiesResult'>".$va_funder['displayname']."</div>", '', '','Detail', 'Entities/'.$va_funder['entity_id']);
					$vn_i++;
					if ($vn_i == 5) {
						print "</div>";
						$vn_i = 0;
					}
				}
				if ((end($va_funders) == $va_funder) && ($vn_i < 5)){print "</div>";}								
				print "</div>";
			print "</div><!-- end blockResults -->";	
		print "</div><!-- end entitiesBlock -->";
	}	

	
?>		
	</div><!-- end relatedInfo-->
<?php
	}
?>	
</div>