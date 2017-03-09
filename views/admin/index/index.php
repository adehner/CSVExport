<?php
    $title = __('CSV Export');
    queue_css_file('csvexport');
    echo head(array('title' => html_escape($title), 'bodyclass' => 'csvexport'));

?>
<div id="primary">
	<?php echo flash(); ?>
	<input class="blue button" type='button' value='Export all data as CSV' onClick='window.location="<?php echo url(array('module'=>'csv-export', 'controller'=>'export', 'action'=>'csv'), 'default') ?>";'/>
</div>
<?php echo foot(); ?>
