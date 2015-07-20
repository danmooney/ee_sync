<?php
/**
 * @var $ee_sites array
 * @var $syncee_site_group Syncee_Site_Group
 * @var $form Syncee_Form_Abstract
 */
require_once dirname(__FILE__) . '/../_init.php';

echo $form;

/*
if (!$syncee_site_group->isNew()): ?>
    <div style="float: right;">
        <a href="<?= Syncee_Helper::createModuleCpUrl('synchronizeSiteGroup', $syncee_site_group->getPrimaryKeyNamesValuesMap()) ?>" class="btn-secondary">Synchronize Site Group With Remote Sites</a>
    </div>
<?php
endif ?>*/
