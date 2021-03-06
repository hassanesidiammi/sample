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
    <div class="form-group form-group-zone">
        <select name="zone" class="form-control dynamic-control" data-remove=".form-group-bank, .form-group-period">
            <option value="">All Zones </option>
            <?php echo $view::selectOptions($view->all_zones, $view->filter_zone); ?>
        </select>
    </div>
    <div class="form-group form-group-bank">
        <select name="bank" class="form-control dynamic-control" data-remove="<?php echo (!empty($subsidiaryRequired) ? '.form-group-period' : ''); ?>" <?php
        if (!empty($subsidiaryRequired)) echo 'required="required"'
        ?> autofocus>
            <option value="">All Banks</option>
            <?php echo $view::selectOptions($view->all_banks, $view->filter_bank); ?>
        </select>
    </div>
    <div class="form-group form-group-period">
        <select name="period" class="form-control" <?php
        if (!empty($periodRequired)) echo 'required="required"'
        ?>>
            <?php if(isset($firstPeriodOption)) :?>
                <option value="<?php echo $firstPeriodOption[0]; ?>"><?php echo $firstPeriodOption[1]; ?></option>
            <?php else: ?>
                <option value="">All Periods</option>
            <?php endif; ?>
            <?php echo $view::selectOptions(array_combine($view->all_periods, $view->all_periods), $view->filter_period); ?>
        </select>
    </div>

    <div class="form-group form-group-all_currency">
        <select name="currency" class="form-control">
            <?php echo $view::selectOptions($view->all_currencies, $view->filter_currency); ?>
        </select>
    </div>
    <input type="submit" name="load" style="display: none;" class="btn btn-sm btn-success hidden" value="" class="load-filter">
    <input type="submit" name="submit" class="btn btn-sm btn-success" value="Filter">
</form>