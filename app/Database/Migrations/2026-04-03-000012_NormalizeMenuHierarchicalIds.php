<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeMenuHierarchicalIds extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2') || ! $this->db->tableExists('menu_lv3') || ! $this->db->tableExists('menu_akses')) {
            return;
        }

        $lv1Rows = $this->db->table('menu_lv1')
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $lv2Rows = $this->db->table('menu_lv2')
            ->orderBy('header', 'ASC')
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $lv3Rows = $this->db->table('menu_lv3')
            ->orderBy('header', 'ASC')
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if ($lv1Rows === [] && $lv2Rows === [] && $lv3Rows === []) {
            return;
        }

        $this->db->transStart();

        $lv1Map = [];
        $sequence = 0;
        foreach ($lv1Rows as $row) {
            $oldId = (string) $row['id'];
            $sequence++;
            $newId = str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $lv1Map[$oldId] = $newId;

            $this->db->table('menu_lv1')
                ->where('id', $oldId)
                ->update(['id' => $newId]);
        }

        $lv2Map = [];
        $lv2SequenceByParent = [];
        foreach ($lv2Rows as $row) {
            $oldId = (string) $row['id'];
            $oldHeader = (string) $row['header'];
            if ($oldHeader === '' || ! isset($lv1Map[$oldHeader])) {
                continue;
            }

            $newHeader = $lv1Map[$oldHeader];
            $lv2SequenceByParent[$newHeader] = ($lv2SequenceByParent[$newHeader] ?? 0) + 1;
            $newId = $newHeader . '-' . str_pad((string) $lv2SequenceByParent[$newHeader], 2, '0', STR_PAD_LEFT);
            $lv2Map[$oldId] = $newId;

            $this->db->table('menu_lv2')
                ->where('id', $oldId)
                ->update([
                    'id'     => $newId,
                    'header' => $newHeader,
                ]);
        }

        $lv3Map = [];
        $lv3SequenceByParent = [];
        foreach ($lv3Rows as $row) {
            $oldId = (string) $row['id'];
            $oldHeader = (string) $row['header'];
            if ($oldHeader === '' || ! isset($lv2Map[$oldHeader])) {
                continue;
            }

            $newHeader = $lv2Map[$oldHeader];
            $lv3SequenceByParent[$newHeader] = ($lv3SequenceByParent[$newHeader] ?? 0) + 1;
            $newId = $newHeader . '-' . str_pad((string) $lv3SequenceByParent[$newHeader], 2, '0', STR_PAD_LEFT);
            $lv3Map[$oldId] = $newId;

            $this->db->table('menu_lv3')
                ->where('id', $oldId)
                ->update([
                    'id'     => $newId,
                    'header' => $newHeader,
                ]);
        }

        $menuIdMap = $lv1Map + $lv2Map + $lv3Map;
        foreach ($menuIdMap as $oldId => $newId) {
            $this->db->table('menu_akses')
                ->where('menu_id', $oldId)
                ->update(['menu_id' => $newId]);
        }

        $this->syncMenuAccessParents($lv1Map, $lv2Map, $lv3Map);

        $this->db->transComplete();
    }

    public function down()
    {
        // Irreversible data normalization.
    }

    private function syncMenuAccessParents(array $lv1Map, array $lv2Map, array $lv3Map): void
    {
        $accessRows = $this->db->table('menu_akses')
            ->orderBy('group_id', 'ASC')
            ->orderBy('menu_id', 'ASC')
            ->get()
            ->getResultArray();

        $lv2Parents = [];
        foreach ($lv2Map as $oldId => $newId) {
            $parentNewId = explode('-', $newId, 2)[0] ?? '';
            if ($parentNewId !== '') {
                $lv2Parents[$newId] = $parentNewId;
            }
        }

        $lv3Parents = [];
        foreach ($lv3Map as $oldId => $newId) {
            $parts = explode('-', $newId);
            if (count($parts) >= 3) {
                $lv2Id = $parts[0] . '-' . $parts[1];
                $lv3Parents[$newId] = $lv2Id;
            }
        }

        $rowsByGroup = [];
        foreach ($accessRows as $row) {
            $rowsByGroup[(int) $row['group_id']][(string) $row['menu_id']] = $row;
        }

        foreach ($rowsByGroup as $groupId => $groupRows) {
            $requiredParents = [];

            foreach (array_keys($groupRows) as $menuId) {
                if (isset($lv3Parents[$menuId])) {
                    $lv2Id = $lv3Parents[$menuId];
                    $lv1Id = explode('-', $lv2Id, 2)[0] ?? '';
                    if ($lv1Id !== '') {
                        $requiredParents[$lv1Id] = true;
                    }
                    if ($lv2Id !== '') {
                        $requiredParents[$lv2Id] = true;
                    }
                }

                if (isset($lv2Parents[$menuId])) {
                    $requiredParents[$lv2Parents[$menuId]] = true;
                }
            }

            foreach (array_keys($requiredParents) as $parentId) {
                if ($parentId === '' || isset($groupRows[$parentId])) {
                    continue;
                }

                $this->db->table('menu_akses')->insert([
                    'group_id' => $groupId,
                    'menu_id' => $parentId,
                    'FiturAdd' => $groupId === 1 ? 1 : 0,
                    'FiturEdit' => $groupId === 1 ? 1 : 0,
                    'FiturDelete' => $groupId === 1 ? 1 : 0,
                    'FiturExport' => $groupId === 1 ? 1 : 0,
                    'FiturImport' => $groupId === 1 ? 1 : 0,
                    'FiturApproval' => $groupId === 1 ? 1 : 0,
                ]);
            }
        }
    }
}