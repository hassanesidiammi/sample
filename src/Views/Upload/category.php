<?php /** @var Views $view */ ?>
<div class="forms bg-gray">
    <?php if (isset($_POST['submit']) && $view->is('filename')): ?>
        <h1>File "<?php echo $view->filename; ?>" uploaded successfully.</h1>
        <h1>Import done</h1>
        <input type='button' value='Retour' onclick='history.go(-1)'>
    <?php endif; ?>
    <?php if ($view->is('fileError')) echo '<p class="text-danger">'.$view->fileError.'</p>';?>
    <form enctype='multipart/form-data' action='<?php echo $view->url('Upload', 'category');?>' method='post' class="form-inline">
        <input size='50' type='file' name='filename' value='load' class="form-control">
        <input type='submit' name='submit' value='Upload UPDATE CATEGORY' class="btn btn-default">
        <span class="control-label">`category` <small>CATEGORY_OF_CLIENTS.CSV</small></span>
    </form>
</div>
