<?php /** @var Views $view */ ?>
<?php $baseUrl = Configuration::get('baseUrl'); ?>
<?php require_once __DIR__ . '/../../_fragments/messages_flash.php'; ?>
<h5>Subsidiaries details.</h5><br/>
    <div class="container-fluid">
        <table class="table table-striped table-responsive th_color">
            <thead >
            <tr>
                <th class="tl">
                    <div class="row">
                        <div class="col-sm-4 col-md-3">Subsidiary</div>
                        <div class="col-md-2 col-md-2 text-center">Enabled</div>
                        <div class="col-md-4 col-md-3 text-center">Bank</div>
                        <div class="col-md-2 col-md-3 text-center">Zone</div>
                    </div>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($view->subsidiaries as $subsidiary) : ?>
            <tr>
                <td class="tl" id="subsidiary<?php echo $subsidiary['id']; ?>" style="<?php if ($view->id && $view->id == $subsidiary['id']) echo  'background-color: #bce46a38;'?>">
                    <div class="row">
                        <form method="POST" name="subsidiaries[<?php echo $subsidiary['id'] ?>]" action="<?php echo $view->url('Configuration', 'updateSubsidiary', ['id' => $subsidiary['id']]);?>">
                            <div class="col-sm-4 col-md-3">
                                <?php echo htmlentities($subsidiary['name']); ?> (#<?php echo $subsidiary['id'] ?>)
                                <input type="hidden" name="subsidiaries[<?php echo $subsidiary['id'] ?>][id]" value="<?php echo $subsidiary['id']; ?>">
                            </div>
                            <div class="col-md-2 text-center">
                                <select name="subsidiaries[<?php echo $subsidiary['id'] ?>][enabled]">
                                <?php if ($subsidiary['enabled']) : ?>
                                    <?php echo $view::selectOptions([0 => 'Disable', 1 => 'Enabled',], $subsidiary['enabled'] ? 1 : 0); ?>
                                <?php else: ?>
                                    <?php echo $view::selectOptions([0 => 'Disabled', 1 => 'Enable',], $subsidiary['enabled'] ? 1 : 0); ?>
                                <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4 col-md-3 text-center">
                                <select name="subsidiaries[<?php echo $subsidiary['id'] ?>][bank_id]">
                                    <?php echo $view::selectOptions($view->banks, $subsidiary['bank_id']); ?>
                                </select>
                            </div>
                            <div class="col-sm-2 col-md-3"><?php echo htmlentities($subsidiary['zone']); ?></div>
                            <div class="col-sm-1"><input type="submit" class="btn btn-success" value="Save"></div>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>