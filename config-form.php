<?php $view = get_view(); ?>
<div id="csv-export-settings">
<h2><?php echo __('Element Sets to include'); ?></h2>

    <div class="field">
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('Check the box below to include a given element set in exports.'); ?>
            </p>
            <ul class="checkboxes">
                <?php foreach($elementSetsAll as $elementSet):?>
                  <li style="list-style-type: none">
                    <?php echo $view->formCheckbox("elementSets[{$elementSet->id}]", null, array('checked' => array_key_exists($elementSet->id, $settings['elementSets']))); ?>
                    <?php echo __($elementSet->name); ?>
                  </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
