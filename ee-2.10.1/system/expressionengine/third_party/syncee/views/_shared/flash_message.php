<?php
/**
 * @var $flash_message string
 * @var $flash_message_type string
 */
if (!isset($flash_message)) {
    return;
}
?>
<div class="flash-message-container">
    <div class="flash-message-type flash-message-type-<?= $flash_message_type ?>">
        <table>
            <tbody>
                <tr>
                    <td>
                        <?= ucwords($flash_message_type) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="flash-message">
        <?= $flash_message ?>
    </div>
    <a class="btn btn-close" title="Close this message"></a>
</div>