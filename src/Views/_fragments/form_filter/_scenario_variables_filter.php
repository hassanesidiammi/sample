<?php /** @var Views $view */ ?>
<form name="filter" method="POST" class="form-inline filter dynamic-filter">
    <div class="form-group form-group-scenario">
        <select name="scenario" class="form-control dynamic-control" data-remove="" <?php if ($view->isEmpty('filter_category') && $view->isEmpty('filter_variable') && $view->isEmpty('filter_like')) echo 'autofocus'; ?>>
            <option value="">All Scenario </option>
            <?php $scenarios = $view->is('all_scenarios') ? $view->all_scenarios : $view->scenarios ?>
            <?php echo $view::selectOptions($scenarios, $view->filter_scenario); ?>
        </select>
    </div>
    <div class="form-group form-group-category">
        <select name="category" class="form-control dynamic-control" data-remove="" <?php if ($view->isEmpty('filter_variable') && $view->isEmpty('filter_like')) echo 'autofocus'; ?>>
            <option value="">All Categories </option>
            <?php echo $view::selectOptions($view->all_categories, $view->filter_category); ?>
        </select>
    </div>

    <div class="form-group form-group-internal">
        <select name="internal" class="form-control dynamic-control" data-remove="">
            <option value="">All Scenario (Internal) </option>
            <?php echo $view::selectOptions($scenarios, $view->filter_internal); ?>
        </select>
    </div>
    <div class="form-group form-group-variable">
        <select name="variable" class="form-control dynamic-control" data-remove="" <?php if ($view->isEmpty('filter_like')) echo 'autofocus'; ?>>
            <option value="">All Variables</option>
            <?php echo $view::selectOptions($view->all_variables, $view->filter_variable); ?>
        </select>
    </div>
    <div class="form-group form-group-like">
        <label class="">LIKE: %<input type="text" name="like" class="form-control dynamic-control" placeholder="Current/Expected" value="<?php echo $view->filter_like; ?>" <?php if (!$view->isEmpty('filter_like')) echo 'autofocus'; ?>>%</label>
    </div>

    <input type="submit" name="submit" class="btn btn-sm btn-success" value="Filter">
</form>
