<?php
/**
 * @var $ee_sites array
 * @var $site_group Syncee_Site_Group
 * @var $form Syncee_Form_Abstract
 */
require_once dirname(__FILE__) . '/../_init.php';

if (!$site_group->isEmptyRow()): ?>

<?php
endif;

echo $form;