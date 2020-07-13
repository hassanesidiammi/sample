<?php /** @var Views $view */ ?>
<?php $baseUrl = Configuration::get('baseUrl'); ?>
<?php require_once __DIR__ . '/../../_fragments/messages_flash.php'; ?>
<h5>Scenarios details.</h5><br/>
<div class="container-fluid">
    <table class="table table-striped table-responsive th_color">
        <thead >
        <tr>
            <th class="tl">
                <div class="row">
                    <div class="col-sm-2 text-center">Scenario</div>
                    <div class="col-sm-1 text-center">Enabled</div>
                    <div class="col-sm-1 text-center">Frequency</div>
                    <div class="col-sm-1 text-center">Score</div>
                    <div class="col-sm-4 text-center">Description</div>
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($view->scenarios as $scenario) : ?>
            <tr>
                <td class="tl" id="scenarios<?php echo $scenario['id']; ?>" style="<?php if ($view->id && $view->id == $scenario['id']) echo  'background-color: #bce46a38;'?>">
                    <div class="row">
                        <form method="POST" name="scenarios[<?php echo $scenario['id'] ?>]" action="<?php echo $view->url('Configuration', 'updateScenario', ['id' => $scenario['id']]);?>">
                            <div class="col-sm-2">
                                <?php echo htmlentities($scenario['name']); ?> (#<?php echo $scenario['id'] ?>)
                                <input type="hidden" name="scenarios[<?php echo $scenario['id'] ?>][id]" value="<?php echo $scenario['id']; ?>">
                            </div>
                            <div class="col-sm-1 text-left">
                                <select name="scenarios[<?php echo $scenario['id'] ?>][enabled]">
                                    <?php if ($scenario['enabled']) : ?>
                                        <?php echo $view::selectOptions([0 => 'Disable', 1 => 'Enabled',], $scenario['enabled'] ? 1 : 0); ?>
                                    <?php else: ?>
                                        <?php echo $view::selectOptions([0 => 'Disabled', 1 => 'Enable',], $scenario['enabled'] ? 1 : 0); ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-1 text-center">
                                <select name="scenarios[<?php echo $scenario['id'] ?>][frequency]">
                                    <?php if (empty($scenario['frequency'])) echo '<option value="" ></option>'; ?>
                                    <?php echo $view::selectOptions(['J' => 'J', 'M' => 'M'], $scenario['frequency']); ?>
                                </select>
                            </div>
                            <div class="col-sm-1 text-center">
                                <select name="scenarios[<?php echo $scenario['id'] ?>][score]">
                                    <?php if (empty($scenario['score'])) echo '<option value="" ></option>'; ?>
                                    <?php echo $view::selectOptions($view->scores, $scenario['score']); ?>
                                </select>
                            </div>
                            <div class="col-sm-5 text-left">
                                <?php if (!empty($scenario['description_en'])) : ?>
                                    <?php echo utf8_encode($scenario['description_en']); ?>
                                <?php elseif (!empty($scenario['description_en'])) : ?>
                                    <?php echo utf8_encode($scenario['description_fr']); ?>
                                <?php elseif (!empty($scenario['description_en'])) : ?>
                                    <?php echo utf8_encode($scenario['comment']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-1">
                                <span class="btn btn-success btn-xs"  style="float: right;" data-toggle="modal" data-target="#modal_<?php echo $scenario['id']; ?>">
                                    <span class="badge-important">+</span> Details
                                </span>
                            </div>
                            <div class="col-sm-1">
                                <input type="submit" class="btn btn-success" value="Save" style="float: right;">
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>