<?php
/** @var Views $view */
?>
<form id="form1" name="form1" method="POST" class="form-inline filter dynamic-filter">
    <div class="form-group form-group-bu">
        <select name="bu" class="form-control dynamic-control" data-remove=".form-group-zone, .form-group-bank, .form-group-period">
            <option value="">All BUs </option>
            <?php echo $view::selectOptions($view->all_bus, $view->filter_bu); ?>
        </select>
    </div>
    <div class="form-group form-group-zone" data-remove=".form-group-bank, .form-group-period">
        <select name="zone" class="form-control dynamic-control" data-remove=".form-group-period" >
            <option value="">All Zones </option>
            <?php echo $view::selectOptions($view->all_zones, $view->filter_zone); ?>
        </select>
    </div>
    <div class="form-group form-group-bank">
        <select name="bank" class="form-control dynamic-control" data-remove=".form-group-period" <?php echo !empty($requiredSubsidiary) ? 'required="required"' : '' ;?> autofocus>
            <option value="">All Banks</option>
            <?php echo $view::selectOptions($view->all_banks, $view->filter_bank); ?>
        </select>
    </div>
    <?php if ($view->is('all_periods') && false !== $view->all_periods) : ?>
    <div class="form-group form-group-period">
        <select name="period" class="form-control" <?php echo !empty($requiredSubsidiary) ? 'required="required"' : '' ;?>>
            <option value="">All Periods</option>
            <?php echo $view::selectOptions(array_combine($view->all_periods, $view->all_periods), $view->filter_period); ?>
        </select>
    </div>
    <?php endif; ?>
    <input type="submit" name="laod" style="display: none" value="0" class="btn btn-sm btn-success load-filter">
    <input type="submit" name="submit" class="btn btn-sm btn-success" value="Filter">
</form>