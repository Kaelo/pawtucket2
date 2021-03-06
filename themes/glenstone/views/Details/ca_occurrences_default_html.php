<?php
	$va_comments = $this->getVar("comments");
?>
	<div class="row">
		<div class='col-xs-1 col-sm-1 col-md-1 col-lg-1'>
			<div class="detailNavBgLeft">
				{{{previousLink}}}{{{resultsLink}}}
			</div><!-- end detailNavBgLeft -->
		</div><!-- end col -->
		<div class='col-xs-10 col-sm-10 col-md-10 col-lg-10'>
	
		</div>
		<div class='col-xs-1 col-sm-1 col-md-1 col-lg-1'>
			<div class="detailNavBgRight">
				{{{nextLink}}}
			</div><!-- end detailNavBgLeft -->
		</div><!-- end col -->
	</div><!-- end row -->	
	<div class="row">		
		<div class="container">
			<H4>{{{^ca_occurrences.preferred_labels.displayname}}}</H4>
			<H5>{{{^ca_occurrences.exh_dates}}}</H5>
			
		{{{<ifcount code="ca_objects" min="1">
			<div id="detailRelatedObjects">
				<H6>Related Objects </H6>
				<div class="jcarousel-wrapper">
					<div id="detailScrollButtonNext"><i class="fa fa-angle-right"></i></div>
					<div id="detailScrollButtonPrevious"><i class="fa fa-angle-left"></i></div>
					<!-- Carousel -->
					<div class="jcarousel">
						<ul>
							<unit relativeTo="ca_objects" delimiter=" "><li><div class='detailObjectsResult'><l>^ca_object_representations.media.library</l></div><div class='caption'><i><l>^ca_objects.preferred_labels.name</l></i><ifdef code="ca_objects.creation_date">, ^ca_objects.creation_date</ifdef></div></li><!-- end detailObjectsBlockResult --></unit>
						</ul>
					</div><!-- end jcarousel -->
					
				</div><!-- end jcarousel-wrapper -->
			</div><!-- end detailRelatedObjects -->
			<script type='text/javascript'>
				jQuery(document).ready(function() {
					/*
					Carousel initialization
					*/
					$('.jcarousel')
						.jcarousel({
							// Options go here
						});
			
					/*
					 Prev control initialization
					 */
					$('#detailScrollButtonPrevious')
						.on('jcarouselcontrol:active', function() {
							$(this).removeClass('inactive');
						})
						.on('jcarouselcontrol:inactive', function() {
							$(this).addClass('inactive');
						})
						.jcarouselControl({
							// Options go here
							target: '-=1'
						});
			
					/*
					 Next control initialization
					 */
					$('#detailScrollButtonNext')
						.on('jcarouselcontrol:active', function() {
							$(this).removeClass('inactive');
						})
						.on('jcarouselcontrol:inactive', function() {
							$(this).addClass('inactive');
						})
						.jcarouselControl({
							// Options go here
							target: '+=1'
						});
				});
			</script></ifcount>}}}
		</div><!-- end container -->
	</div><!-- end row -->
	<div class="row">
		
		<div class='col-md-6 col-lg-6'>
			{{{<ifdef code="ca_occurrences.notes"><H6>About</H6>^ca_occurrences.notes<br/></ifdef>}}}
			
			
			{{{<ifcount code="ca_entities" min="1" max="1"><H6>Related person</H6></ifcount>}}}
			{{{<ifcount code="ca_entities" min="2"><H6>Related people</H6></ifcount>}}}
			{{{<unit relativeTo="ca_entities" delimiter="<br/>"><l>^ca_entities.preferred_labels.displayname</l></unit><br/><br/>}}}
		</div><!-- end col -->
	</div><!-- end row -->