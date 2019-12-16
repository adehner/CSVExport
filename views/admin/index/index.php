<?php
    $title = __('CSV Export');
    queue_css_file('csvexport');
    echo head(array(
        'title' => html_escape($title),
        'bodyclass' => 'csvexport',
    ));
?>
<div id="primary">
    <?php echo flash(); ?>
    <input class="blue button" type='button' value='<?php echo __('Export all data as CSV'); ?>' onClick='window.location="<?php echo url(array('module' => 'csv-export', 'controller' => 'export', 'action' => 'csv'), 'default') ?>";'/>
    <p>
        <?php echo __('Params can be set in the %sconfig%s of the plugin.', sprintf('<a href="%s">', WEB_ROOT . '/admin/plugins/config?name=CSVExport'), '</a>'); ?>
    </p>
</div>
<?php echo foot();
