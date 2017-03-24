<div class="user-info"><?php echo $this->partial("layout/user-info.php"); ?></div>
<ul>
    <?php if (key_exists('session', $_SESSION)): ?>
        <li><a href="/" class="index">Server</a></li>
        <li><a href="/user" class="user">Người dùng</a></li>
        <li>
            <a href="/dictionary?type=abbrev" class="dictionary">Từ điển</a>
            <ul class="type-action">
                <li><a href="/dictionary?type=abbrev" class="abbrev">Từ điển viết tắt</a></li>
                <li><a href="/dictionary?type=loan" class="loan">Từ điển vay mượn</a></li>
            </ul>
        </li>
        <li>
        <li><a href="/voice" class="voice">Voice</a></li>
        <ul class="type-action">
            <li><a href="/voice?type=install" class="install">Install </a></li>
            <li><a href="/voice?type=uninstall" class="uninstall">Uninstall</a></li>
        </ul>
        <li><a href="/update" class="update">Update</a></li>
    </li>
    <li><a href="/user/logout">Đăng xuất</a></li>
<?php endif; ?>
</ul>
