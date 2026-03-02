<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

set_flash('warning', t('contribute.payment_cancelled'));
redirect('contribute.php');
