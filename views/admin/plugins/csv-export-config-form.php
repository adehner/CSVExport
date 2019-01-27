<?php
/**
 * @var Omeka_View $this
 * @var array $elementSets
 */
?>
<fieldset id="csv-export-output"><legend><?php echo __('Export'); ?></legend>
    <?php $metadata = array(
        'id' => __('Id'),
        'collection' => __('Collection Name'),
        'files' => __('Files'),
        'file_sources' => __('File Sources'),
        'tags' => __('Tags'),
        'elements' => __('Elements'),
    ); ?>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('csv_export_metadata',
                __('Metadata to output')); ?>
        </div>
        <div class="inputs five columns omega">
            <div class="input-block">
                <?php echo $this->formMultiCheckbox('csv_export_metadata', json_decode(get_option('csv_export_metadata'), true), null, $metadata); ?>
            </div>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('elementSets',
                __('Element Sets to include')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('Check the box below to include a given element set in exports.'); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formMultiCheckbox('csv_export_element_sets', json_decode(get_option('csv_export_element_sets'), true), null, $elementSets); ?>
            </div>
        </div>
    </div>

</fieldset>

<fieldset id="csv-export-format"><legend><?php echo __('Format'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('csv_export_header_name',
                __('Header name')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formRadio('csv_export_header_name',
                get_option('csv_export_header_name'),
                null,
                array(
                    'simple' => __('Element name only ("Title")'),
                    'full' => __('Element set name and element name ("Dublin Core : Title")'),
                )); ?>
            <p class="explanation">
                <?php echo __('This option is useful when there are duplicate element names.'); ?>
            </p>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('csv_export_no_filter',
                __('Skip Omeka filters')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formCheckbox('csv_export_no_filter', true,
                array('checked' => (bool) get_option('csv_export_no_filter'))); ?>
            <p class="explanation">
                <?php echo __('This option avoids to call the filters of the plugins (in particular fixes compatibility with the plugin SearchByMetadata).'); ?>
            </p>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('csv_export_all_elements',
                __('Return all elements, even unused')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formCheckbox('csv_export_all_elements', true,
                array('checked' => (bool) get_option('csv_export_all_elements'))); ?>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('csv_export_single_value',
                __('Return a single value in each column')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formCheckbox('csv_export_single_value', true,
                array('checked' => (bool) get_option('csv_export_single_value'))); ?>
            <p class="explanation">
                <?php echo __('If checked, each cell will have only one value, so there will be multiple columns for each element.'); ?>
                <br />
                <?php echo __('Warning: if there are many files, tags and elements by record, the number of columns can be very big and not manageable by spreadsheet software (1024 for LibreOffice, 256 for Excel 2003, 16384 for Excel 2007.'); ?>
                <?php echo __('There is no issue when the csv is managed by script or with a standard editor.'); ?>
            </p>
        </div>
    </div>
</fieldset>

<fieldset id="fieldset-csv-format"><legend><?php echo __('Csv Format'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('csv_export_delimiter',
                __('Delimiter')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formRadio('csv_export_delimiter',
                get_option('csv_export_delimiter'),
                null,
                array(
                    ',' => __('Comma ,'),
                    ';' => __('Semi-colon ;'),
                    'tab' => __('Tabulation'),
                )); ?>
            <p class="explanation">
                <?php echo __('It’s generally recommended to use the tabulation, because it is not used anywhere in metadata.'); ?>
            </p>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('csv_export_enclosure',
                __('Enclosure')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formRadio('csv_export_enclosure',
                get_option('csv_export_enclosure'),
                null,
                array(
                    '"' => __('Double quote "'),
                    '#' => __('Hash #'),
                )); ?>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('csv_export_separator',
                __('Multivalue separator')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formText('csv_export_separator', get_option('csv_export_separator'), null); ?>
            <p class="explanation">
                <?php echo __('Unlike delimiter and enclosure, it can have multiple characters.'); ?>
                <?php echo __('It is recommended to use a never used string, like " | ".'); ?>
            </p>
        </div>
    </div>
</fieldset>
