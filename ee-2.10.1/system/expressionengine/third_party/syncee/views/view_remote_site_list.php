<?php
/**
 * @var $syncee_remote_sites Syncee_Site_Collection
 * @var $syncee_remote_site Syncee_Site
 * @var $paginator Syncee_Paginator
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if (!count($syncee_remote_sites)): ?>
        <p>You currently don't have any remote sites set up.  Click the "New Remote Site" button to set up one.</p>
<?php
    endif ?>
    <a class="btn" href="<?= Syncee_Helper::createModuleCpUrl('newRemoteSite') ?>">New Remote Site</a><br><br>
<?php
    if (count($syncee_remote_sites)):
        echo new Syncee_Table(
            new Syncee_Table_Column_Collection(array(
                new Syncee_Table_Column('Label', 'title', true, 'left', new Syncee_Table_Column_Value_Formatter_Link('editRemoteSite')),
                new Syncee_Table_Column('URL', 'site_url', true, 'left'),
                new Syncee_Table_Column('EE Site ID', 'ee_site_id', true, 'right'),
                new Syncee_Table_Column('EE Action ID', 'action_id', true, 'right'),
                new Syncee_Table_Column('Call over HTTPS?', 'use_https', true, 'center', new Syncee_Table_Column_Value_Formatter_YesNo()),
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
                            Syncee_Helper::createModuleCpUrl('viewRequestLog', $last_request_log->getPrimaryKeyNamesValuesMap()), // TODO - implement
                            $last_request_log_status
                        );
                    }

                    return $last_request_link_html;
                }, true, 'center'),
                new Syncee_Table_Column('Date Created', 'create_datetime', true, 'center', new Syncee_Table_Column_Value_Formatter_Datetime()),
                new Syncee_Table_Column('Syncee Site ID', 'site_id', true, 'right'),
                new Syncee_Table_Column('Edit', null, false, 'center', new Syncee_Table_Column_Value_Formatter_Link('editRemoteSite')),
                new Syncee_Table_Column('Delete', null, false, 'center', new Syncee_Table_Column_Value_Formatter_Link('deleteRemoteSite')),
            )),
            $syncee_remote_sites,
            null,
            $paginator
        );
    endif;