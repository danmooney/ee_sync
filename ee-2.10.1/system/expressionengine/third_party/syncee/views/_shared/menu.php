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
    'Request Log' => array(
        'method' => 'viewRequestLogList',
    ),
    'Conflicts' => array(
        'method' => 'viewConflictList'
    ),
    'Settings' => array(
        'method' => 'viewSettingsList',
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
        <a class="btn-secondary <?= in_array($data['method'], get_class_methods(get_class($mcp))) ? 'active' : 'not-active' ?>" href="<?= Syncee_Helper::createModuleCpUrl($data['method']) ?>"><?= $label ?></a>
    </li>
<?php
    endforeach ?>
</ul>
<div class="clr"></div>