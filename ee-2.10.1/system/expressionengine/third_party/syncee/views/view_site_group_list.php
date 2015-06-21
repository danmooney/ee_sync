<?php

require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if (!count($syncee_site_groups)): ?>
        <p>You currently don't have any site groups setup.  Click the "New Site Group" button to set up one.</p>
<?php
    endif ?>
    <a class="btn" href="<?= Syncee_Helper::createModuleCpUrl('newSiteGroup') ?>">New Site Group</a>
<?php
    if (count($syncee_site_groups)): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Local Site</th>
                    <th># of Remote Sites in Group</th>
                    <th>Date Created</th>
                    <th>Date Last Synchronization</th>
                    <th>ID</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
    <?php
        /**
        * @var $syncee_site_group Syncee_Site_Group
        */
        foreach ($syncee_site_groups as $syncee_site_group): ?>
            <tr>
                <td><a href="#"><?= $syncee_site_group->title ?></a></td>
                <td><?= 'TODO - Local Site' ?></td>
                <td><?= count($syncee_site_group->getSiteCollection()) ?></td>
                <td><?= Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($syncee_site_group->create_datetime) ?></td>
                <td><?= $syncee_site_group->last_sync_datetime ? Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($syncee_site_group->create_datetime) : '<i>Never</i>' ?></td>
                <td><?= $syncee_site_group->getPrimaryKeyValues(true) ?></td>
                <td><a href="<?= Syncee_Helper::createModuleCpUrl('editSiteGroup', array('site_group_id' => $syncee_site_group->getPrimaryKeyValues(true))) ?>">Edit</a></td>
                <td><a href="<?= Syncee_Helper::createModuleCpUrl('deleteSiteGroup', array('site_group_id' => $syncee_site_group->getPrimaryKeyValues(true))) ?>">Delete</a></td>
            </tr>
    <?php
        endforeach ?>
            </tbody>
        </table>
<?php
    endif;