<?php /** @var Views $view */ ?>

<div class="form-group form-group-period">
    <label class=""> FROM </label>
    <select name="startdate" class="form-control dynamic-control" data-remove=".form-group-enddate" required="required">
        <?php if ($view->count('all_starts') != 1 ) echo '<option value="">All Periods</option>'; ?>
        <?php echo $view::selectOptions($view->all_starts, $view->filter_start); ?>
    </select>
    <label class="form-group-enddate">TO </label>
    <select name="enddate" class="form-control form-group-enddate" <?php if (!empty($periodRequired)) echo 'required="required"'; ?> >
        <?php if ($view->count('all_ends') != 1 ) echo '<option value="">All Periods</option>'; ?>
        <?php echo $view::selectOptions($view->all_ends, $view->filter_end); ?>
    </select>
</div>
