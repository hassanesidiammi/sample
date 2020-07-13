<?php

/** @var Menu $menu */

$i=0;

if(false !== strpos($_SERVER['REQUEST_URI'], 'admin.php')) : ?>
    <label for="sky-tab0" style="background-color: black;">
        <span><span checked="" id="config"><a href="./index.php" style="color: grey;">Home</a></span></span>
    </label>
<?php endif; ?>
<?php  foreach ($menu->getItems() as $page => $item): ?>
    <label for="sky-tab<?php echo $i; ?>" class="<?php echo ($menu->isActive($page) ? ' active' : '')?>">
        <a href="<?php echo $menu->url($page); ?>" style="color: grey;">
            <span><span class="bg-green"><?php echo $item; ?></span></span>
        </a>
    </label>
<?php endforeach; ?>
<?php if(false === strpos($_SERVER['REQUEST_URI'], 'admin.php')) : ?>
    <label for="sky-tab8" style="background-color: black;">
        <span><span checked="" id="config"><a href="./admin.php" style="color: grey;">Config</a></span></span>
    </label>
<?php endif; ?>

<label for="sky-tab9" style="background-color: black;">
    <span><span checked="" id="top_menu"><a href="./logout.php" style="color: grey;">Sign out</a></span></span>
</label>
