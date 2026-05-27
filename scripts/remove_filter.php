<?php
// Script untuk menghapus filter card dari SIMAK view
$file = 'app/Views/admin/kontrak/simak_konstruksi.php';
$content = file_get_contents($file);

// Remove the entire filter card (lines 88-133)
$lines = explode("\n", $content);
$newLines = array_merge(
    array_slice($lines, 0, 87),  // Before filter card
    array_slice($lines, 133)  // After filter card
);
$content = implode("\n", $newLines);
file_put_contents($file, $content);
echo "Filter removed from {$file}\n";

// Same for konsultasi
$file2 = 'app/Views/admin/kontrak/simak_konsultasi.php';
$content2 = file_get_contents($file2);
$lines2 = explode("\n", $content2);
$newLines2 = array_merge(
    array_slice($lines2, 0, 87),
    array_slice($lines2, 133)
);
$content2 = implode("\n", $newLines2);
file_put_contents($file2, $content2);
echo "Filter removed from {$file2}\n";
