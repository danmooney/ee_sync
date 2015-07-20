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
    $should_output_first_button = $paginator->getCurrentPageNumber() > 1;
    $should_output_prev_button  = $should_output_first_button;

    if ($should_output_first_button):
        echo sprintf(
            '<a href="%s">&laquo;</a>',
            Syncee_Helper::createModuleCpUrl(
                $mcp->getCalledMethod(),
                array_merge($paginator->getParams(), array('offset' => 0))
            )
        );
    endif;

    if ($should_output_prev_button):
        echo sprintf(
            '<a href="%s">&lsaquo;</a>',
            Syncee_Helper::createModuleCpUrl(
                $mcp->getCalledMethod(),
                array_merge(
                    $paginator->getParams(),
                    array(
                        'offset' => $paginator->getOffsetByPageNumber(
                            $paginator->getCurrentPageNumber() - 1
                        )
                    )
                )
            )
        );
    endif;

    if ($paginator->getTotalPages() > 1):
        for ($i = 1; $i <= $paginator->getTotalPages(); $i += 1):
            $pagination_link = Syncee_Helper::createModuleCpUrl($mcp->getCalledMethod(), array_merge($paginator->getParams(), array('offset' => $paginator->getOffsetByPageNumber($i)))) ?>
        <?php
            if ($i !== $paginator->getCurrentPageNumber()): ?>
                <a href="<?= $pagination_link ?>"><?= $i ?></a>
        <?php
            else: ?>
                <?= $i ?>
        <?php
            endif;
        endfor;
    endif;

    $should_output_next_button = $paginator->getCurrentPageNumber() < $paginator->getTotalPages();
    $should_output_last_button = $should_output_next_button;

    if ($should_output_next_button):
        echo sprintf(
            '<a href="%s">&rsaquo;</a>',
            Syncee_Helper::createModuleCpUrl(
                $mcp->getCalledMethod(),
                array_merge(
                    $paginator->getParams(),
                    array(
                        'offset' => $paginator->getOffsetByPageNumber(
                            $paginator->getCurrentPageNumber() + 1
                        )
                    )
                )
            )
        );
    endif;

    if ($should_output_last_button):
        echo sprintf(
            '<a href="%s">&raquo;</a>',
            Syncee_Helper::createModuleCpUrl(
                $mcp->getCalledMethod(),
                array_merge(
                    $paginator->getParams(),
                    array(
                        'offset' => $paginator->getOffsetByPageNumber(
                            $paginator->getTotalPages()
                        )
                    )
                )
            )
        );
    endif;
?>
</div>
