<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

if (!is_post()) {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}
verify_csrf_or_fail();

admin_logout();
set_flash('success', 'Deconnexion effectuee.');
redirect('admin/login.php');
