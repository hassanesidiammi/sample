<?php /** @var Views $view */ ?>
<li style='height:890px !important' class="sky-tab-content-1">
    <div class="typography">
        <h1 class='page-title'>Administration</h1>
    </div>
    <div class="sky-tabs sky-tabs-pos-top-left sky-tabs-anim-slide-right sky-tabs-response-to-icons">
        <input type="radio" name="sky-tabs-1" <?php echo ('index' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-1" class="sky-tab-content-1">
        <label for="sky-tab-1-1">
            <a href="<?php echo $view->url('Configuration'); ?>">
                <span><span>Upgrade DB</span></span>
            </a>
        </label>
        <input type="radio" name="sky-tabs-1" <?php echo ('mapping' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-2" class="sky-tab-content-2">
        <label for="sky-tab-1-2">
            <a href="<?php echo $view->url('Configuration', 'mapping'); ?>">
                <span><span>Mapping (Subsidiary)</span></span>
            </a>
        </label>
        <input type="radio" name="sky-tabs-1" <?php echo ('mappingBank' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-3" class="sky-tab-content-3">
        <label for="sky-tab-1-3">
            <a href="<?php echo $view->url('Configuration', 'mappingBank'); ?>">
                <span><span>Mapping (Bank)</span></span>
            </a>
        </label>
        <input type="radio" name="sky-tabs-1" <?php echo ('scenarios' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-4" class="sky-tab-content-4">
        <label for="sky-tab-1-4">
            <a href="<?php echo $view->url('Configuration', 'scenarios'); ?>">
                <span><span>Scenarios</span></span>
            </a>
        </label>
        <input type="radio" name="sky-tabs-1" <?php echo ('scenarioVariables' === $view->action || 'newScenarioVariable' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-5" class="sky-tab-content-5">
        <label for="sky-tab-1-5">
            <a href="<?php echo $view->url('Configuration', 'scenarioVariables'); ?>">
                <span><span>Scenario Variables</span></span>
            </a>
        </label>
        <input type="radio" name="sky-tabs-1" <?php echo ('users' === $view->action || 'newUser' === $view->action ? 'checked' : ''); ?> id="sky-tab-1-6" class="sky-tab-content-6">
        <label for="sky-tab-1-5">
            <a href="<?php echo $view->url('Configuration', 'users'); ?>">
                <span><span>USERS</span></span>
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
                    if ('mapping' === $view->action){
                        $view->render();
                    }
                    ?>
                </div>
            </li>
            <li class="sky-tab-content-3">
                <div class="typography">
                    <?php
                    /** @var Views $view */
                    if ('mappingBank' === $view->action){
                        $view->render();
                    }
                    ?>
                </div>
            </li>
            <li class="sky-tab-content-4">
                <div class="typography">
                    <?php
                    /** @var Views $view */
                    if ('scenarios' === $view->action){
                        $view->render();
                    }
                    ?>
                </div>
            </li>
            <li class="sky-tab-content-5">
                <div class="typography">
                    <?php
                    /** @var Views $view */
                    if ('scenarioVariables' === $view->action || 'newScenarioVariable' === $view->action){
                        $view->render();
                    }
                    ?>
                </div>
            </li>
            <li class="sky-tab-content-6">
                <div class="typography">
                    <?php
                    /** @var Views $view */
                    if ('users' === $view->action || 'newUser' === $view->action){
                        $view->render();
                    }
                    ?>
                </div>
            </li>
        </ul>
    </div>
</li>

<?php if ('scenarios' === $view->action): ?>
<?php foreach ($view->scenarios as $scenario) : ?>
    <div class="modal fade" id="modal_<?php echo $scenario['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modal_label<?php echo $scenario['id']; ?>">
        <div class="modal-dialog modal-lg" role="document">

            <form method="POST" name="scenarios[<?php echo $scenario['id'] ?>]" action="<?php echo $view->url('Configuration', 'updateScenario', ['id' => $scenario['id']]);?>" class="form-horizontal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modal_label<?php echo $scenario['id'] ?>"><?php echo $scenario['name']; ?> (#<?php echo $scenario['id']; ?>)</h4>
                        <input type="hidden" name="scenarios[<?php echo $scenario['id'] ?>][id]" value="<?php echo $scenario['id']; ?>">
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="enabled<?php echo $scenario['id']; ?>" class="col-sm-2 control-label"></label>
                            <div class="col-sm-10">
                                <select id="enabled<?php echo $scenario['id']; ?>" name="scenarios[<?php echo $scenario['id'] ?>][enabled]" class="form-control w-10">
                                    <?php if ($scenario['enabled']) : ?>
                                        <?php echo $view::selectOptions([0 => 'Disable', 1 => 'Enabled',], $scenario['enabled'] ? 1 : 0); ?>
                                    <?php else: ?>
                                        <?php echo $view::selectOptions([0 => 'Disabled', 1 => 'Enable',], $scenario['enabled'] ? 1 : 0); ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="Frequency<?php echo $scenario['id']; ?>" class="col-sm-2 control-label">Frequency</label>
                            <div class="col-sm-10">
                                <select name="scenarios[<?php echo $scenario['id'] ?>][frequency]" class="form-control w-6">
                                    <?php if (empty($scenario['frequency'])) echo '<option value="" ></option>'; ?>
                                    <?php echo $view::selectOptions(['J' => 'J', 'M' => 'M'], $scenario['frequency']); ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="Score<?php echo $scenario['id']; ?>" class="col-sm-2 control-label">Score</label>
                            <div class="col-sm-10">
                                <select name="scenarios[<?php echo $scenario['id'] ?>][score]" class="form-control w-6">
                                    <?php if (empty($scenario['score'])) echo '<option value="" ></option>'; ?>
                                    <?php echo $view::selectOptions($view->scores, $scenario['score']); ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description_en<?php echo $scenario['id']; ?>" class="col-sm-2 control-label">Description (EN)</label>
                            <div class="col-sm-10">
                                    <textarea name="scenarios[<?php echo $scenario['id'] ?>][description_en]" id="description_en<?php echo $scenario['id']; ?>" class="form-control autofocus" rows="3" ><?php
                                        echo utf8_encode($scenario['description_en']); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description_fr<?php echo $scenario['id']; ?>" class="col-sm-2 control-label">Description (FR)</label>
                            <div class="col-sm-10">
                                    <textarea name="scenarios[<?php echo $scenario['id'] ?>][description_fr]" id="description_fr<?php echo $scenario['id']; ?>" class="form-control" rows="3"><?php
                                        echo utf8_encode($scenario['description_fr']); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment<?php echo $scenario['id']; ?>" class="col-sm-2 control-label">Comment</label>
                            <div class="col-sm-10">
                                    <textarea name="scenarios[<?php echo $scenario['id'] ?>][comment]" id="comment<?php echo $scenario['id']; ?>" class="form-control" rows="3"><?php
                                        echo utf8_encode($scenario['comment']); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save changes</button>
                        <span type="button" class="btn btn-danger" data-dismiss="modal">Close</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
<?php endif; ?>