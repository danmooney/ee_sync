<?php
/**
 * @var $syncee_site_groups Syncee_Site_Group_Collection
 * @var $syncee_site_group Syncee_Site_Group
 * @var $local_syncee_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if (!count($syncee_site_groups)): ?>
        <p>You currently don't have any site groups set up.  Click the "New Site Group" button to set up one.</p>
<?php
    endif ?>
    <a class="btn" href="<?= Syncee_Helper::createModuleCpUrl('newSiteGroup') ?>">New Site Group</a><br><br>
<?php
    if (count($syncee_site_groups)): ?>
        <table class="collection-table">
            <thead>
                <tr>
                    <th>Site Group Name</th>
                    <th>Local Site</th>
                    <th>Remote Sites</th>
                    <th>Date Created</th>
                    <th>Date Last Synchronized</th>
                    <th>Syncee Site Group ID</th>
                    <th>Synchronize</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
    <?php
        foreach ($syncee_site_groups as $syncee_site_group):
            $site_group_primary_key_value_map = array($syncee_site_group->getPrimaryKeyNames(true) => $syncee_site_group->getPrimaryKeyValues(true));
            ?>
            <tr>
                <td><a href="<?= Syncee_Helper::createModuleCpUrl('editSiteGroup', $site_group_primary_key_value_map) ?>"><?= $syncee_site_group->title ?></a></td>
                <td>
                    <?php
                    $local_syncee_site                       = $syncee_site_group->local_site;
                    $local_syncee_site_primary_key_value_map = array($local_syncee_site->getPrimaryKeyNames(true) => $local_syncee_site->getPrimaryKeyValues(true));
                    if ($local_syncee_site->isEmptyRow()) {
                        echo 'CONFLICT - NONE';
                    } else {
                        $local_ee_site = $local_syncee_site->getCorrespondingLocalEeSiteRow();
                        echo vsprintf('<a href="%s">%s</a>', array(
                            Syncee_Helper::createModuleCpUrl('editLocalSite', $local_syncee_site_primary_key_value_map),
                            $local_ee_site->site_label
                        ));
                    }
                    ?>
                </td>
                <?php /* <td align="right"><?= count($syncee_site_group->getSiteCollection()->filterByCondition(array('is_local' => false))) ?></td> */ ?>
                <td align="center">
                    <?php
                        /**
                         * @var $remote_site Syncee_Site
                         */
                        $remote_site_collection = $syncee_site_group->getSiteCollection()->filterByCondition(array('is_local' => false));

                        if (!count($remote_site_collection)):
                            echo '<i>(No Remote Sites Assigned)</i>';
                        else:
                            $remote_site_html = array();

                            foreach ($remote_site_collection as $remote_site):
                                $remote_site_html[] = sprintf(
                                    '<a href="%s">%s</a>',
                                    Syncee_Helper::createModuleCpUrl('editRemoteSite', array('site_id' => $remote_site->getPrimaryKeyValues(true))),
                                    $remote_site->title
                                );
                            endforeach;

                            echo implode('<br>', $remote_site_html);
                        endif;
                    ?>
                </td>
                <td align="center"><?= Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($syncee_site_group->create_datetime) ?></td>
                <td align="center"><?= $syncee_site_group->last_sync_datetime ? Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($syncee_site_group->create_datetime) : '<i>Never</i>' ?></td>
                <td align="right"><?= $syncee_site_group->getPrimaryKeyValues(true) ?></td>
                <td align="center"><a href="<?= Syncee_Helper::createModuleCpUrl('viewSiteGroup', $site_group_primary_key_value_map) ?>">Synchronize with Remote Sites</a></td>
                <td align="center"><a href="<?= Syncee_Helper::createModuleCpUrl('editSiteGroup', $site_group_primary_key_value_map) ?>">Edit</a></td>
                <td align="center"><a href="<?= Syncee_Helper::createModuleCpUrl('deleteSiteGroup', $site_group_primary_key_value_map) ?>">Delete</a></td>
            </tr>
    <?php
        endforeach ?>
            </tbody>
        </table>
<?php
    endif;