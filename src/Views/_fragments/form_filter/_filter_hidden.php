<?php
/** @var Views $view */
/** @var string $context */
?>
<form name="filter" method="POST" class="form-inline filter dynamic-filter">
    <input type="hidden" name="bu" value="<?php echo $view->filter_bu; ?>">
    <input type="hidden" name="zone" value="<?php echo $view->filter_zone; ?>">
    <input type="hidden" name="bank" value="<?php echo $view->filter_bank; ?>">
    <input type="hidden" name="startdate" value="<?php echo $view->filter_start; ?>">
    <input type="hidden" name="enddate" value="<?php echo $view->filter_end; ?>">
    <input type="hidden" name="score" value="<?php echo $view->filter_score; ?>">
    <input type="hidden" name="export_context" value="<?php echo $context; ?>">
    <input type="submit" name="export" class="btn btn-sm btn-success" value="Export to XLS">
</form>
