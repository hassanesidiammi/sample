<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<?php /** @var Views $view */ ?>
<?php /** @var Session $session */ ?>
<h5>INSERT FILES</h5>
<div id="container" class="mt-0">
    <?php require_once 'alert.php'; ?>
    <?php require_once 'freq_hits_category.php'; ?>
    <?php require_once 'freq_hits_scenario.php'; ?>
    <?php require_once 'seuil.php'; ?>
    <?php require_once 'devise.php'; ?>
    <?php require_once 'risk_countries.php'; ?>
    <?php require_once 'category.php'; ?>
    <?php require_once 'scenario.php'; ?>
    <?php require_once 'transactions.php'; ?>
</div>

<h5>DELETE FILES</h5>
<div class="container relative mt-0 pb-3" id="container-delete-table">
    <?php if ($view->dataDeleted): ?>
        <div class="alert alert-success alert-dismissible absolute ">
            <button type="button" class="close" aria-hidden="true" data-dismiss="alert">&times;</button>
            <p>Data was deleted from table <b>`<?php echo $view->deleteTab ?>`</b>.</p>
            <ul>
                Filters
                <li>Subsidiary: <b>`<?php echo $view->deleteSubsidiary ?>`</b></li>
                <li>Period: <b>`<?php echo $view->deletePeriod ?>`</b></li>
            </ul>
        </div>
    <?php endif; ?>
    <div class="forms bg-gray">
        <?php $filterAction = $view->url('Upload', null, [], 'container-delete-table');?>
        <form class="filter form-inline" name="formFilter" method="POST" action="<?php echo $filterAction;?>">
            <div class="row mt-1 mb-1">
                <div class="col-xs-6">
                    <div class="form-group w-50">
                        <select name="table" class="form-control select-table" required="required">
                            <option value="" selected='selected'>ALL TABLES</option>
                            <?php echo $view::selectOptions($view->tables, $view->filterTab); ?>
                        </select>
                    </div>
                    <input type='submit' name='submit' class='btn btn-default w-11' value='valider'>
                </div>
            </div>
            <?php if (isset($_POST['submit']) && $view->filterTab) : ?>
            <div class="row mt-2 mb-1 table-selected">
                <div class="col-xs-6">
                    <?php $subsidiaries = $view->filterManager->getSubsidiariesFrom($view->filterTab); ?>
                    <div class="form-group w-50">
                        <select name="bank" class="form-control select-subsidiary" required="required">
                            <option value="" selected="selected">All Banks</option>
                            <?php echo $view::selectOptions($subsidiaries, $view->filterSubsidiary); ?>
                        </select>
                    </div>
                    <input type="submit" type="button" class="btn btn-default w-11" name="submit" value="CHOOSE PERIOD">
                </div>
                <?php if ($view->filterSubsidiary): ?>
                    <div class="col-xs-6 subsidiary-selected">
                        <div class="form-group w-50">
                            <select name="period" class="form-control select-period" required="required">
                                <option value="" selected="selected">All Period</option>
                                <?php $periods = $view->filterManager->getPeriodsFrom($view->filterTab, $view->filterSubsidiary); ?>
                                <?php echo $view::selectOptions($periods, $view->filterPeriod); ?>
                            </select>
                        </div>
                        <input type="submit" type="button" class="btn btn-default w-11" name="submit" value="DELETE">
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
</form>
