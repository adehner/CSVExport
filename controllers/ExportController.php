<?php

class CSVExport_ExportController extends Omeka_Controller_AbstractActionController
{
    protected $multivalueSeparator = '; ';

    public function csvAction()
    {
        $search = isset($_GET['search']);
        if ($search) {
            $items = $this->csv_search($_GET);
        } else {
            $items = get_records('Item', array(), 0);
        }

        // Cache element set info to speed up loop.
        $elements = $this->prepareElements();

        $separator = get_option('csv_export_separator') ?: $this->multivalueSeparator;
        $full = get_option('csv_export_header_name') === 'full';
        $noFilter = (bool) get_option('csv_export_no_filter');
        $mutipleValues = !get_option('csv_export_single_value');

        $headers = array();
        $result = array();
        set_loop_records('items', $items);

        if ($mutipleValues) {
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
                    $result[$id][$header] = metadata($item, $element, array('all' => true, 'no_filter' => $noFilter));
                    // $result[$id][$header] = $item->getElementTexts($elementSetName, $elementName);
                    // foreach ($result[$id][$header] as $k => $v) {
                    //     $result[$id][$header][$k] = (string) $v;
                    // }
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

            $headers = reset($result);
            $headers = array_keys($headers);
        }

        // For multiple values, the same header will be used multiple times.
        else {
            // First, prepare all values.
            foreach (loop('items') as $item) {
                $id = metadata($item, 'ID');
                $result[$id]['id'] = array($id);
                $result[$id]['Collection Name'] = array(metadata($item, 'collection name'));
                foreach ($elements as $element) {
                    $elementSetName = $element[0];
                    $elementName = $element[1];
                    $header = $full ? $elementSetName . ' : ' . $elementName : $elementName;
                    $result[$id][$header] = metadata($item, $element, array('all' => true, 'no_filter' => $noFilter));
                }
            }

            // Second, get max number of values by column.
            $headerCounts = $this->countHeaders($result);
            $allElements = (bool) get_option('csv_export_all_elements');
            // Keep at least one header, even if the column is empty.
            if ($allElements) {
                foreach ($headerCounts as $header => $count) {
                    $headers[] = $header;
                    for ($i = 1; $i < $count; $i++) {
                        $headers[] = $header;
                    }
                }
            }
            // Only used headers.
            else {
                foreach ($headerCounts as $header => $count) {
                    for ($i = 1; $i <= $count; $i++) {
                        $headers[] = $header;
                    }
                }
            }

            // Third, prepare the rows according to the max number of values.
            foreach ($result as $key => $data) {
                $row = array();
                foreach ($data as $header => $values) {
                    if (in_array($header, $headers)) {
                        $values = array_values($values);
                        $max = $headerCounts[$header];
                        for ($i = 0; $i < $max; $i++) {
                            $row[] = isset($values[$i]) ? $values[$i] : null;
                        }
                    }
                }
                $result[$key] = $row;
            }
        }

        $this->view->assign('search', $search);
        $this->view->assign('headers', $headers);
        $this->view->assign('result', $result);
    }

    /**
     * Compute the max number of columns by header.
     *
     * @param array $result
     * @return array
     */
    protected function countHeaders(array $result)
    {
        $headers = array();
        foreach ($result as $data) {
            foreach ($data as $header => $values) {
                $headers[$header] = isset($headers[$header])
                    ? max($headers[$header], count($values))
                    : count($values);
            }
        }
        return $headers;
    }

    protected function csv_search($terms)
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
    protected function prepareElements()
    {
        // Get all element sets except for legacy files data.
        $db = get_db();
        $table = $db->getTable('ElementSet');
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
        $table = $db->getTable('Element');
        foreach ($elementSets as $elementSet) {
            $elements = array_merge(
                $elements,
                $table->findBySet($elementSet->name)
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

        $allElements = (bool) get_option('csv_export_all_elements');
        if (!$allElements) {
            $sql = <<<SQL
SELECT DISTINCT(element_id)
FROM `omeka_element_texts`
WHERE record_type IS NULL OR record_type = 'Item'
SQL;
            $usedElements = $db->fetchCol($sql);
            $simpleElements = array_intersect_key(
                $simpleElements,
                array_flip($usedElements)
            );
        }

        return $simpleElements;
    }
}
