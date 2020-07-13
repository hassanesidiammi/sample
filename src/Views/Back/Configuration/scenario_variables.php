<?php /** @var Views $view */ ?>
<?php $baseUrl = Configuration::get('baseUrl'); ?>
<h5>
    Scenarios Variables mapping (<?php echo $view->count('variables'); ?>).

<?php if ($view->allow_add) :?>
    <a class="btn btn-success right" href="<?php echo $view->url('Configuration', 'newScenarioVariable'); ?>" style="float: right;">New Variable</a><br>
<?php endif; ?>
</h5><br/>
<?php require_once __DIR__ . '/../../_fragments/form_filter/_scenario_variables_filter.php'; ?><br>

<?php require __DIR__ . '/../../_fragments/messages_flash.php'; ?>
<div class="container-fluid">
    <div id="tooltip-ta_left"></div>
    <table class="table table-striped table-responsive th_color">
        <thead >
        <tr>
            <th class="tl">
                <div class="row">
                    <div class="col-sm-2 text-center">Scenario</div>
                    <div class="col-sm-1 text-center">Category</div>
                    <div class="col-sm-1 text-center">Variable</div>
                    <div class="col-sm-2 text-center">Internal</div>
                    <div class="col-sm-2 text-center" title="External Current" data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true">Current</div>
                    <div class="col-sm-3 text-center" title="External Expected" data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true">Expected</div>
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php if ($view->count('variables')) : ?>
            <?php $categoryNames = $view->category_names ?>
            <?php foreach ($view->variables as $variable) : ?>
                <?php
                $external = htmlentities($variable['external_expected']);
                if (empty($external) && !empty($variable['external_expected'])) {
                    $external = htmlentities(utf8_encode($variable['external_expected']));
                }
                $current = utf8_decode($variable['external_current']);
                if (empty($external) && !empty($variable['external_current'])) {
                    $external = htmlentities(utf8_encode($variable['external_current']));
                }
                ?>
                <tr>
                    <td class="tl" id="variables_<?php echo $variable['id']; ?>" style="<?php if ($view->id && $view->id == $variable['id']) echo  'background-color: #bce46a38;'?>">
                        <div class="row">
                            <form class="form-inline" method="POST" name="variables[<?php echo $variable['id'] ?>]" action="<?php echo $view->url('Configuration', 'updateScenarioVariable', ['id' => $variable['id']], 'variables_'.$variable['id']);?>">
                                <input type="hidden" name="variables[<?php echo $variable['id'] ?>][id]" value="<?php echo $variable['id']; ?>">
                                <div class="col-sm-2 form-group text-left" title="(#<?php echo $variable['scenario_id'] ?>)" data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true">
                                    <select name="variables[<?php echo $variable['id'] ?>][scenario_id]" class="form-control form-control-sm">
                                        <?php echo $view::selectOptions($view->scenarios, $variable['scenario_id']); ?>
                                    </select>
                                </div>
                                <div class="col-sm-1 form-group text-left overflow-h">
                                    <select class="form-control form-control-sm" name="variables[<?php echo $variable['id'] ?>][category_name]">
                                        <?php echo $view::selectOptions($categoryNames, $variable['category_name']); ?>
                                    </select>
                                </div>
                                <div class="col-sm-1 form-group text-left overflow-h">
                                    <input  class="form-control form-control-sm" style="width: 6em;" type="text" name="variables[<?php echo $variable['id'] ?>][variable_name]" value="<?php echo $variable['variable_name']; ?>">
                                </div>
                                <div class="col-sm-2 form-group text-left overflow-h" title="<?php echo $variable['inernal_prefix'].$variable['internal']; ?>" data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true">
                                    <input  class="form-control form-control-sm" type="text" name="variables[<?php echo $variable['id'] ?>][internal]" value="<?php echo $variable['internal']; ?>">
                                </div>
                                <div class="col-sm-2 form-group text-left overflow-h small" title="<?php echo $current; ?>" data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true">
                                    <input  class="form-control form-control-sm" type="text" name="variables[<?php echo $variable['id'] ?>][external_current]" value="<?php echo $current; ?>">
                                </div>
                                <div class="col-sm-3 form-group text-left overflow-h small" title="<?php echo $external; ?>" data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true">
                                    <input  class="form-control form-control-sm" type="text" name="variables[<?php echo $variable['id'] ?>][external_expected]" value="<?php echo $external; ?>">
                                </div>
                                <div class="col-sm-1 form-group small">
                                    <input type="submit" class="btn btn-success btn-small" value="Save" style="float: right;">
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>