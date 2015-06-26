<?php
/**
 * @var $syncee_remote_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if ($syncee_remote_site->isEmptyRow()): ?>
        <p>Give this remote site a name</p>
        <input type="text" name="title" value="<?= $syncee_remote_site->title ?>">
        <p>Call this remote site over HTTPS?</p>
        <select name="use_https">
            <option value="0">No</option>
            <option value="1" <?= $syncee_remote_site->use_https ? 'selected' : '' ?>>Yes</option>
        </select>
        <form method="post">
            <div class="remote-site-settings-payload-container">
                <label for="remote_site_settings_payload">Paste Settings from Remote Site Below</label>
                <textarea id="remote_site_settings_payload" name="remote_site_settings_payload"><?= !$syncee_remote_site->isEmptyRow() ? $syncee_remote_site->generateRemoteSiteSettingsPayload() : '' ?></textarea>
            </div>
            <button type="submit">Save Remote Site</button>
            <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
            <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
        </form>

        <button>Ping Site</button>
<?php
    else: ?>

<?php
    endif ?>
