<?php

class CSVExport_ExportController extends Omeka_Controller_AbstractActionController
{

  public function csvAction()
  {
    $search = false;

    if (isset($_GET['search'])) {
      $items = $this->csv_search($_GET);
      $search = true;
    } else {
      $items = get_records('Item', array(), 0);
    }

    // Get all element sets except for legacy files data.
    $table = get_db()->getTable('ElementSet');
    $elementSetsAll = $table->fetchObjects($table->getSelect());
    $elementSets = array();

    // Filter by those set in config UI.
    $settings = unserialize(get_option('csv_export_settings'));
    foreach ($elementSetsAll as $elementSet) {
      if (array_key_exists($elementSet->id, $settings['elementSets'])) {
        $elementSets[$elementSet->id] = $elementSet;
      }
    }

    // get all fields from a specific element set (eg. dublin core)
    $elements = array();
    foreach ($elementSets as $elementSet) {
      $elements = array_merge(
        $elements,
        get_db()->getTable('Element')->findBySet($elementSet->name)
      );
    }

    set_loop_records('items', $items);
    foreach (loop('items') as $item) {

      // get omeka id and add it to the csv output
      $id = metadata($item, 'ID');
      $result[$id]['id'] = $id;

      // get collection name and add it to the csv output
      $result[$id]['Collection Name'] = metadata($item, 'collection name');

      // Cache element set info to speed up loop.
      foreach ($elements as $element) {
        $elementSetName = $elementSets[$element->element_set_id]->name;
        $element = $element->name;
        $result[$id][$element] = metadata($item, array($elementSetName, $element), array('all' => true));
        if (count($result[$id][$element]) == 1) {
          // the field has 1 value, get it
          $result[$id][$element] = $result[$id][$element][0];
        } elseif (count($result[$id][$element]) > 1) {
          // if a field has multiple values, parse them
          $results = '';
          foreach ($result[$id][$element] as $value) {
            $results .= $value . '; ';
          }
          $result[$id][$element] = rtrim($results, "; ");

        } else {
          // this field is empty/null
          $result[$id][$element] = null;
        }
      }
    }

    $this->view->assign('result', $result);
    $this->view->assign('search', $search);

  }

  function csv_search($terms)
  {
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
