<?php

class CSVExport_ExportController extends Omeka_Controller_AbstractActionController
{
    protected $multivalueSeparator = '; ';

    public function csvAction()
    {
        $search = false;

        if (isset($_GET['search'])) {
            $items = $this->csv_search($_GET);
            $search = true;
        } else {
            $items = get_records('Item', array(), 0);
        }

        // Cache element set info to speed up loop.
        $elements = $this->prepareElements();

        $separator = get_option('csv_export_separator') ?: $this->multivalueSeparator;
        $full = get_option('csv_export_header_name') === 'full';

        $result = array();
        set_loop_records('items', $items);
        foreach (loop('items') as $item) {
            // get omeka id and add it to the csv output
            $id = metadata($item, 'ID');
            $result[$id]['id'] = $id;

            // get collection name and add it to the csv output
            $result[$id]['Collection Name'] = metadata($item, 'collection name');

            foreach ($elements as $element) {
                $elementSetName = $element[0];
                $elementName = $element[1];
                $header = $full ? $elementSetName . ' : ' . $elementName : $elementName;
                // $result[$id][$elementName] = $item->getElementTexts($elementSetName, $elementName);
                $result[$id][$header] = metadata($item, $element, array('all' => true));
                foreach ($result[$id][$header] as $k => $v) {
                    $result[$id][$header][$k] = (string) $v;
                }
                if (count($result[$id][$header]) == 1) {
                    // the field has 1 value, get it
                    $result[$id][$header] = reset($result[$id][$header]);
                } elseif (count($result[$id][$header]) > 1) {
                    // if a field has multiple values, parse them to add a multivalue separator.
                    $result[$id][$header] = implode($separator, $result[$id][$header]);
                } else {
                    // this field is empty/null
                    $result[$id][$header] = null;
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

    /**
     * Prepare the list of element names one time only.
     *
     * @return array
     */
    function prepareElements()
    {
        // Get all element sets except for legacy files data.
        $table = get_db()->getTable('ElementSet');
        $elementSetsAll = $table->fetchObjects($table->getSelect());

        // Filter by those set in config UI, and keep item only.
        $elementSets = array();
        $settings = unserialize(get_option('csv_export_settings'));
        foreach ($elementSetsAll as $elementSet) {
            if (array_key_exists($elementSet->id, $settings['elementSets'])) {
                // For security, remove element sets that are not "All" or "Item".
                if (in_array($elementSet->record_type, array(null, 'Item'))) {
                    $elementSets[$elementSet->id] = $elementSet;
                }
            }
        }

        // Get all fields from each specific element set (eg. Dublin Core).
        $elements = array();
        foreach ($elementSets as $elementSet) {
            $elements = array_merge(
                $elements,
                get_db()->getTable('Element')->findBySet($elementSet->name)
            );
        }

        // Simplify the elements array one time.
        $simpleElements = array();
        foreach ($elements as $element) {
            $simpleElements[$element->id] = array(
                $elementSets[$element->element_set_id]->name,
                $element->name
            );
        }

        return $simpleElements;
    }
}
