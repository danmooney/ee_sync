<?php
$menu = array(
    'Site Groups' => array(
        'method' => 'viewSiteGroupList'
    ),
    'Local Sites' => array(
        'method' => 'viewLocalSiteList'
    ),
    'Remote Sites' => array(
        'method' => 'viewRemoteSiteList'
    ),
    'Help' => array(
        'method' => 'help'
    ),
);

$current_method = ee()->input->get('method');

?>
<?= false; /*Syncee_Upd::MODULE_NAME . ' ' . Syncee_Upd::VERSION*/ ?>
<ul class="menu">
<?php
    foreach ($menu as $label => $data): ?>
    <li>
        <a class="<?= $current_method === $data['method'] ? 'active' : 'not-active' ?>" href="<?= Syncee_Helper::createModuleCpUrl($data['method']) ?>"><?= $label ?></a>
    </li>
<?php
    endforeach ?>
</ul>
<div class="clr"></div>