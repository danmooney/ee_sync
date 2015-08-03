<?php

$dir_iterator     = new RecursiveDirectoryIterator($theme_path . '/img', RecursiveDirectoryIterator::SKIP_DOTS);
$image_subpaths   = array();

/**
 * @var $file SplFileInfo
 */
foreach (new RecursiveIteratorIterator($dir_iterator) as $file) {
    $image_subpaths[] = str_replace($theme_path, '', $file->getPathname());
}

?>
<div class="image-cache">
    <?php
        foreach ($image_subpaths as $image_subpath): ?>
            <img src="<?= $theme_url . $image_subpath ?>">
    <?php
        endforeach ?>
</div>