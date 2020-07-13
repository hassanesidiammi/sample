<?php
/** @var Views $view */
?>
<form name="form1" method="POST" class="form-inline filter">
    <input type="hidden" name="bu" value="<?php echo $view->filter_bu; ?>">
    <input type="hidden" name="zone" value="<?php echo $view->filter_zone; ?>">
    <input type="hidden" name="bank" value="<?php echo $view->filter_bank; ?>">
    <input type="hidden" name="startdate" value="<?php echo $view->filter_start; ?>">
    <input type="hidden" name="enddate" value="<?php echo $view->filter_end; ?>">
    <input type="hidden" name="status" value="<?php echo $view->filter_status; ?>">
    <input type="submit" name="export" class="btn btn-sm btn-success" value="Export to XLS">
</form>