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
        'Outbound Request Log' => array(
            'method' => 'viewRequestLogList',
            'request_direction' => Syncee_Site_Request_Log::REQUEST_DIRECTION_OUTBOUND
        ),
        'Inbound Request Log' => array(
            'method' => 'viewRequestLogList',
            'request_direction' => Syncee_Site_Request_Log::REQUEST_DIRECTION_INBOUND
        ),
    ),
    'Conflicts' => array(
        'method' => 'viewConflictList'
    ),
    'Settings' => array(
        'method' => 'viewSettingList',
    ),
    'Help' => array(
        'method' => 'help',
        'href'   => '',
    ),
);

$active_menu_item_submenu_items = null;

?>
<ul class="menu">
<?php
    $mcp_class_methods = get_class_methods(get_class($mcp));

    foreach ($menu as $label => $data):
        if (isset($data['method'])) {
            $menu_item_to_reference = $data;
            $should_be_active_menu_item = in_array($data['method'], $mcp_class_methods);
            $has_submenu = false;
        } else {
            $menu_item_to_reference = reset($data);
            $has_submenu = true;
        }

        $should_be_active_menu_item = in_array($menu_item_to_reference['method'], $mcp_class_methods);

        if ($should_be_active_menu_item && $menu_item_to_reference !== $data) {
            $active_menu_item_submenu_items = $data;
        }

        $additional_class = $should_be_active_menu_item ? 'active' : 'not-active';

        if ($has_submenu) {
            $additional_class .= ' has-submenu';
        }

    ?>
    <li>
        <a class="btn-secondary <?= $additional_class ?>" href="<?= Syncee_Helper::createModuleCpUrl($menu_item_to_reference) ?>"><?= $label ?></a>
    </li>
<?php
    endforeach ?>
</ul>
<?php
    if ($active_menu_item_submenu_items): ?>
        <ul class="menu submenu">
        <?php
            foreach ($active_menu_item_submenu_items as $label => $submenu):
                $should_be_active_menu_item = Syncee_Helper::queryParamsMatchValues($submenu);

                ?>
                <li>
                    <a class="btn-secondary <?= $should_be_active_menu_item ? 'active' : 'not-active' ?>" href="<?= Syncee_Helper::createModuleCpUrl($submenu) ?>">
                        <?= $label ?>
                    </a>
                </li>
        <?php
            endforeach ?>
        </ul>
<?php
    endif ?>
<div class="clr"></div>