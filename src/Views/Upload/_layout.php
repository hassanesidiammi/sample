<?php /** @var Views $view */?>
<li style='height:890px !important' class="sky-tab-content-2">
    <div class="typography">
        <h1 class='page-title'>UPLOAD</h1>
    </div>
    <div class="sky-tabs sky-tabs-pos-top-left sky-tabs-anim-slide-right sky-tabs-response-to-icons">
        <input type="radio" name="sky-tabs-2" checked id="sky-tab-2-1" class="sky-tab-content-1">
        <label for="sky-tab-2-1"><span><span>MANAGEMENT DATABASE</span></span></label>
        <ul>
            <li class="sky-tab-content-1">
                <div class="typography">
                    <?php require_once __DIR__ . '/../_fragments/messages_flash.php'; ?>
                    <?php /** @var Views $view */ $view->render(); ?>
                </div>
            </li>
        </ul>
    </div>
</li>