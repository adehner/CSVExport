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
        'uninstall',
        'config_form',
        'config',
        'admin_items_browse'

    );

    /**
     * @var array This plugin's options.
     */
    protected $_options = array(
        'csv_export_settings' => array(
            'elementSets' => array(),
        ),
        'csv_export_header_name' => 'simple',
        'csv_export_delimiter' => ',',
        'csv_export_enclosure' => '"',
        'csv_export_separator' => '; ',
    );

    /**
     * Installs the plugin.
     */
    public function hookInstall()
    {
        $table = get_db()->getTable('ElementSet');
        $dublinCore = $table->findByName('Dublin Core');
        // Set Dublin core first.
        if (isset($dublinCore->id)) {
            $this->_options['csv_export_settings']['elementSets'][$dublinCore->id] = true;
        }
        $this->_options['csv_export_settings'] = serialize($this->_options['csv_export_settings']);
        $this->_installOptions();
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
        $settings = unserialize(get_option('csv_export_settings'));
        $table = get_db()->getTable('ElementSet');
        $elementSetsAll = $table->fetchObjects($table->getSelect());

        // For security, remove element sets that are not "All" or "Item".
        foreach ($elementSetsAll as $key => $elementSet) {
            if (!in_array($elementSet->record_type, array(null, 'Item'))) {
                unset($elementSetsAll[$key]);
            }
        }

        $view = get_view();
        echo $view->partial(
            'plugins/csv-export-config-form.php', array(
                'settings' => $settings,
                'elementSetsAll' => $elementSetsAll,
            ));
    }

    /**
     * Saves plugin configuration page.
     *
     * @param array Options set in the config form.
     */
    public function hookConfig($args)
    {
        $post = $args['post'];
        foreach (array_keys($this->_options) as $optionKey) {
            if (isset($post[$optionKey])) {
                set_option($optionKey, $post[$optionKey]);
            }
        }

        $settings = array();
        $settings['elementSets'] = array();

        $elementSets = $post['elementSets'];
        foreach ($elementSets as $id => $checked) {
            if (is_numeric($id) && (bool) $checked) {
                $settings['elementSets'][(int) $id] = (bool) $checked;
            }
        }

        set_option('csv_export_settings', serialize($settings));
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
