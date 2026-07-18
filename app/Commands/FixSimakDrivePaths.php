<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\Admin\Kontrak;

class FixSimakDrivePaths extends BaseCommand
{
    protected $group = 'App';
    protected $name = 'app:fix-simak-drive-paths';
    protected $description = 'Fix old SIMAK documents that were uploaded to Google Drive but lack unique local paths.';
    protected $usage = 'app:fix-simak-drive-paths';

    public function run(array $params)
    {
        CLI::write('Starting to fix SIMAK Google Drive paths...', 'green');

        $db = db_connect();
        $tables = ['trn_kontrak_simak_verifikasi_dokumen', 'trn_kontrak_simak_konsultasi_verifikasi_dokumen'];

        $totalUpdated = 0;

        foreach ($tables as $index => $table) {
            if (!$db->tableExists($table)) {
                CLI::write("Table $table does not exist. Skipping.", 'yellow');
                continue;
            }

            CLI::write("Processing table: $table", 'cyan');
            
            $sharedType = $index === 0 ? 'konstruksi' : 'konsultasi';

            $rows = $db->table($table)
                ->where('file_relative_path LIKE', 'http%')
                ->where('is_google_drive_link', 0)
                ->get()
                ->getResultArray();

            $count = count($rows);
            CLI::write("Found $count records to fix.", 'yellow');

            foreach ($rows as $row) {
                $id = (int)$row['id'];
                $simakId = (int)$row['simak_id'];
                $rowNo = (int)$row['row_no'];
                $originalName = (string)$row['file_original_name'];
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                if (empty($ext)) $ext = 'bin';
                $gdriveLink = (string)$row['file_relative_path'];

                $uniqueHash = substr(md5(uniqid('', true)), 0, 8);
                $storedName = 'simak_' . $simakId . '_' . $rowNo . '_' . $uniqueHash . '.' . $ext;
                $relativePath = 'uploads/simak/' . $sharedType . '/' . $simakId . '/' . $storedName;

                $updateData = [
                    'file_stored_name' => $storedName,
                    'file_relative_path' => $relativePath,
                    'is_google_drive_link' => 1,
                    'google_drive_source_url' => $gdriveLink,
                ];

                // extract file ID if possible
                $ctrl = new Kontrak();
                $method = new \ReflectionMethod($ctrl, 'extractGoogleDriveFileId');
                $method->setAccessible(true);
                $fileId = $method->invoke($ctrl, $gdriveLink);
                if ($fileId) {
                    $updateData['original_file_id'] = $fileId;
                }

                $db->table($table)->where('id', $id)->update($updateData);
                $totalUpdated++;
            }
        }

        CLI::write("Done! Updated $totalUpdated records.", 'green');
    }
}
