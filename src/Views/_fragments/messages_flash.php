<?php /** @var Session $session */ ?>
<?php if (!isset($session)) $session = Session::start(); ?>
<?php foreach (['error', 'danger', 'warning', 'info', 'success'] as $status): ?>
    <?php if ($session->hasMessageFlash($status)) : ?>
        <?php foreach ($session->getMessageFlash($status) as $message): ?>
            <div class="alert alert-dismissible alert-<?php echo $status; echo $status =='error'?' alert-danger':'' ?>">
                <button type="button" class="close" aria-hidden="true" data-dismiss="alert">&times;</button>
                <?php echo $message; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endforeach; ?>
<?php if ($session->hasMessageFlash('progress')) : ?>
    <?php foreach ($session->getMessageFlash('progress') as $progress): ?>
        <?php $refresh  = $progress['refresh']; ?>
        <?php $progress = $progress['progress']; ?>
        <div class="progress">
            <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $progress ?>"
                 aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $progress ?>%">
                <span><?php echo number_format($progress, 2) ?>% Complete</span>
            </div>
        </div>
        <?php if (!empty($refresh) && $progress < 100): ?>
            <script>
                $(function (){
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                });
            </script>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
