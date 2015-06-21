<?php
$menu = array(
    'Site Groups' => array(
        'method' => 'viewSiteGroupList',
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
<ul class="menu">
<?php
    foreach ($menu as $label => $data): ?>
    <li>
        <a class="<?= in_array($data['method'], get_class_methods(get_class($mcp))) ? 'active' : 'not-active' ?>" href="<?= Syncee_Helper::createModuleCpUrl($data['method']) ?>"><?= $label ?></a>
    </li>
<?php
    endforeach ?>
</ul>
<div class="clr"></div>