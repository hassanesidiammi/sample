<?php /** @var Views $view */ ?>
<form name="filter" method="POST" class="form-inline filter dynamic-filter">
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
        <select name="bank" class="form-control dynamic-control" data-remove=".form-group-period, .form-group-score" autofocus>
            <option value="">All Banks</option>
            <?php echo $view::selectOptions($view->all_banks, $view->filter_bank); ?>
        </select>
    </div>
    <?php require '__period_range.php'; ?>
    <div class="form-group form-group-score">
        <label>Scores </label>
        <select name="score">
            <?php echo $view::selectOptions(array_combine($view->all_scores, $view->all_scores), $view->filter_score); ?>
        </select>
    </div>
    <input type="submit" name="load" class="hidden btn btn-sm btn-success" value="0" class="load-filter">
    <input type="submit" name="submit" class="btn btn-sm btn-success" value="Filter">
</form>
