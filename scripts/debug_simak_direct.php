<?php
$db = new mysqli('satkerpps-riau.online', 'agun9011_satkerpps', '9w:wxJn|K', 'agun9011_satkerpps');
if ($db->connect_error) {
    die("DB connect failed: " . $db->connect_error);
}

// tree helper functions from controller/traits
function buildTree(array $elements, $parentId = 0): array
{
    $branch = [];
    foreach ($elements as $element) {
        $pId = $element['parent_id'] ? (int)$element['parent_id'] : 0;
        if ($pId === $parentId) {
            $children = buildTree($elements, (int)$element['id']);
            if ($children !== []) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }
    return $branch;
}

function annotateTreeDisplayNumbers(array $tree, string $prefix = ''): array
{
    $counter = 1;
    foreach ($tree as &$node) {
        $displayNo = $prefix ? $prefix . '.' . $counter : (string) $counter;
        $node['display_no'] = $displayNo;
        if (isset($node['children']) && is_array($node['children'])) {
            $node['children'] = annotateTreeDisplayNumbers($node['children'], $displayNo);
        }
        $counter++;
    }
    return $tree;
}

function flattenTree(array $tree): array
{
    $flat = [];
    foreach ($tree as $node) {
        $children = $node['children'] ?? [];
        unset($node['children']);
        $flat[] = $node;
        if ($children !== []) {
            $flat = array_merge($flat, flattenTree($children));
        }
    }
    return $flat;
}

function getTemplateItems($db, $includeHiddenShare) {
    $selectFields = ['id', 'parent_id', 'row_no', 'uraian', 'row_kind', 'has_question', 'has_draft', 'ordering', 'is_hidden_share'];
    $rows = [];
    $res = $db->query("SELECT " . implode(', ', $selectFields) . " FROM mst_simak_konstruksi_item WHERE is_active = 1 ORDER BY ordering ASC, id ASC");
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    if ($rows === []) return [];

    $tree = annotateTreeDisplayNumbers(buildTree($rows));
    
    // Custom flatten/walk like in getSimakKonstruksiTemplateFromMaster
    $flattened = [];
    $walk = static function (array $items, int $depth, string $sectionKey, string $sectionTitle) use (&$walk, &$flattened, $includeHiddenShare): void {
        foreach ($items as $item) {
            if (!$includeHiddenShare && (int) ($item['is_hidden_share'] ?? 0) === 1) {
                continue;
            }

            $rowKind = (string) ($item['row_kind'] ?? 'question');
            $hasQuestion = (int) ($item['has_question'] ?? 0) === 1;
            $hasDraft = (int) ($item['has_draft'] ?? 0) === 1;
            $children = $item['children'] ?? [];

            $isSection = ($rowKind === 'section');
            $isSubsection = ($rowKind === 'subsection');
            $isLeaf = ($children === [] && $hasQuestion);

            $currentSectionKey = $sectionKey;
            $currentSectionTitle = $sectionTitle;
            if ($isSection) {
                $currentSectionKey = (string) ($item['display_no'] ?? '');
                $currentSectionTitle = (string) ($item['uraian'] ?? '');
            }

            $rowType = 'detail';
            if ($isSection) {
                $rowType = 'section_header';
            } elseif ($isSubsection) {
                $rowType = 'subsection_header';
            }

            $flattened[] = [
                'id' => (int) ($item['id'] ?? 0),
                'row_no' => (int) ($item['row_no'] ?? 0),
                'display_no' => (string) ($item['display_no'] ?? ''),
                'uraian' => (string) ($item['uraian'] ?? ''),
                'row_type' => $rowType,
                'is_header' => ($isSection || $isSubsection),
                'is_leaf' => $isLeaf,
                'has_question' => $hasQuestion,
                'has_draft' => $hasDraft,
                'indent_level' => $depth,
                'section_key' => $currentSectionKey,
                'section_title' => $currentSectionTitle,
                'is_hidden_share' => (int) ($item['is_hidden_share'] ?? 0),
            ];

            if ($children !== []) {
                $walk($children, $depth + 1, $currentSectionKey, $currentSectionTitle);
            }
        }
    };

    $walk($tree, 0, '', '');
    return $flattened;
}

foreach ([true, false] as $includeHidden) {
    $items = getTemplateItems($db, $includeHidden);
    $leafCount = 0;
    foreach ($items as $item) {
        if ($item['is_leaf']) {
            $leafCount++;
        }
    }
    echo "includeHiddenShare = " . ($includeHidden ? 'true' : 'false') . " => Total Leaf Rows = " . $leafCount . "\n";
}

$db->close();
