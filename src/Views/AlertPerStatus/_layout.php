<?php /** @var Views $view */ ?>
<li style='height:890px !important' class="sky-tab-content-1">
    <div class="typography">
        <h1 class='page-title'>Alerts per status</h1>
    </div>
    <div class="sky-tabs sky-tabs-pos-top-left sky-tabs-anim-slide-right sky-tabs-response-to-icons">
        <input type="radio" name="sky-tabs-1" <?php echo ('index' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-1" class="sky-tab-content-1">
        <label for="sky-tab-1-1">
            <a href="<?php echo $view->url('AlertPerStatus'); ?>">
                <span><span>Monthly per entity</span></span>
            </a>
        </label>

        <input type="radio" name="sky-tabs-1" <?php echo ('monthly' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-2" class="sky-tab-content-2">
        <label for="sky-tab-1-2">
            <a href="<?php echo $view->url('AlertPerStatus', 'monthly'); ?>">
                <span><span>Monthly all entities</span></span>
            </a>
        </label>

        <input type="radio" name="sky-tabs-1" <?php echo ('sar' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-3" class="sky-tab-content-3">
        <label for="sky-tab-1-3">
            <a href="<?php echo $view->url('AlertPerStatus', 'sar'); ?>">
                <span><span>Number of SAR</span></span>
            </a>
        </label>
        <ul>
            <li class="sky-tab-content-1">
                <div class="typography">
                    <?php
                    /** @var Views $view */
                    if ('index' === $view->action){
                        $view->render();
                    }
                    ?>
                </div>
            </li>
            <li class="sky-tab-content-2">
                <div class="typography">
                    <?php
                    /** @var Views $view */
                    if ('monthly' === $view->action){
                        $view->render();
                    }
                    ?>
                </div>
            </li>
            <li class="sky-tab-content-3">
                <div class="typography">
                    <?php
                    /** @var Views $view */
                    if ('sar' === $view->action){
                        $view->render();
                    }
                    ?>
                </div>
            </li>
        </ul>
    </div>
</li>
