<?php /** @var Views $view */?>
<?php require_once __DIR__ . '/../_fragments/messages_flash.php'; ?>
<h5>Number of alerts per status</h5><br/>
<?php require_once __DIR__ . '/../_fragments/form_filter/_filter_period_range.php'; ?>

<?php if (!$view->isEmpty('counts')): ?>
    <?php
    $counts  = $view->counts;
    $totals = $view->totals;
    ?>
<?php if (!$view->isEmpty('periods_missing')): ?>
        <div class="small text-danger panel-group col-md-10 col-md-offset-1 mt-1" id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-warning">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            (*) Attention : Il manque des mois
                        </a>
                    </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                        <ul>
                            <?php foreach ($view->periods_missing as $subsidiary => $missing) : ?>
                                <li style="font-size: 1.3em;"><b><?php echo $subsidiary; ?>:</b> <?php echo implode(', ', $missing); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
<?php endif; ?>
    <table class="table table-bordered" id='table1'>
        <th class='bold' id='th_color'>Subsidiary</th>
        <th id="th_color"><?php
            echo implode('</th><th id="th_color">', $view->all_status); ?>
        </th>

        <th style="background-color: #000000;color:#ffffff;text-align:center">TOTAL (without techn.)</th>
        <?php foreach ($view->subsidiaries as $subsidiary) {
            echo "<tr><td>" . $subsidiary . "</td>";
            foreach (array_keys($view->all_status) as $status) {
                if (array_key_exists($status, $counts[$subsidiary]) && array_key_exists('subsidiary', $counts[$subsidiary][$status])) {
                    echo '<td>' . $counts[$subsidiary][$status]['subsidiary'].'</td>';
                }else {
                    echo '<td>NULL</td>';
                }
            }
            echo "<td style='background-color: #000000;color:#ffffff;text-align:center'>" .$totals['subsidiary'][$subsidiary]. "</td></tr>";
        }

        if(count($view->subsidiaries) > 1) {
            echo '<tr><td style="background-color: #000000;color:#ffffff;text-align:center">TOTAL</td>';
            foreach (array_keys($view->all_status )as $status) {
                echo '<td style="background-color: #000000;color:#ffffff;text-align:center">'.$totals['status'][$status].'</td>';
            }
            echo '<td style="background-color: #000000;color:#ffffff;text-align:center">'.array_sum($totals['status']).'</td>';
            echo '</tr>';
        }
        ?>
    </table>
    <div class="pull-right">
        <?php require __DIR__ . '/_filter_period_range_hide.php'; ?>
    </div>
<?php endif;?>
