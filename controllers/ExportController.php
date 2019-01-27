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

        $metadata = json_decode(get_option('csv_export_metadata'), true) ?: array();
        $full = get_option('csv_export_header_name') === 'full';
        $noFilter = (bool) get_option('csv_export_no_filter');

        // Cache element set info to speed up loop.
        $elements = in_array('elements', $metadata) ? $this->prepareElements() : array();

        $options = compact('metadata', 'elements', 'full', 'noFilter');

        $headers = array();
        $result = array();
        set_loop_records('items', $items);

        // First, prepare all values.
        /** @var Item $item */
        foreach (loop('items') as $item) {
            $result[$item->id] = $this->fillRecordData($item, $options);
        }

        // Second, output the metadata according to options.
        $mutipleValues = !get_option('csv_export_single_value');
        if ($mutipleValues) {
            // Convert all results into a string.
            $separator = get_option('csv_export_separator') ?: $this->multivalueSeparator;
            foreach ($result as $id => $meta) {
                foreach ($meta as $header => $values) {
                    if (count($values) == 1) {
                        // If the field has 1 value, get it.
                        $result[$id][$header] = reset($values);
                    } elseif (count($values) > 1) {
                        // If a field has multiple values, parse them to add a multivalue separator.
                        $result[$id][$header] = implode($separator, $values);
                    } else {
                        // This field is empty/null.
                        $result[$id][$header] = null;
                    }
                }
            }

            $headers = reset($result);
            $headers = array_keys($headers);
        }

        // For multiple values, the same header will be used multiple times.
        else {
            // Get max number of values by column.
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
            // Else only used headers.
            else {
                foreach ($headerCounts as $header => $count) {
                    for ($i = 1; $i <= $count; $i++) {
                        $headers[] = $header;
                    }
                }
            }

            // Prepare the rows according to the max number of values.
            foreach ($result as $key => $meta) {
                $row = array();
                foreach ($meta as $header => $values) {
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
     * Get all wanted metadata of an item.
     *
     * Each metadata is an array to simplify conversion into any output format,
     * even single values like id.
     *
     * @param Omeka_Record_AbstractRecord
     * @param array $options
     * @return array
     */
    protected function fillRecordData(Omeka_Record_AbstractRecord $record, array $options)
    {
        $data = array();

        /**
         * @var array $metadata
         * @var array $elements
         * @var bool $full
         * @var bool $noFilter
         */
        extract($options);

        // Fill an array of data in the order specified in the metadata array.
        foreach ($metadata as $meta) switch ($meta) {
            case 'id':
                $data['id'] = array($record->id);
                break;

            case 'item_type':
                $value = $record->getProperty('item_type_name');
                $data['Item Type'] = $value ? array($value) : array();
                break;

            case 'collection':
                $value = metadata($record, 'collection name');
                $data['Collection Name'] = $value ? array($value) : array();
                break;

            case 'files':
                $data['Files'] = array();
                foreach ($record->getFiles() as $file) {
                    $data['Files'][] = $file->getProperty('uri');
                }
                break;

            case 'file_sources':
                $data['File sources'] = array();
                foreach ($record->getFiles() as $file) {
                    $data['File sources'][] = metadata($file, 'original_filename');
                }
                break;

            case 'tags':
                $data['Tags'] = array();
                if (metadata($record, 'has_tags')) {
                    foreach ($record->getTags() as $tag) {
                        $data['Tags'][] = $tag->name;
                    }
                }
                break;

            case 'elements':
                foreach ($elements as $element) {
                    $elementSetName = $element[0];
                    $elementName = $element[1];
                    $header = $full ? $elementSetName . ' : ' . $elementName : $elementName;
                    // All elements are filled, even empty.
                    $data[$header] = metadata($record, $element, array('all' => true, 'no_filter' => $noFilter));
                    // $data[$header] = $record->getElementTexts($elementSetName, $elementName);
                    // foreach ($data[$header] as $k => $v) {
                    //     $data[$header][$k] = (string) $v;
                    // }
                }
                break;
        }

        return $data;
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
        $elementSetNames = json_decode(get_option('csv_export_element_sets'), true) ?: array();
        foreach ($elementSetsAll as $elementSet) {
            if (in_array($elementSet->name, $elementSetNames)) {
                // To avoid error, remove automatically element sets that are
                // not "All" or "Item".
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
