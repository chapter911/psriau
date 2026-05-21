<?php

namespace App\Traits;

trait SimakNumberingTrait
{
    protected function buildTree(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $row['children'] = [];
            $map[(int) $row['id']] = $row;
        }

        $roots = [];
        foreach ($map as $id => $row) {
            $parentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
            if ($parentId > 0 && isset($map[$parentId])) {
                $map[$parentId]['children'][] = &$map[$id];
                continue;
            }

            $roots[] = &$map[$id];
        }

        $sortFn = function (array &$items) use (&$sortFn): void {
            usort($items, static function (array $a, array $b): int {
                $orderingCmp = ((int) ($a['ordering'] ?? 0)) <=> ((int) ($b['ordering'] ?? 0));
                if ($orderingCmp !== 0) {
                    return $orderingCmp;
                }

                return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
            });

            foreach ($items as &$item) {
                if (! empty($item['children'])) {
                    $sortFn($item['children']);
                }
            }
            unset($item);
        };

        $sortFn($roots);

        return $roots;
    }

    protected function flattenTree(array $tree): array
    {
        $result = [];

        $walker = function (array $nodes, int $depth) use (&$walker, &$result): void {
            foreach ($nodes as $node) {
                $node['depth'] = $depth;
                $node['children_count'] = is_array($node['children'] ?? null) ? count($node['children']) : 0;
                $result[] = $node;

                if (! empty($node['children'])) {
                    $walker($node['children'], $depth + 1);
                }
            }
        };

        $walker($tree, 0);

        return $result;
    }

    protected function annotateTreeDisplayNumbers(array $tree): array
    {
        $walker = function (array $nodes, int $depth) use (&$walker): array {
            $annotated = [];

            foreach ($nodes as $index => $node) {
                $node['display_no_auto'] = $this->formatSimakDisplayNo($depth, $index + 1);
                $children = is_array($node['children'] ?? null) ? $node['children'] : [];
                $node['children'] = $children !== [] ? $walker($children, $depth + 1) : [];
                $annotated[] = $node;
            }

            return $annotated;
        };

        return $walker($tree, 0);
    }

    protected function formatSimakDisplayNo(int $depth, int $position): string
    {
        return match ($depth % 4) {
            0 => $this->formatAlphaIndex($position, true) . '.',
            1 => $position . '.',
            2 => $this->formatAlphaIndex($position, false) . '.',
            default => '-',
        };
    }

    protected function formatAlphaIndex(int $position, bool $uppercase = true): string
    {
        $position = max(1, $position);
        $letters = '';

        while ($position > 0) {
            $position--;
            $letters = chr(65 + ($position % 26)) . $letters;
            $position = intdiv($position, 26);
        }

        return $uppercase ? $letters : strtolower($letters);
    }
}
