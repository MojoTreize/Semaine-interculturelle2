<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

admin_logout();
set_flash('success', 'Déconnexion effectuée.');
redirect('admin/login.php');
