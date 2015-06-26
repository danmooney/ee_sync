<?php
/**
 * @var $syncee_remote_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if ($syncee_remote_site->isEmptyRow()): // TODO - move this into new_remote_site.php???? ?>
        <form method="POST">
            <div class="site-settings-container">
                <h1>Add New Remote Site</h1>
            </div>
            <div class="remote-site-settings-payload-container">
                <label for="remote_site_settings_payload">Copy the settings payload from a local site on another Syncee installation and paste them into here.</label>
                <textarea id="remote_site_settings_payload" name="remote_site_settings_payload"><?= $syncee_remote_site->isEmptyRow() ? '' : $syncee_remote_site->generateRemoteSiteSettingsPayload() ?></textarea>
                <button type="submit">Save New Remote Site</button>
            </div>
            <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
            <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
        </form>
<?php
    else: ?>
        <div class="site-settings-container">
            <h1>Edit Remote Site</h1>
            <h2><?= "{$syncee_remote_site->getSiteUrl()} (EE Site ID: {$syncee_remote_site->ee_site_id})" ?></h2>
            <button>Ping Site</button>
            <br><br>
            <form method="post">
                <label for="title">Label (make it anything you want so you can identify it easier)</label>
                <input id="title" type="text" name="title" value="<?= $syncee_remote_site->title ?>">
                <br><br>
                <label for="use_https">Call this remote site over HTTPS?</label><br>
                <select id="use_https" name="use_https">
                    <option value="0">No</option>
                    <option value="1" <?= $syncee_remote_site->use_https ? 'selected' : '' ?>>Yes</option>
                </select>
                <br><br>
                <?php /*
                <div class="remote-site-settings-payload-container">
                    <label for="remote_site_settings_payload">Paste Settings from Remote Site Below</label>
                    <textarea id="remote_site_settings_payload" name="remote_site_settings_payload"><?= !$syncee_remote_site->isEmptyRow() ? $syncee_remote_site->generateRemoteSiteSettingsPayload() : '' ?></textarea>
                </div>*/ ?>
                <button type="submit">Update Remote Site Settings</button>
                <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
                <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
            </form>
        </div>
<?php
    endif ?>
<div class="clr"></div>
