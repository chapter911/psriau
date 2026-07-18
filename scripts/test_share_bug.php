<?php
define('ENVIRONMENT', 'development');
require __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$db = db_connect();

// 1. Create a dummy simak
$simakPayload = [
    'nama_paket' => 'Test Paket',
    'nomor_kontrak' => '123',
];
$db->table('trn_kontrak_simak')->insert($simakPayload);
$simakId = $db->insertID();

// 2. Create a share link
$token = 'test_token_' . time();
$sharePayload = [
    'simak_id' => $simakId,
    'share_token' => $token,
    'is_active' => 1
];
$db->table('trn_kontrak_simak_share')->insert($sharePayload);

// 3. Resolve it
$ctrl = new \App\Controllers\Admin\Kontrak();
$res = $ctrl->testResolveSharedSimak($token);
echo "Active token resolved: " . ($res ? 'YES' : 'NO') . "\n";

// 4. Deactivate it
$db->table('trn_kontrak_simak_share')->where('simak_id', $simakId)->update(['is_active' => 0]);

// 5. Resolve again
$res = $ctrl->testResolveSharedSimak($token);
echo "Deactivated token resolved: " . ($res ? 'YES' : 'NO') . "\n";

// Cleanup
$db->table('trn_kontrak_simak_share')->where('simak_id', $simakId)->delete();
$db->table('trn_kontrak_simak')->where('id', $simakId)->delete();
