<span class="syncee-version">
    <?= Syncee_Upd::MODULE_NAME . ' ' . SYNCEE_VERSION ?>
    <?php
        if (SYNCEE_VERSION_FREE): ?>
            <a class="btn go-pro" href="#">Go Pro</a>
    <?php
        endif;
        if (Syncee_Upd::updateIsAvailable()): ?>
            <a class="btn" href="#">New Version <?= Syncee_Upd::getLatestVersion() ?> available!</a>
        <?php
        endif ?>

</span>