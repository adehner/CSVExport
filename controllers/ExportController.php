<?php

class CSVExport_ExportController extends Omeka_Controller_AbstractActionController
{
	
    public function csvAction()
    {
    	$search = false;

	if (isset($_GET['search'])){
		$items = $this->csv_search($_GET);
		$search = true;
	} else {
		$items = get_records('Item', array(), 0);
	}
	
	// manually identify specific fields for output
	 //$elements = array();
	/* $elements[] = get_db()->getTable('Element')->findByElementSetNameAndElementName('Dublin Core','Title');
	 $elements[]= get_db()->getTable('Element')->findByElementSetNameAndElementName('Dublin Core','Type');
	 $elements[]= get_db()->getTable('Element')->findByElementSetNameAndElementName('Dublin Core','Date');
	 $elements[]= get_db()->getTable('Element')->findByElementSetNameAndElementName('Dublin Core','Creator');*/
	
	
	// get all fields from a specific element set (eg. dublin core)
         $elementSetName = 'Dublin Core';
         $elements = get_db()->getTable('Element')->findBySet($elementSetName);
    	
    	// get all fields from all element sets
    	/*$table = get_db()->getTable('Element');
    	
    	
        $select = $table->getSelect()
            ->order('elements.element_set_id')
            ->order('ISNULL(elements.order)')
            ->order('elements.order');
        $elements = $table->fetchObjects($select);*/
		
	set_loop_records('items', $items);
	foreach(loop('items') as $item){
	
		// get omeka id and add it to the csv output
		$id = metadata($item, 'ID');		
		$result[$id]['id'] = $id;
		
		// get collection name and add it to the csv output
		$result[$id]['Collection Name'] = metadata($item, 'collection name');
		
		foreach ($elements as $element){
			// remove Omeka Legacy File metadata from csv output
			if ($element->getElementSet()->record_type != 'File') {
				$elementSet = $element->getElementSet()->name;
				$element = $element->name;
				$result[$id][$element] = metadata($item, array($elementSet, $element), array('all'=>true));
				if (count($result[$id][$element]) == 1){
				// the field has 1 value, get it
					$result[$id][$element] = $result[$id][$element][0];
				} elseif (count($result[$id][$element])>1) {
				// if a field has multiple values, parse them
					$results ='';
					foreach ($result[$id][$element] as $value){
						$results .= $value. '; ';
					}
					$result[$id][$element] = rtrim($results, "; ");
				
				} else {
				// this field is empty/null
					$result[$id][$element] = null;
				}
			}
		}
			    
	}
			
	$this->view->assign('result', $result);
	$this->view->assign('search', $search);
		
    }
	
    function csv_search($terms) {   
        $itemTable = $this->_helper->db->getTable('Item');
        if (isset($_GET['search'])) {           
            $items = $itemTable->findBy($_GET);   
            return $items;                   
        } else {
            $queryArray = unserialize($itemTable->query);
            // Some parts of the advanced search check $_GET, others check
            // $_REQUEST, so we set both to be able to edit a previous query.
            $_GET = $queryArray;
            $_REQUEST = $queryArray;
            $items = $itemTable->findBy($_REQUEST);   
            return $items;
        }
    }
}