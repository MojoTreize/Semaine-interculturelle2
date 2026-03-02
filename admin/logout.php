<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

admin_logout();
set_flash('success', 'Deconnexion effectuee.');
redirect('admin/login.php');
