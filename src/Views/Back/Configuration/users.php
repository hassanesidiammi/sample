<?php /** @var Views $view */ ?>
<?php $baseUrl = Configuration::get('baseUrl'); ?>
<?php require_once __DIR__ . '/../../_fragments/messages_flash.php'; ?>
<h5>users details.</h5><br/>
<div class="container">
    <div class="row">
        <h6>New User</h6>
        <form method="POST" class="form-horizontal" action="<?php echo $view->url('Configuration', 'newUser');?>">
            <div class="form-group">
                <label for="user_user" class="col-sm-2 col-sm-offset-2 control-label">Login</label>
                <div class="col-sm-4">
                    <input name="user[user]" id="user_user?>" class="form-control mt-1" type="text" required="required"
                           value="<?php echo !empty($_GET['user']) ? $_GET['user'] : ''; ?>"
                    >
                </div>
            </div>
            <div class="form-group">
                <label for="user_mail" class="col-sm-2 col-sm-offset-2 control-label">Email</label>
                <div class="col-sm-4">
                    <input name="user[mail]" id="user_mail?>" class="form-control mt-1" placeholder="username@socgen.com" type="email" required="required"
                           value="<?php echo !empty($_GET['mail']) ? $_GET['mail'] : ''; ?>"
                    >
                </div>
            </div>
            <div class="form-group">
                <label for="user_pass" class="col-sm-2 col-sm-offset-2 control-label">Pass</label>
                <div class="col-sm-4">
                    <p id="user_pass" class="form-control-static" style="padding: 12px;"><?php echo UserManager::DEFAULT_PASS ?></p>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-8">
                    <button type="submit" class="btn btn-default" style="float: right;">Sign on</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="container-fluid">
    <table class="table table-striped table-responsive th_color">
        <thead >
        <tr>
            <th class="tl">
                <div class="row">
                    <div class="col-sm-3 text-center">Name</div>
                    <div class="col-sm-3 text-center">Mail</div>
                    <div class="col-sm-3 text-center">Is admin (For users)</div>
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($view->users as $user) : ?>
            <tr>
                <td class="tl" id="users_<?php echo $user['id']; ?>" style="<?php if ($view->id && $view->id == $user['id']) echo  'background-color: #bce46a38;'?>">
                    <div class="row">
                        <form method="POST" name="users[<?php echo $user['id'] ?>]" action="<?php echo $view->url('Configuration', 'updateUser', ['id' => $user['id']]);?>">
                            <div class="col-sm-3">
                                <?php echo htmlentities($user['user']); ?> (#<?php echo $user['id'] ?>)
                                <input type="hidden" name="users[<?php echo $user['id'] ?>][id]" value="<?php echo $user['id']; ?>">
                            </div>
                            <div class="col-sm-3">
                                <?php echo htmlentities($user['mail']); ?>
                            </div>
                            <div class="col-sm-3 text-left">
                                <select name="users[<?php echo $user['id'] ?>][admin_users]">
                                    <?php echo $view::selectOptions([0 => 'False', 1 => 'True',], $user['admin_users'] ? 1 : 0); ?>
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <input type="submit" class="btn btn-success" value="Save" style="float: right;">
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>