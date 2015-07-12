<?php
/**
 * @var $paginator Syncee_Paginator
 * @var $request_log_collection Syncee_Site_Request_Log_Collection
 * @var $request_log Syncee_Site_Request_Log
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if (!count($request_log_collection)): ?>
        <p>There aren't any requests to display.</p>
<?php
    else: ?>
        <table class="collection-table">
            <thead>
                <tr>
                    <th>Syncee Request ID</th>
                    <th>Site</th>
                    <th>Request Entity Type</th>
                    <?php /* <th>Success?</th> */ ?>
                    <th>Respnse Status Code</th>
                    <th>Response Syncee Version</th>
                    <th>Response Message</th>
                    <th>Response Errors</th>
                    <th>Date of Request</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
        <?php
            foreach ($request_log_collection as $request_log):
                $primary_key_value_map      = $request_log->getPrimaryKeyNamesValuesMap();
                $site_primary_key_value_map = $request_log->site->getPrimaryKeyNamesValuesMap();
                ?>
                <tr class="<?= $request_log->isSuccess() ? 'positive' : 'negative' ?>">
                    <td align="center"><a href="<?= Syncee_Helper::createModuleCpUrl('viewRequestLog', $primary_key_value_map) ?>"><?= $request_log->getPrimaryKeyValues(true) ?></a></td>
                    <td align="center">
                        <a href="<?= Syncee_Helper::createModuleCpUrl($request_log->site->isLocal() ? 'editLocalSite' : 'editRemoteSite', $site_primary_key_value_map) ?>">
                            <?= $request_log->site->title ?>
                        </a>
                    </td>
                    <td align="center"><?= $request_log->request_entity->getName() ?></td>
                    <?php /* <td align="center"><?= $request_log->isSuccess() ? 'Yes' : 'No' ?></td> */ ?>
                    <td align="right"><?= $request_log->code ?></td>
                    <td align="right"><?= $request_log->version ?: '<i>(N/A)</i>' ?></td>
                    <td><?= $request_log->message ?: '<i>(N/A)</i>' ?></td>
                    <td><?= $request_log->errors  ? implode('<br>', $request_log->errors) : '<i>(N/A)</i>' ?></td>
                    <td align="center"><?= Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($request_log->create_datetime) ?></td>
                    <td align="center"><a href="<?= Syncee_Helper::createModuleCpUrl('deleteRequestLog', $primary_key_value_map) ?>">Delete</a></td>
                </tr>
        <?php
            endforeach ?>
            </tbody>
        </table>
<?php
    endif;