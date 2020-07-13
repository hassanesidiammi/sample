<?php
/** @var Views $view */
?>
<form ame="form1" method="POST" class="form-inline filter">
    <input type="hidden" name="bu" value="<?php echo $view->filter_bu; ?>">
    <input type="hidden" name="zone" value="<?php echo $view->filter_zone; ?>">
    <input type="hidden" name="bank" value="<?php echo $view->filter_bank; ?>">
    <input type="hidden" name="period" value="<?php echo $view->filter_period; ?>">
    <input type="submit" name="export" class="btn btn-sm btn-success" value="Export to XLS">
</form>