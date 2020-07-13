<?php /** @var Views $view */ ?>
<?php require_once __DIR__ . '/../_fragments/messages_flash.php'; ?>
<h5>CLOSED with SAR</h5><br/>
<?php require_once __DIR__ . '/../_fragments/form_filter/_filter_period_range.php'; ?>
<?php $mysqli = Configuration::getConnection(); ?>
<?php if (!empty($_POST) && !$view->isEmpty('counts')): ?>
    <?php $counts = $view->counts; ?>
    <div style="overflow: auto;">
        <table class="table table-bordered th_color">
            <thead>
            <tr>
                <th>Subsidiary</th>
                <th>Number of SAR</th>
                <th>All Alerts</th>
                <th>SAR Rate</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($counts as $subsidiary => $count): ?>
                <tr>
                    <td><?php echo $subsidiary; ?></td><td><?php echo implode('</td><td>', $count['T']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pull-right">
        <?php require_once __DIR__ . '/_filter_period_range_hide.php'; ?>
    </div>
<?php endif; ?>
