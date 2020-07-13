<?php /** @var Views $view */ ?>
<?php require_once __DIR__ . '/../_fragments/messages_flash.php'; ?>
<h5>AVERAGE DAILY ALERT | AVERAGE POURCENTAGE OF TREATED ALERT</h5><br/>
<?php require_once __DIR__ . '/../_fragments/form_filter/_filter_period_range.php'; ?>
<?php $mysqli = Configuration::getConnection(); ?>
<?php if (!empty($_POST) && !$view->isEmpty('counts')): ?>
    <?php $counts = $view->counts; ?>
    <div style="overflow: auto;">
        <div id="tooltip-ta_left" style="text-align: left"></div>
        <table class="table table-bordered th_color">
            <thead>
            <tr>
                <th rowspan="2">Period</th>
                <th rowspan="2">Subsidiary</th>
                <th colspan="2">Alerts</th>
                <th rowspan="2">Alerts Per Day</th>
                <th rowspan="2">Taux de cl&ocirc;ture</th>
                <th rowspan="2">All Alerts</th>
            </tr>
            <tr>
                <th>J</th>
                <th>M</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($counts as $period => $countSub): ?>
                <?php foreach ($countSub as $subsidiary => $count): ?>
                    <tr>
                        <td><?php echo $period; ?></td>
                        <td><?php echo $subsidiary; ?></td>
                        <td data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true" title="All status"><?php echo $count[0]; ?></td>
                        <td data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true" title="All status"><?php echo $count[1]; ?></td>
                        <td data-toggle="tooltip" data-placement="top" data-container="#tooltip-ta_left"  data-html="true" title="<?php echo $count[4];?> / {number of days}"><?php echo $count[2]; ?></td>
                        <td><?php echo $count[3]; ?></td>
                        <td><?php echo $count[4]; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pull-right">
        <?php require_once __DIR__ . '/_filter_period_range_hide.php'; ?>
    </div>
<?php endif; ?>
