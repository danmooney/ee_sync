<?php
/**
 * @var $paginator Syncee_Paginator
 * @var $request_log_collection Syncee_Site_Request_Log_Collection
 * @var $request_log Syncee_Site_Request_Log
 * @var $paginator Syncee_Paginator
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if (!count($request_log_collection)): ?>
        <p>There aren't any requests to display.</p>
<?php
    else:
        echo new Syncee_Table(
            new Syncee_Table_Column_Collection(array(
                new Syncee_Table_Column('Syncee Request ID', 'request_log_id', true, 'center', new Syncee_Table_Column_Value_Formatter_Link('viewRequestLog')),
                new Syncee_Table_Column('Site', function (Syncee_Site_Request_Log $request_log) {
                    return sprintf(
                        '<a href="%s">%s</a>',
                        Syncee_Helper::createModuleCpUrl($request_log->site->isRemote() ? 'editRemoteSite' : 'editLocalSite', $request_log->site->getPrimaryKeyNamesValuesMap()),
                        $request_log->site->title
                    );
                }, false, 'center'),
                new Syncee_Table_Column('Date of Request', 'create_datetime', true, 'center', new Syncee_Table_Column_Value_Formatter_Datetime()),
                new Syncee_Table_Column('Request Entity Type', function (Syncee_Site_Request_Log $request_log) {
                    return $request_log->request_entity->getName();
                }),
                new Syncee_Table_Column('Response Status Code', 'code', true, 'right'),
                new Syncee_Table_Column('Response Syncee Version', 'version', true, 'right'),
                new Syncee_Table_Column('Response Message', 'message', true, 'left'),
                new Syncee_Table_Column('Response Errors', 'errors', false, 'left', new Syncee_Table_Column_Value_Formatter_List()),
                new Syncee_Table_Column('Delete', null, false, 'center', new Syncee_Table_Column_Value_Formatter_Link('deleteRequestLog')),
            )),
            $request_log_collection,
            new Syncee_Table_Row_Formatter_PositiveNegative(function (Syncee_Site_Request_Log $request_log) {
                return $request_log->isSuccess();
            }),
            $paginator
        );
    endif;