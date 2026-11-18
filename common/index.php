<?php
require __DIR__ . '/bootstrap.php';

$user = common_user();
if ($user) {
    redirect('/' . $user['vai_tro']);
}
redirect('/index.php');
