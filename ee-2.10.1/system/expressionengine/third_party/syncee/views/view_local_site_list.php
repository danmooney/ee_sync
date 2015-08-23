<?php
/**
 * @var $syncee_local_sites Syncee_Site_Collection
 * @var $syncee_local_site Syncee_Site
 * @var $paginator Syncee_Paginator
 */
require_once dirname(__FILE__) . '/../_init.php';

echo new Syncee_Table(
    new Syncee_Table_Column_Collection(array(
        new Syncee_Table_Column('Label', 'title', false, 'left', new Syncee_Table_Column_Value_Formatter_Link('editLocalSite')),
        new Syncee_Table_Column('EE Site ID', 'ee_site_id', true, 'right'),
        new Syncee_Table_Column('Allows calls from remote sites?', 'requests_from_remote_sites_enabled', true, 'center', new Syncee_Table_Column_Value_Formatter_YesNo()),
        new Syncee_Table_Column('IP Whitelist', function (Syncee_Site $row) {
            $ip_whitelist_exploded = $row->getIpWhitelistExploded();
            $ip_whitelist_count    = count($ip_whitelist_exploded);

            if ($ip_whitelist_count >= 5) {
                $html = "$ip_whitelist_count IP addresses";
            } elseif ($ip_whitelist_count) {
                $html = implode('<br>', $ip_whitelist_exploded);
            } else {
                $html = '<i>(Empty)</i>';
            }

            return $html;
        }),
        new Syncee_Table_Column('Last Call Status Result', function (Syncee_Site $row) {
            $last_request_log = $row->last_request_log;

            if ($last_request_log->isEmptyRow()) {
                $last_request_log_status = '(N/A)';
                $last_request_link_html = sprintf('<i>%s</i>', $last_request_log_status);
            } else {
                $last_request_log_status = $last_request_log->isSuccess()
                    ? 'SUCCESS'
                    : 'ERROR'
                ;

                $last_request_link_html = sprintf(
                    '<a href="%s">%s</a>',
                    Syncee_Helper::createModuleCpUrl('viewRequestLog', $last_request_log->getPrimaryKeyNamesValuesMap()),
                    $last_request_log_status
                );
            }

            return $last_request_link_html;
        }, true, 'center'),
        new Syncee_Table_Column('Syncee Site ID', 'site_id', true, 'right'),
        new Syncee_Table_Column('Edit', null, false, 'center', new Syncee_Table_Column_Value_Formatter_Link('editLocalSite')),
    )),
    $syncee_local_sites,
    null,
    $paginator
);