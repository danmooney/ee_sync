<?php

$site_group = isset($site_group) ? $site_group : new Syncee_Site_Group();

$getSiteGroupId = function () use ($site_group) {
    return $site_group->getPrimaryKeyValues(true);
};

$resolveFunctionsInMenuData = function ($data) {
    return array_map(function ($datum) {
        return is_callable($datum) ? $datum() : $datum;
    }, $data);
};

$menu = array(
    'Site Groups' => array(
        'method' => 'viewSiteGroupList',
        'shouldShowSubmenu' => $getSiteGroupId,
        'Synchronize Channels' => array(
            'method'             => 'viewSynchronizeProfileList',
            'remove_method_prior_to_comparison_match' => true,
            'site_group_id'      => $getSiteGroupId,
            'comparator_library' => 'Syncee_Entity_Channel_Collection_Library',
            'remote_entity'      => 'Syncee_Request_Remote_Entity_Channel'
        ),
        'Synchronize Channel Fields' => array(
            'method'             => 'viewSynchronizeProfileList',
            'remove_method_prior_to_comparison_match' => true,
            'site_group_id'      => $getSiteGroupId,
            'comparator_library' => 'Syncee_Entity_Channel_Field_Collection_Library',
            'remote_entity'      => 'Syncee_Request_Remote_Entity_Channel_Field'
        ),
        'Synchronize Channel Data' => array(
            'method'             => 'viewSynchronizeProfileList',
            'remove_method_prior_to_comparison_match' => true,
            'site_group_id'      => $getSiteGroupId,
            'comparator_library' => 'Syncee_Entity_Channel_Data_Collection_Library',
            'remote_entity'      => 'Syncee_Request_Remote_Entity_Channel_Data'
        ),
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
<ul class="menu mainmenu">
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

        $has_submenu = (bool) count(array_filter($data, 'is_array'));

        $should_be_active_menu_item = in_array($menu_item_to_reference['method'], $mcp_class_methods);

        $should_show_submenu = $menu_item_to_reference !== $data || (isset($menu_item_to_reference['shouldShowSubmenu']) && $menu_item_to_reference['shouldShowSubmenu']());

        if ($should_be_active_menu_item && $should_show_submenu) {
            $active_menu_item_submenu_items = array_filter($data, 'is_array');
        }

        $additional_class = $should_be_active_menu_item ? 'active' : 'not-active';

        if ($should_show_submenu) {
            $additional_class .= ' has-submenu';
        }

        $filtered_and_resolved_menu = array_filter($resolveFunctionsInMenuData($menu_item_to_reference), 'is_scalar');
    ?>
    <li>
        <a class="btn btn-secondary <?= $additional_class ?>" href="<?= Syncee_Helper::createModuleCpUrl($filtered_and_resolved_menu) ?>"><?= $label ?></a>
    </li>
<?php
    endforeach ?>
</ul>
<?php
    if ($active_menu_item_submenu_items): ?>
        <ul class="menu submenu">
        <?php
            foreach ($active_menu_item_submenu_items as $label => $submenu):
                $filtered_and_resolved_submenu = array_filter($resolveFunctionsInMenuData($submenu), 'is_scalar');

                if (isset($filtered_and_resolved_submenu['remove_method_prior_to_comparison_match']) && $filtered_and_resolved_submenu['remove_method_prior_to_comparison_match']) {
                    unset($filtered_and_resolved_submenu['remove_method_prior_to_comparison_match']);

                    $filtered_and_resolved_submenu_sans_method = $filtered_and_resolved_submenu;
                    unset($filtered_and_resolved_submenu_sans_method['method']);
                    $should_be_active_menu_item    = Syncee_Helper::queryParamsMatchValues($filtered_and_resolved_submenu_sans_method, $mcp);
                } else {
                    $should_be_active_menu_item    = Syncee_Helper::queryParamsMatchValues($filtered_and_resolved_submenu, $mcp);
                }

                ?>
                <li>
                    <a class="btn btn-tertiary <?= $should_be_active_menu_item ? 'active' : 'not-active' ?>" href="<?= Syncee_Helper::createModuleCpUrl($filtered_and_resolved_submenu) ?>">
                        <?= $label ?>
                    </a>
                </li>
        <?php
            endforeach ?>
        </ul>
<?php
    endif ?>
<div class="clr"></div>