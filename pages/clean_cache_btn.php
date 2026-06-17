<?php

namespace NpAgbShippingMethod; 

?>

<div class="block-abslute-pose-agb">

    <h1 class="group-h1">
        <?php
        echo TranslatorCenter::run('Clean Cache AGB');
        ?>
    </h1>

    <div class="btn-clean-cache">
        <span class="cache1">
        <?php
        echo TranslatorCenter::run('Clean Main Cache');
        ?>
        </span>
    </div>

    <div class="btn-clean-cache">
        <span class="cache2">
        <?php
        echo TranslatorCenter::run('Clean Product Filter Cache');
        ?>
        </span>
    </div>

</div><!--block-abslute-pose-agb-->

<?php
Files_Include_Functions::include_template_php('footer-loader-1');
?>