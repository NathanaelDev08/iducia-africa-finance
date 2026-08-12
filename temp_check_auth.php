<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$u = User::where('email', 'admin@fiducia-africa.local')->first();
if ($u) {
    echo "user_exists\n";
    echo Hash::check('password', $u->password) ? "password_ok\n" : "password_bad\n";
    echo $u->name . "\n";
} else {
    echo "user_missing\n";
}
