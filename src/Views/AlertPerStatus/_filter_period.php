<?php
/** @var Views $view */
?>
<form id="form1" name="form1" method="POST" class="form-inline filter">
    <div class="form-group">
        <select name="bank" class="form-control">
            <option value="">All Banks</option>
            <?php echo $view::selectOptions($view->all_banks, $view->filter_bank); ?>
        </select>
    </div>
    <div class="form-group">
        <select name="period" class="form-control">
            <option value="">All Periods</option>
            <?php echo $view::selectOptions(array_combine($view->all_periods, $view->all_periods), $view->filter_period); ?>
        </select>
    </div>
    <input type="submit" name="submit" class="btn btn-sm btn-success" value="Filter">
    <input type="submit" name="export" class="btn btn-sm btn-success" value="Export to XLS">
</form>