<?php
if (! function_exists('pdf_normalize_html')) {
    function pdf_normalize_html(string $html): string
    {
        $text = $html;
        $text = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', $text);
        $text = str_replace('&nbsp;', ' ', $text);
        $text = trim($text);
        return $text;
    }
}

if (! function_exists('pdf_split_blocks')) {
    function pdf_split_blocks(string $html): array
    {
        $html = pdf_normalize_html($html);
        if ($html === '') {
            return [];
        }

        // Try DOMDocument
        if (class_exists('DOMDocument')) {
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>');
            libxml_clear_errors();
            $container = $doc->getElementsByTagName('div')->item(0);
            $chunks = [];
            if ($container) {
                foreach ($container->childNodes as $child) {
                    $outer = $doc->saveHTML($child);
                    $outer = trim((string) $outer);
                    if ($outer !== '') {
                        $chunks[] = $outer;
                    }
                }
            }
            if (! empty($chunks)) {
                return $chunks;
            }
        }

        // fallback
        $parts = preg_split('/<br\s*\/?\s*>|<\/p>|<\/div>/i', $html);
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $out[] = '<p>' . $p . '</p>';
            }
        }
        return $out;
    }
}
