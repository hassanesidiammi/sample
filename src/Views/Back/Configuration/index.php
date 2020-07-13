<?php /** @var Views $view */ ?>
<?php $baseUrl = Configuration::get('baseUrl'); ?>
<?php require_once __DIR__ . '/../../_fragments/messages_flash.php'; ?>
<h5>Update Data Base (structure).</h5><br/>
<?php if (!$view->isEmpty('migration')) : ?>
    <?php $migration = $view->migration; ?>
<div class="container-fluid">
    <h1 style="text-align: center;">Executed Migration</h1>
    <a class="h1 btn btn-warning mb-1" style="float: right;" href="<?php echo $view->url('Configuration'); ?>">Refresh to continue!</a>
    <table class="table table-striped table-responsive table-total">
        <thead>
        <tr>
            <th style="width: 3rem;">#</th>
            <th>Description</th>
            <th style="width: 3rem;">Status</th>
            <?php if (array_key_exists('requests', $migration)): ?>
                <th>Queries</th>
            <?php endif; ?>
            <th>logs</th>
        </tr>
        </thead>
        <tr>
            <td><?php echo $migration['number']; ?></td>
            <td><?php echo !empty($migration['comment']) ? $migration['comment'] : ''; ?></td>
            <td><?php echo ($migration['status'] == 0 ? 'OK' : (!empty($migration['executed']) ? 'KO' : '')); ?></td>
            <?php if (array_key_exists('requests', $migration)): ?>
                <td><?php echo implode('<br>', $migration['requests'])?></td>
            <?php endif; ?>

            <td>
                <?php if (array_key_exists('logs', $migration)): ?>
                    <?php if (is_string($migration['logs'])): ?>
                        <?php echo $migration['logs']; ?>
                    <?php else: ?>
                        <?php foreach ($migration['logs'] as $index => $log): ?>
                            <?php echo $index.': '.htmlentities(utf8_encode($log));?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <a class="h1 btn btn-warning mb-3" style="float: right;" href="<?php echo $view->url('Configuration'); ?>" id="refresh">Refresh to continue!</a>
</div>
<?php endif; ?>
<h1 style="text-align: center;">Migrations History</h1>
    <table class="table table-striped table-responsive">
        <thead>
        <tr>
            <th style="width: 3rem;">#</th>
            <th>Description</th>
            <th>Status</th>
            <th>Executed At</th>
            <th>logs</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($view->migrations as $migration) : ?>
        <tr>
            <td><?php echo $migration['number']; ?></td>
            <td><?php echo $migration['comment']; ?></td>
            <td><?php echo ($migration['status'] == 0 ? 'OK' : ($migration['executed'] ? 'KO' : '')); ?></td>
            <td><?php echo $migration['executed']; ?></td>
            <td><?php echo (array_key_exists('logs', $migration) ? nl2br(utf8_encode($migration['logs'])) : ''); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>