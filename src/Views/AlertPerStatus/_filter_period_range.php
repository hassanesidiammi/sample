<?php /** @var Views $view */ ?>
<form id="form1" name="form1" method="POST" class="form-inline filter dynamic-filter">
    <div class="form-group form-group-bu" >
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
        <select name="bank" class="form-control dynamic-control" data-remove=".form-group-period">
            <option value="">All Banks</option>
            <?php echo $view::selectOptions($view->all_banks, $view->filter_bank); ?>
        </select>
    </div>
    <div class="form-group form-group-period">
        <label class="">&nbsp;&nbsp;FROM</label>
        <select name="startdate" class="form-control dynamic-control" data-remove=".dynamic-control-enddate">
            <option value="">All Periods</option>
            <?php echo $view::selectOptions($view->all_starts, $view->filter_start); ?>
        </select>
        <label>&nbsp;&nbsp;&nbsp;TO</label>
        <select name="enddate" class="form-control dynamic-control-enddate">
            <option value="">All Periods</option>
            <?php echo $view::selectOptions($view->all_ends, $view->filter_end); ?>
        </select>
    </div>
    <input type="submit" name="load" style="display: none" value="0" class="btn btn-sm btn-success load-filter">
    <input type="submit" name="submit" class="btn btn-sm btn-success" value="Filter">
</form>