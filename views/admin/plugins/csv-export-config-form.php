<fieldset id="csv-export-settings">
    <legend><?php echo __('Element sets'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('elementSets',
                __('Element Sets to include')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('Check the box below to include a given element set in exports.'); ?>
            </p>
            <ul class="checkboxes">
                <?php foreach($elementSetsAll as $elementSet):?>
                  <li style="list-style-type: none">
                    <?php echo $this->formCheckbox("elementSets[{$elementSet->id}]", null, array('checked' => array_key_exists($elementSet->id, $settings['elementSets']))); ?>
                    <?php echo __($elementSet->name); ?>
                  </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
</fieldseet>

<fieldset id="csv-export-output"><legend><?php echo __('Output'); ?></legend>
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
