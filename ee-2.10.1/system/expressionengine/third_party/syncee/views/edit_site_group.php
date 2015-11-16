<?php
/**
 * @var $ee_sites array
 * @var $site_group Syncee_Site_Group
 * @var $form Syncee_Form_Abstract
 */
require_once dirname(__FILE__) . '/../_init.php';

if (!$site_group->isEmptyRow()): // TODO - decide whether or not to add the 'synchronize with remote sites' button right here ?>
    <?php /*
    <a class="btn btn-secondary" href="<?= Syncee_Helper::createModuleCpUrl('synchronizeSiteGroup', $site_group->getPrimaryKeyNamesValuesMap()) ?>">Synchronize with Remote Sites</a>
    <br><br> */ ?>
<?php
endif;

echo $form;

/*
if (!$site_group->isNew()): ?>
    <div style="float: right;">
        <a href="<?= Syncee_Helper::createModuleCpUrl('synchronizeSiteGroup', $site_group->getPrimaryKeyNamesValuesMap()) ?>" class="btn-secondary">Synchronize Site Group With Remote Sites</a>
    </div>
<?php
endif ?>*/
