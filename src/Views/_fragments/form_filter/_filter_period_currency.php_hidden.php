<?php
/** @var Views $view */
?>
<form name="form1" method="POST" class="form-inline filter">
    <input type="hidden" name="bank" value="<?php echo $view->filter_bank; ?>">
    <input type="hidden" name="period"     value="<?php echo $view->filter_period;     ?>">
    <input type="hidden" name="currency"   value="<?php echo $view->filter_currency;   ?>">
    <input type="submit" name="export" class="btn btn-sm btn-success" value="Export to XLS">
</form>