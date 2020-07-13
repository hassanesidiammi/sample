<?php /** @var Views $view */ ?>
<?php $baseUrl = Configuration::get('baseUrl'); ?>
<?php require __DIR__ . '/../../_fragments/messages_flash.php'; ?>

<h5>NEW Scenario Variable.</h5><br/>

<div class="container-fluid">
    <div id="tooltip-ta_left"></div>
    <?php
    $variable      = $view->variable;
    $categoryNames = $view->category_names;
    $variableNames = $view->variable_names;
    $hasError      = $view->unicit_violation ? 'has-error' : '';

    ?>
    <form class="form-horizontal" method="POST" name="variable"
          action="<?php echo $view->url('Configuration', 'newScenarioVariable'); ?>">
        <?php if (!empty($variable['id'])) : ?>
            <input type="hidden" name="variable[id]" value="<?php echo $variable['id']; ?>">
        <?php endif; ?>
        <div class="form-group <?php echo $hasError; ?>">
            <label class="control-label col-sm-2" for="scenario_id">Scenario</label>
            <div class="col-sm-6">
                <select name="variable[scenario_id]" id="scenario_id" class="form-control form-control-sm">
                    <?php echo $view::selectOptions($view->scenarios, !empty($variable['scenario_id']) ? $variable['scenario_id'] : ''); ?>
                </select>
            </div>
        </div>
        <div class="form-group  <?php echo $hasError; ?>">
            <label class="control-label col-sm-2" for="category_name">Category</label>
            <div class="col-sm-6">
            <select class="form-control" id="category_name" name="variable[category_name]">
                <?php echo $view::selectOptions($categoryNames, empty($variable['category_name']) ?: $variable['category_name']); ?>
            </select>
            </div>
        </div>
        <div class="form-group  <?php echo $hasError; ?>">
            <label class="control-label col-sm-2" for="variable_name">Variable name</label>
            <div class="col-sm-6">
                <select class="form-control" id="variable_name" name="variable[variable_name]">
                    <?php echo $view::selectOptions($variableNames, empty($variable['variable_name']) ? '' : $variable['variable_name']); ?>
                </select>
            </div>
        </div>
        <div class="form-group <?php echo $view->unicit_internal_violation ? 'has-error' : ''; ?>"
             title="<?php echo !empty($variable['internal']) ? ((!empty($variable['inernal_prefix']) ? $variable['inernal_prefix'] : '') . $variable['internal']) : ''; ?>" data-toggle="tooltip"
             data-placement="top" data-container="#tooltip-ta_left" data-html="true">
            <label class="control-label col-sm-2" for="internal">Internal</label>
            <div class="col-sm-6">
            <input class="form-control" type="text" id="internal" name="variable[internal]"
                   value="<?php echo empty($variable['internal']) ? '' : $variable['internal']; ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="inernal_prefix">Internal Prefix</label>
            <div class="col-sm-6">
            <input class="form-control" type="text" id="inernal_prefix" name="variable[inernal_prefix]"
                   value="<?php echo !array_key_exists('inernal_prefix', $variable) ? 'vBHFM_' : $variable['inernal_prefix']; ?>">
            </div>
        </div>
        <div class="form-group" title="<?php echo empty($variable['external_current']) ? '' : $variable['external_current']; ?>"
             data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left" data-html="true">
            <label class="control-label col-sm-2" for="external_current">External Current</label>
            <div class="col-sm-6">
            <input class="form-control" type="text" id="external_current" name="variable[external_current]"
                   value="<?php echo empty($variable['external_current']) ? '' : $variable['external_current']; ?>">
            </div>
        </div>
        <div class="form-group" title="<?php echo empty($variable['external_expected']) ? '' : $variable['external_expected']; ?>"
             data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left" data-html="true">
            <label class="control-label col-sm-2" for="external_expected">External Expected</label>
            <div class="col-sm-6">
            <input class="form-control form-control-sm" type="text" id="external_expected" name="variable[external_expected]"
                   value="<?php echo empty($variable['external_expected']) ? '' : $variable['external_expected']; ?>">
            </div>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-success btn-small" value="Save" style="float: right;">
        </div>
    </form>
    <?php ?>
</div>