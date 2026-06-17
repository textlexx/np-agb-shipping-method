<?php

namespace NpAgbShippingMethod;

?>

<div class="loader-wrap-agb" style="display:none;">
    <div class="has-loader-text-agb">

        <div class="inner-has-loader-text-agb">

            <div class="loader-mess-body-agb">

                <div class="process-div">
                    <?php echo TranslatorCenter::run(
                    'Wait for several minutes, proccess in progress.');
                    ?>
                </div>

                <div class="success-div" style="display:none;">
                    <?php echo TranslatorCenter::run(
                    'Success. Delete done.');
                    ?>
                </div>

                <div class="error-div" style="display:none;">
                    <?php echo TranslatorCenter::run(
                    'Error. Delete failed.');
                    ?>
                </div>
            </div>

        </div>

        <div class="inner-has-loader-text-agb">
            
            <div class="lrdr-img-agb">
                <img src="<?php echo URL_DIR_CURRENT_PLG_NP_S_MT; ?>/img/loader.gif">

                <div class="btn-close-agb" style="display:none;">
                    <?php echo TranslatorCenter::run(
                    'Close');
                    ?>
                </div>
            </div>

        </div>

    </div>
</div><!--loader-wrap-agb-->


<div class="wrap-eco-ask-block" style="display: none;">
    <div class="eco-ask-block">

        <div class="eco-mess-body">
            <?php
            echo TranslatorCenter::run('Do you accept clean the cache?');
            ?>
        </div>

        <div class="eco-btn eco-btn-cancel">
            <?php
            echo TranslatorCenter::run('Cancel');
            ?>
        </div>

        <div class="eco-btn eco-btn-confirm">
            <?php
            echo TranslatorCenter::run('Accept');
            ?>
        </div>

    </div><!--eco-ask-block-->
</div>