<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
$user = User::where('email','admin@fiducia-africa.local')->first();
echo $user ? "FOUND=YES\n" : "FOUND=NO\n";
if ($user) {
    echo "EMAIL={$user->email}\n";
    echo "HASH_CHECK=" . (Hash::check('password', $user->password) ? 'YES' : 'NO') . "\n";
    echo "ATTEMPT=" . (Auth::attempt(['email' => 'admin@fiducia-africa.local', 'password' => 'password']) ? 'YES' : 'NO') . "\n";
}
