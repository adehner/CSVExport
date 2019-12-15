<?php

class CSVExport_ExportController extends Omeka_Controller_AbstractActionController
{
    protected $perPage = 100;

    protected $multivalueSeparator = '; ';

    protected $usedElementIds;

    protected $settings = array();

    public function init()
    {
        $this->_helper->db->setDefaultModelName('Item');
    }

    public function csvAction()
    {
        $params = $_GET;
        $isSearch = isset($params['search']);
        if ($isSearch) {
            $totalRecords = $this->_helper->db->count($params);
        } else {
            $params = array();
            $totalRecords = total_records('Item');
        }

        if (empty($totalRecords)) {
            $this->flash(__('No item to export.'));
            return $this->forward('index', 'index');
        }

        $metadata = json_decode(get_option('csv_export_metadata'), true) ?: array();
        $isFullName = get_option('csv_export_header_name') === 'full';
        $noFilter = (bool) get_option('csv_export_no_filter');
        $endOfLine = (bool) get_option('csv_export_end_of_line');
        $enclosure = get_option('csv_export_enclosure') ?: '"';
        $delimiter = get_option('csv_export_delimiter') ?: ',';
        if ($delimiter === 'tab') {
            $delimiter = "\t";
        }

        $isMultipleValuesByCell = !get_option('csv_export_single_value');
        $separator = get_option('csv_export_separator') ?: $this->multivalueSeparator;
        $isAllElements = (bool) get_option('csv_export_all_elements');
        // Cache element set info to speed up loop.
        $elements = in_array('elements', $metadata) ? $this->prepareElements() : array();

        $this->settings = array(
            'isSearch' => $isSearch,
            'metadata' => $metadata,
            'isFullName' => $isFullName,
            'noFilter' => $noFilter,
            'endOfLine' => $endOfLine,
            'delimiter' => $delimiter,
            'enclosure' => $enclosure,
            'isMultipleValuesByCell' => $isMultipleValuesByCell,
            'separator' => $separator,
            'isAllElements' => $isAllElements,
            'elements' => $elements,
        );

        // The process requires a first loop to get all headers, in particular
        // when there are multiple columns by element.
        $page = 1;
        $headers = array();
        while (true) {
            $partHeaders = $this->prepareHeaders($params, $this->perPage, $page);
            $headers = $this->mergeHeaders($headers, $partHeaders);
            if (($page * $this->perPage) >= $totalRecords) {
                break;
            }
            ++$page;
        }

        $headers = $this->finalizeHeaders($headers);

        // Output headers.
        $file = $this->prepareOutput($headers);

        // Output rows.
        $page = 1;
        while (true) {
            $result = $this->exportItems($params, $headers, $this->perPage, $page);
            foreach ($result as $data) {
                fputcsv($file, $data, $delimiter, $enclosure);
            }
            if (($page * $this->perPage) >= $totalRecords) {
                break;
            }
            ++$page;
        }

        fclose($file);
    }

    protected function prepareHeaders($params, $perPage, $page)
    {
        $itemTable = $this->_helper->db->getTable('Item');
        $items = $itemTable->findBy($params, $perPage, $page);

        $headers = array();
        $result = array();
        set_loop_records('items', $items);

        // First, prepare all values.
        /** @var Item $item */
        foreach (loop('items') as $item) {
            $result[$item->id] = $this->fillRecordData($item);
        }

        // Second, output the metadata according to options.
        if ($this->settings['isMultipleValuesByCell']) {
            // Convert all results into a string.
            foreach ($result as $id => $meta) {
                foreach ($meta as $header => $values) {
                    if (count($values) == 1) {
                        // If the field has 1 value, get it.
                        $result[$id][$header] = reset($values);
                    } elseif (count($values) > 1) {
                        // If a field has multiple values, parse them to add a multivalue separator.
                        $result[$id][$header] = implode($this->settings['separator'], $values);
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
            // Keep at least one header, even if the column is empty.
            if ($this->settings['isAllElements']) {
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

        // In case of a search, there may be useless columns to remove.
        // TODO Remove all empty columns in case of a search with only used elements.
        // if ($this->settings['isSearch'] && !$this->settings['isAllElements']) {
        // }

        return $headers;
    }

    protected function exportItems($params, $headers, $perPage, $page)
    {
        $itemTable = $this->_helper->db->getTable('Item');
        $items = $itemTable->findBy($params, $perPage, $page);

        $result = array();
        set_loop_records('items', $items);

        // First, prepare all values.
        /** @var Item $item */
        foreach (loop('items') as $item) {
            $result[$item->id] = $this->fillRecordData($item);
        }

        // Second, output the metadata according to options.
        if ($this->settings['isMultipleValuesByCell']) {
            // Convert all results into a string.
            foreach ($result as $id => $meta) {
                $result[$id] = array_fill_keys($headers, null);
                foreach ($meta as $header => $values) {
                    if (count($values) == 1) {
                        // If the field has 1 value, get it.
                        $result[$id][$header] = reset($values);
                    } elseif (count($values) > 1) {
                        // If a field has multiple values, parse them to add a multivalue separator.
                        $result[$id][$header] = implode($this->settings['separator'], $values);
                    } else {
                        // This field is empty/null.
                        $result[$id][$header] = null;
                    }
                }
            }
        }

        // For multiple values, the same header will be used multiple times.
        else {
            // Get max number of values by column.
            $headerCounts = array_count_values($headers);

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

        return $result;
    }

    protected function mergeHeaders($headers, $newHeaders)
    {
        if ($this->settings['isMultipleValuesByCell']) {
            return array_unique(array_merge($headers, $newHeaders));
        }

        $count = array_count_values($headers);
        $newCount = array_count_values($newHeaders);
        foreach ($newCount as $element => $total) {
            if (isset($count[$element])) {
                if ($total > $count[$element]) {
                    for ($i = 0; $i < ($total - $count[$element]); $i++) {
                        $headers[] = $element;
                    }
                }
            } else {
                for ($i = 0; $i < $total; $i++) {
                    $headers[] = $element;
                }
            }
        }
        return $headers;
    }

    protected function finalizeHeaders($headers)
    {
        // Reorder according to elements.
        $result = array();
        $isFullName = $this->settings['isFullName'];

        $elements = array_merge(
            array(
                'id',
                'Item Type',
                'Collection Name',
                'Files',
                'File sources',
                'Tags',
            ),
            array_values(array_map(function($v) use ($isFullName) {
                return $isFullName ? $v[0] . ' : ' . $v[1] : $v[1];
            }, $this->settings['elements']))
        );

        if ($this->settings['isMultipleValuesByCell']) {
            $headers = array_unique($headers);
            foreach ($elements as $element) {
                if (in_array($element, $headers)) {
                    $result[] = $element;
                }
            }
        } else {
            $count = array_count_values($headers);
            foreach ($elements as $element) {
                if (isset($count[$element])) {
                    for ($i = 0; $i < $count[$element]; $i++) {
                        $result[] = $element;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get all wanted metadata of an item.
     *
     * Each metadata is an array to simplify conversion into any output format,
     * even single values like id.
     *
     * @param Omeka_Record_AbstractRecord
     * @return array
     */
    protected function fillRecordData(Omeka_Record_AbstractRecord $record)
    {
        $data = array();

        // Fill an array of data in the order specified in the metadata array.
        foreach ($this->settings['metadata'] as $meta) switch ($meta) {
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
                $usedElementIds = $this->getUsedElementIds();
                foreach ($this->settings['elements'] as $elementId => $element) {
                    $elementSetName = $element[0];
                    $elementName = $element[1];
                    $header = $this->settings['isFullName'] ? $elementSetName . ' : ' . $elementName : $elementName;
                    // For performance reasons, check the list of used elements.
                    $elementValues = isset($usedElementIds[$elementId])
                        ? metadata($record, $element, array('all' => true, 'no_filter' => $this->settings['noFilter']))
                        : array();
                    if ($this->settings['endOfLine']) {
                        $elementValues = array_map(function ($v) {
                            return str_replace(array("\n\r", "\r\n", "\n", "\r\r"), "\r", $v);
                        }, $elementValues);
                    }

                    $data[$header] = $elementValues;
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

    protected function csv_search($params, $perPage, $page)
    {
        $itemTable = $this->_helper->db->getTable('Item');

        if (isset($params['search'])) {
            return $itemTable->findBy($params, $perPage, $page);
        }

        $queryArray = unserialize($itemTable->query);
        // Some parts of the advanced search check $_GET, others check
        // $_REQUEST, so we set both to be able to edit a previous query.
        $_GET = $queryArray;
        $_REQUEST = $queryArray;
        return $itemTable->findBy($_REQUEST, $perPage, $page);
    }


    protected function prepareOutput($headers)
    {
        $delimiter = $this->settings['delimiter'];
        if ($delimiter === 'tab' || $delimiter === "\t") {
            $delimiter = "\t";
            $extension = 'tsv';
            $mediaType = 'text/tab-separated-values';
        } else {
            $extension = 'csv';
            $mediaType = 'text/csv';
        }

        $title = option('site_title');
        $title = preg_replace('/[^A-Za-z0-9-]/', '_', $title);
        $title = preg_replace('/_+/', '_', $title);
        $title = substr($title, 0, 16);
        $title = urlencode($title);

        // date_default_timezone_set('America/Los_Angeles');
        $fileName = $title . '-' . ($this->settings['isSearch'] ? 'Export' : 'Full_Export') . '-' . date('Ymd-His') . '.' . $extension;

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: ' . $mediaType);
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Expires: 0');
        header('Pragma: public');

        $file = fopen('php://output', 'w');

        fputcsv($file, $headers, $delimiter, $this->settings['enclosure']);

        return $file;
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

        $isAllElements = (bool) get_option('csv_export_all_elements');
        if (!$isAllElements) {
            $usedElementIds = $this->getUsedElementIds();
            $simpleElements = array_intersect_key(
                $simpleElements,
                $usedElementIds
            );
        }

        return $simpleElements;
    }

    protected function getUsedElementIds()
    {
        if (is_array($this->usedElementIds)) {
            return $this->usedElementIds;
        }

        $db = get_db();

        $sql = <<<SQL
SELECT DISTINCT(element_id)
FROM `{$db->prefix}element_texts`
WHERE record_type IS NULL OR record_type = 'Item'
SQL;
        $result = $db->fetchCol($sql);
        $this->usedElementIds = array_combine($result, $result);
        return $this->usedElementIds;
    }
}
