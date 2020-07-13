<?php /** @var Views $view */ ?>
<?php $baseUrl = Configuration::get('baseUrl'); ?>
<?php require_once __DIR__ . '/../../_fragments/messages_flash.php'; ?>
<h5>Banks details.</h5><br/>
<div class="container" style="margin-top: 0; background: none;">
    <table class="table table-striped table-responsive th_color">
        <thead >
        <tr>
            <th class="tl">
                <div class="row">
                    <div class="col-sm-2 text-center">Bank</div>
                    <div class="col-sm-2 text-center">Zone</div>
                    <div class="col-sm-2 text-center">Country</div>
                    <div class="col-sm-2 text-center">Devise</div>
                    <div class="col-sm-2 text-center">Devise Value</div>
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($view->banks as $bank) : ?>
            <tr>
                <td class="tl" id="banks<?php echo $bank['id']; ?>" style="<?php if ($view->id && $view->id == $bank['id']) echo  'background-color: #bce46a38;'?>">
                    <div class="row">
                        <form method="POST" name="banks[<?php echo $bank['id'] ?>]" action="<?php echo $view->url('Configuration', 'updateBank', ['id' => $bank['id']]);?>">
                            <div class="col-sm-2">
                                <?php echo htmlentities($bank['name']); ?> (#<?php echo $bank['id'] ?>)
                                <input type="hidden" name="banks[<?php echo $bank['id'] ?>][id]" value="<?php echo $bank['id']; ?>">
                            </div>
                            <div class="col-sm-2 text-center">
                                <select name="banks[<?php echo $bank['id'] ?>][zone_id]">
                                    <?php if (is_null($bank['zone_id'])) : ?><option value="" >NULL</option><?php endif; ?>
                                    <?php echo $view::selectOptions($view->zones, $bank['zone_id']); ?>
                                </select>
                                <!--                            --><?php //if (null === $bank['zone_id']) : ?><!--<span class="label label-warning" style="font-size: .7em;">NULL</span>--><?php //endif; ?>
                            </div>
                            <div class="col-sm-2 text-center">
                                <select name="banks[<?php echo $bank['id'] ?>][country_id]" class="form-control" style="width: 80%;">
                                    <?php if (is_null($bank['country_id'])) : ?><option value="" >NULL</option><?php endif; ?>
                                    <?php echo $view::selectOptions($view->countries, $bank['country_id']); ?>
                                </select>
                            </div>
                            <div class="col-sm-2 text-center">
                                <?php echo $bank['devise_code']; ?>
                            </div>
                            <div class="col-sm-2 text-center">
                                <?php echo $bank['devise_value'];?>
                            </div>
                            <div class="col-sm-2"><input type="submit" class="btn btn-success" value="Save" style="float: right;"></div>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>