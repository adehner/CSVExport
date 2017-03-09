<?php


class CSVExportPlugin extends Omeka_Plugin_AbstractPlugin
{

	//Add filters
    protected $_filters = array(
        'admin_navigation_main'
    );

	// Define Hooks
    protected $_hooks = array(
        'admin_items_browse'
        
    );



	public function filterAdminNavigationMain($nav)
	{

	    $nav[] = array(
	                    'label' => __('CSV Export'),
	                    'uri' => url('/csv-export/index')
	                  );
	    return $nav;
	}

	// Adds a button to the admin search page for CSV export
	public function hookAdminItemsBrowse($items)
	{
		if (isset($_GET['search']) && count($items))
		{
			$params = array();
			foreach ($_GET as $key => $value){
					$params[$key] = $value;
			}
			try{
					$params['hits'] = ZEND_REGISTRY::get('total_results');
			}
			catch(Zend_Exception $e){
					$params['hits'] = 0;
			}
			echo "<a  class='button blue' style='margin-top:20px;' href='" . url('csv-export/export/csv', $params) ."'><input style='background-color:transparent;color:white;border:none;' type='button' value='Export results as CSV' /></a>";
		} else {
			echo "<a class='button blue' style='margin-top:20px;' href='" . url('csv-export/export/csv') ."'><input style='background-color:transparent;color:white;border:none;' type='button' value='Export all data as CSV' /></a>";
		}
	}
	
}

?>
