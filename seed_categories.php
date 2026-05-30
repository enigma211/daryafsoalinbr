<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

for($i = 1; $i <= 24; $i++) {
    \App\Models\Category::firstOrCreate(['topic' => 'مبحث ' . $i]);
}
\App\Models\Category::firstOrCreate(['topic' => 'سایر']);
echo "Categories seeded successfully.";
