<?php

class CSVExportPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array This plugin's filters.
     */
    protected $_filters = array(
        'admin_navigation_main'
    );

    /**
     * @var array This plugin's hooks.
     */
    protected $_hooks = array(
        'install',
        'upgrade',
        'uninstall',
        'config_form',
        'config',
        'admin_items_browse'

    );

    /**
     * @var array This plugin's options.
     */
    protected $_options = array(
        'csv_export_metadata' => array(
            'id',
            'item_type_name',
            'collection',
            'files',
            // 'file_sources',
            'tags',
            'elements',
        ),
        'csv_export_element_sets' => array(
            'Dublin Core',
        ),
        'csv_export_header_name' => 'simple',
        'csv_export_no_filter' => false,
        'csv_export_all_elements' => false,
        'csv_export_single_value' => false,
        'csv_export_delimiter' => ',',
        'csv_export_enclosure' => '"',
        'csv_export_separator' => '; ',
    );

    /**
     * Installs the plugin.
     */
    public function hookInstall()
    {
        /** @var ElementSet $dublinCore */
        $table = get_db()->getTable('ElementSet');
        $dublinCore = $table->findByName('Dublin Core');
        // Set Dublin core first.
        $this->_options['csv_export_element_sets'] = empty($dublinCore)
            ? array()
            : array($dublinCore->name);
        $this->_options['csv_export_element_sets'] = json_encode($this->_options['csv_export_element_sets']);
        $this->_options['csv_export_metadata'] = json_encode($this->_options['csv_export_metadata']);
        $this->_installOptions();
    }

    /**
     * Upgrade the plugin.
     */
    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];

        if (version_compare($oldVersion, '1.2', '<')) {
            // Convert element set ids into a list of names.
            $elementSets = unserialize(get_option('csv_export_settings'));
            $elementSets = $elementSets['elementSets'];
            $this->_options['csv_export_element_sets'] = array();
            foreach ($elementSets as $elementSetId => $toOutput) {
                if ($toOutput) {
                    $elementSet = get_record_by_id('ElementSet', $elementSetId);
                    if ($elementSet) {
                        $this->_options['csv_export_element_sets'][] = $elementSet->name;
                    }
                }
            }

            $this->_options['csv_export_element_sets'] = json_encode($this->_options['csv_export_element_sets']);
            $this->_options['csv_export_metadata'] = json_encode($this->_options['csv_export_metadata']);
            $this->_installOptions();

            delete_option('csv_export_settings');
            $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
            $flash->addMessage(__('Check and save the config of the plugin: there are new options.'));
        }
    }

    /**
     * Uninstalls the plugin.
     */
    public function hookUninstall()
    {
        $this->_uninstallOptions();
    }

    /**
     * Shows plugin configuration page.
     */
    public function hookConfigForm($args)
    {
        // For simplicity, remove element sets that are not "All" or "Item".
        $elementSets = array();
        $elementSetsAll = get_records('ElementSet', array(), 0);
        foreach ($elementSetsAll as $elementSet) {
            if (in_array($elementSet->record_type, array(null, 'Item'))) {
                $elementSets[$elementSet->name] = $elementSet->name;
            }
        }

        $view = get_view();
        echo $view->partial(
            'plugins/csv-export-config-form.php',
            array(
                'elementSets' => $elementSets,
            )
        );
    }

    /**
     * Saves plugin configuration page.
     *
     * @param array Options set in the config form.
     */
    public function hookConfig($args)
    {
        $post = $args['post'];
        foreach ($this->_options as $optionKey => $defaultValue) {
            if (isset($post[$optionKey])) {
                if (is_array($defaultValue)) {
                    $post[$optionKey] = json_encode($post[$optionKey]);
                }
                set_option($optionKey, $post[$optionKey]);
            }
        }
    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('CSV Export'),
            'uri' => url('/csv-export/index')
        );
        return $nav;
    }

    /**
     * Adds a button to the admin search page for CSV export
     */
    public function hookAdminItemsBrowse($items)
    {
        if (isset($_GET['search']) && count($items)) {
            $params = array();
            foreach ($_GET as $key => $value) {
                $params[$key] = $value;
            }
            try {
                $params['hits'] = ZEND_REGISTRY::get('total_results');
            } catch (Zend_Exception $e) {
                $params['hits'] = 0;
            }
            echo "<a  class='button blue' style='margin-top:20px;' href='" . url('csv-export/export/csv', $params) . "'><input style='background-color:transparent;color:white;border:none;' type='button' value='Export results as CSV' /></a>";
        } else {
            echo "<a class='button blue' style='margin-top:20px;' href='" . url('csv-export/export/csv') . "'><input style='background-color:transparent;color:white;border:none;' type='button' value='Export all data as CSV' /></a>";
        }
    }
}
