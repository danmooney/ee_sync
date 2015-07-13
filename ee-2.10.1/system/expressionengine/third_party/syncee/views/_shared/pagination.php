<?php
/**
 * @var $paginator Syncee_Paginator
 * @var $mcp       Syncee_Mcp_Abstract
 */

if ($paginator->getTotalPages() === 1) {
    return;
}
?>
<div class="pagination">
<?php
    for ($i = 1; $i <= $paginator->getTotalPages(); $i += 1):
        $pagination_link = Syncee_Helper::createModuleCpUrl($mcp->getCalledMethod(), array_merge($_GET, array('offset' => $paginator->getOffsetByPageNumber($i)))) ?>
    <?php
        if ($i !== $paginator->getCurrentPageNumber()): ?>
            <a href="<?= $pagination_link ?>"><?= $i ?></a>
    <?php
        else: ?>
            <?= $i ?>
    <?php
        endif;
    endfor ?>
</div>
