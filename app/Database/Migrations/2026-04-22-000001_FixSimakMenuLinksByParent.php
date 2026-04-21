<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixSimakMenuLinksByParent extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2') || ! $this->db->tableExists('menu_lv3')) {
            return;
        }

        $masterId = $this->findLv1IdByLabel('Master');
        $kontrakId = $this->findLv1IdByLabel('Kontrak');

        if ($masterId !== null) {
            $masterSimakId = $this->findLv2IdByHeaderAndLabel($masterId, 'Simak');
            if ($masterSimakId !== null) {
                $this->setLv3LinkByHeaderAndLabels($masterSimakId, ['Kontruksi', 'Konstruksi'], 'admin/master/simak/konstruksi');
                $this->setLv3LinkByHeaderAndLabels($masterSimakId, ['Konsultasi'], 'admin/master/simak/konsultasi');
            }
        }

        if ($kontrakId !== null) {
            $kontrakSimakId = $this->findLv2IdByHeaderAndLabel($kontrakId, 'Simak');
            if ($kontrakSimakId !== null) {
                $this->setLv3LinkByHeaderAndLabels($kontrakSimakId, ['Kontruksi', 'Konstruksi'], 'admin/kontrak/simak/konstruksi');
                $this->setLv3LinkByHeaderAndLabels($kontrakSimakId, ['Konsultasi'], 'admin/kontrak/simak/konsultasi');
            }
        }
    }

    public function down()
    {
        // No-op: this migration is a corrective sync based on menu hierarchy.
    }

    private function findLv1IdByLabel(string $label): ?string
    {
        $row = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', strtolower($label))
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! is_array($row) || ! isset($row['id'])) {
            return null;
        }

        return (string) $row['id'];
    }

    private function findLv2IdByHeaderAndLabel(string $header, string $label): ?string
    {
        $row = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->where('LOWER(label)', strtolower($label))
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! is_array($row) || ! isset($row['id'])) {
            return null;
        }

        return (string) $row['id'];
    }

    private function setLv3LinkByHeaderAndLabels(string $header, array $labels, string $link): void
    {
        $normalizedLabels = array_values(array_unique(array_map(static fn (string $item): string => strtolower(trim($item)), $labels)));
        if ($normalizedLabels === []) {
            return;
        }

        $this->db->table('menu_lv3')
            ->where('header', $header)
            ->whereIn('LOWER(label)', $normalizedLabels)
            ->update(['link' => $link]);
    }
}