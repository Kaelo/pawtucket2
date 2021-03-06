<?php
/* ----------------------------------------------------------------------
 * app/controllers/SearchController.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2014 Whirl-i-Gig
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
 * ----------------------------------------------------------------------
 */
 	require_once(__CA_MODELS_DIR__."/ca_collections.php");
 	require_once(__CA_APP_DIR__."/helpers/searchHelpers.php");
 	
 	class SearchController extends ActionController {
 		# -------------------------------------------------------
 		/**
 		 *
 		 */
 		private $ops_find_type = "search";

 		/**
 		 *
 		 */
 		private $opo_result_context = null;
 		
 		/**
 		 *
 		 */
 		protected $opa_access_values = array();
 		
 		# -------------------------------------------------------
 		/**
 		 *
 		 */
 		public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
 			parent::__construct($po_request, $po_response, $pa_view_paths);

 			$this->opa_access_values = caGetUserAccessValues($po_request);
 		 	$this->view->setVar("access_values", $this->opa_access_values);
 			
 			caSetPageCSSClasses(array("search", "results"));
 			
 			if ($this->request->config->get('pawtucket_requires_login')&&!($this->request->isLoggedIn())) {
                $this->response->setRedirect(caNavUrl($this->request, "", "", ""));
            }
 		}
 		# -------------------------------------------------------
 		/**
 		 *
 		 */ 
 		public function __call($ps_function, $pa_args) {
 			$o_config = caGetBrowseConfig();
 			
 			$ps_function = strtolower($ps_function);
 			$ps_type = $this->request->getActionExtra();
 			
 			if (!($va_browse_info = caGetInfoForBrowseType($ps_function))) {
 				// invalid browse type – throw error
 				die("Invalid browse type");
 			}
 			$vs_class = $va_browse_info['table'];
 			$va_types = caGetOption('restrictToTypes', $va_browse_info, array(), array('castTo' => 'array'));
 			
 			$this->opo_result_context = new ResultContext($this->request, $va_browse_info['table'], $this->ops_find_type);
 			$this->opo_result_context->setAsLastFind();
 			
 			$this->view->setVar('browseInfo', $va_browse_info);
 			$this->view->setVar('options', caGetOption('options', $va_browse_info, array(), array('castTo' => 'array')));
 			
 			
 			$ps_view = $this->request->getParameter('view', pString);
 			if(!in_array($ps_view, array('list', 'images', 'timeline', 'map', 'timelineData'))) {
 				$ps_view = 'images';
 			}
 			$vs_format = ($ps_view == 'timelineData') ? 'json' : 'html';
 			
 			#caAddPageCSSClasses(array($vs_class, $ps_function, $ps_view));
 			caAddPageCSSClasses(array($vs_class, $ps_function));
 			
 			$this->view->setVar('isNav', (bool)$this->request->getParameter('isNav', pInteger));	// flag for browses that originate from nav bar
 			
			$t_instance = $this->getAppDatamodel()->getInstanceByTableName($vs_class, true);
			$vn_type_id = $t_instance->getTypeIDForCode($ps_type);
			
			$this->view->setVar('t_instance', $t_instance);
 			$this->view->setVar('table', $va_browse_info['table']);
 			$this->view->setVar('primaryKey', $t_instance->primaryKey());
		
			$this->view->setVar('browse', $o_browse = caGetBrowseInstance($vs_class));
			$this->view->setVar('views', caGetOption('views', $va_browse_info, array(), array('castTo' => 'array')));
			$this->view->setVar('view', $ps_view);
			$this->view->setVar('viewIcons', $o_config->getAssoc("views"));
		
			//
			// Load existing browse if key is specified
			//
			if ($ps_cache_key = $this->request->getParameter('key', pString)) {
				$o_browse->reload($ps_cache_key);
			}
		
			if (is_array($va_types) && sizeof($va_types)) { $o_browse->setTypeRestrictions($va_types); }
		
			//
			// Clear criteria if required
			//
			
			if ($vs_remove_criterion = $this->request->getParameter('removeCriterion', pString)) {
				$o_browse->removeCriteria($vs_remove_criterion, array($this->request->getParameter('removeID', pString)));
			}
			
			if ((bool)$this->request->getParameter('clear', pInteger)) {
				$o_browse->removeAllCriteria();
			}
			
				
			if ($this->request->getParameter('getFacet', pInteger)) {
				$vs_facet = $this->request->getParameter('facet', pString);
				$this->view->setVar('facet_content', $o_browse->getFacetContent($vs_facet, array("checkAccess" => $this->opa_access_values)));
				$this->view->setVar('facet_name', $vs_facet);
				$this->view->setVar('key', $o_browse->getBrowseID());
				$va_facet_info = $o_browse->getInfoForFacet($vs_facet);
				$this->view->setVar('facet_info', $va_facet_info);
				# --- pull in different views based on format for facet - alphabetical, list, hierarchy
				switch($va_facet_info["group_mode"]){
					default:
						$this->render("Browse/list_facet_html.php");
					break;
				}
				return;
			}
		
			//
			// Add criteria and execute
			//
			if ($vs_facet = $this->request->getParameter('facet', pString)) {
				$o_browse->addCriteria($vs_facet, array($this->request->getParameter('id', pString)));
			} else { 
				if ($o_browse->numCriteria() == 0) {
					$o_browse->addCriteria("_search", array($x=$this->opo_result_context->getSearchExpression()));
				}
			}
			
			//
			// Sorting
			//
			if (!($ps_sort = $this->request->getParameter("sort", pString))) {
 				if (!($ps_sort = $this->opo_result_context->getCurrentSort())) {
 					if(is_array(($va_sorts = caGetOption('sortBy', $va_browse_info, null)))) {
 						$ps_sort = array_shift(array_keys($va_sorts));
 					}
 				}
 			}
 			
 			if (!($ps_sort_direction = $this->request->getParameter("direction", pString))) {
 				if (!($ps_sort_direction = $this->opo_result_context->getCurrentSortDirection())) {
 					$ps_sort_direction = 'asc';
 				}
 			}
 			
 			$this->opo_result_context->setCurrentSort($ps_sort);
 			$this->opo_result_context->setCurrentSortDirection($ps_sort_direction);
 			
			$va_sort_by = caGetOption('sortBy', $va_browse_info, null);
			$this->view->setVar('sortBy', is_array($va_sort_by) ? $va_sort_by : null);
			$this->view->setVar('sortBySelect', $vs_sort_by_select = (is_array($va_sort_by) ? caHTMLSelect("sort", $va_sort_by, array('id' => "sort"), array("value" => $ps_sort)) : ''));
			$this->view->setVar('sortControl', $vs_sort_by_select ? _t('Sort with %1', $vs_sort_by_select) : '');
			$this->view->setVar('sort', $ps_sort);
			$this->view->setVar('sort_direction', $ps_sort_direction);
			

			$o_browse->execute(array('checkAccess' => $this->opa_access_values));
		
			//
			// Facets
			//
			if ($vs_facet_group = caGetOption('facetGroup', $va_browse_info, null)) {
				$o_browse->setFacetGroup($vs_facet_group);
			}
			$va_available_facet_list = caGetOption('availableFacets', $va_browse_info, null);
			$va_facets = $o_browse->getInfoForAvailableFacets();
			if(is_array($va_available_facet_list) && sizeof($va_available_facet_list)) {
				foreach($va_facets as $vs_facet_name => $va_facet_info) {
					if (!in_array($vs_facet_name, $va_available_facet_list)) {
						unset($va_facets[$vs_facet_name]);
					}
				}
			} 
		
			foreach($va_facets as $vs_facet_name => $va_facet_info) {
				$va_facets[$vs_facet_name]['content'] = $o_browse->getFacetContent($vs_facet_name, array("checkAccess" => $this->opa_access_values));
			}
		
			$this->view->setVar('facets', $va_facets);
		
			$this->view->setVar('key', $vs_key = $o_browse->getBrowseID());
			$this->request->session->setVar($ps_function.'_last_browse_id', $vs_key);
			
		
			//
			// Current criteria
			//
			$va_criteria = $o_browse->getCriteriaWithLabels();
			if (isset($va_criteria['_search']) && (isset($va_criteria['_search']['*']))) {
				unset($va_criteria['_search']);
			}
			$va_criteria_for_display = array();
			foreach($va_criteria as $vs_facet_name => $va_criterion) {
				$va_facet_info = $o_browse->getInfoForFacet($vs_facet_name);
				foreach($va_criterion as $vn_criterion_id => $vs_criterion) {
					$va_criteria_for_display[] = array('facet' => $va_facet_info['label_singular'], 'facet_name' => $vs_facet_name, 'value' => $vs_criterion, 'id' => $vn_criterion_id);
				}
			}
			$this->view->setVar('criteria', $va_criteria_for_display);
		
			// 
			// Results
			//
			$qr_res = $o_browse->getResults(array('sort' => $va_sort_by[$ps_sort], 'sort_direction' => $ps_sort_direction));
			$this->view->setVar('result', $qr_res);
		
			if (!($pn_hits_per_block = $this->request->getParameter("n", pString))) {
 				if (!($pn_hits_per_block = $this->opo_result_context->getItemsPerPage())) {
 					$pn_hits_per_block = $o_config->get("defaultHitsPerBlock");
 				}
 			}
 			$this->opo_result_context->getItemsPerPage($pn_hits_per_block);
			
			$this->view->setVar('hits_per_block', $pn_hits_per_block);
			
			$this->view->setVar('start', $this->request->getParameter('s', pInteger));
			
			$this->opo_result_context->setParameter('key', $vs_key);
			$this->opo_result_context->setResultList($qr_res->getPrimaryKeyValues());
			$this->opo_result_context->saveContext();
 			
 			if ($vn_type_id) {
 				if ($this->render("Browse/{$vs_class}_{$vs_type}_{$ps_view}_{$vs_format}.php")) { return; }
 			} 
 			
 			switch($ps_view) {
 				case 'timelineData':
 					$this->view->setVar('view', 'timeline');
 					$this->render("Browse/browse_results_timelineData_json.php");
 					break;
 				default:
 					$this->render("Browse/browse_results_html.php");
 					break;
 			}
 		}
 		# -------------------------------------------------------
		/** 
		 * Generate the URL for the "back to results" link from a browse result item
		 * as an array of path components.
		 */
 		public static function getReturnToResultsUrl($po_request) {
 			$va_ret = array(
 				'module_path' => '',
 				'controller' => 'Search',
 				'action' => $po_request->getAction(),
 				'params' => array(
 					'key'
 				)
 			);
			return $va_ret;
 		}
 		# -------------------------------------------------------
	}
 ?>