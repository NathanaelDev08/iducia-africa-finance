<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
echo "ROOT=" . __DIR__ . PHP_EOL;
echo "APP_ENV=" . env('APP_ENV') . PHP_EOL;
echo "DB_CONNECTION=" . env('DB_CONNECTION') . PHP_EOL;
echo "DB_DATABASE=" . env('DB_DATABASE') . PHP_EOL;
$user = User::where('email','admin@fiducia-africa.local')->first();
echo "FOUND=" . ($user ? 'YES' : 'NO') . PHP_EOL;
if ($user) {
    echo "HASH_CHECK=" . (Hash::check('password', $user->password) ? 'YES' : 'NO') . PHP_EOL;
    echo "ATTEMPT=" . (Auth::attempt(['email' => 'admin@fiducia-africa.local','password' => 'password']) ? 'YES' : 'NO') . PHP_EOL;
}
