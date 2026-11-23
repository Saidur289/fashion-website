<?php

namespace App\Support;

/**
 * Converts between SafeBuy's JSON content blocks ([['h', ...], ['p', ...], ['ul', [...]], ['ol', [...]]])
 * and clean admin-editable text (## heading, - bullet, 1. numbered step, plain paragraph).
 */
class ProductBlocks
{
    public static function toText(?array $blocks): string
    {
        if (! $blocks) {
            return '';
        }

        $out = [];
        foreach ($blocks as $block) {
            if (! is_array($block) || count($block) < 2) {
                continue;
            }
            [$type, $content] = $block;
            if ($type === 'h' && is_string($content)) {
                $out[] = '## '.$content;
            } elseif ($type === 'p' && is_string($content)) {
                $out[] = $content;
            } elseif ($type === 'ul' && is_array($content)) {
                $out[] = collect($content)->map(fn ($i) => '- '.$i)->implode("\n");
            } elseif ($type === 'ol' && is_array($content)) {
                $out[] = collect($content)->values()->map(fn ($i, $idx) => ($idx + 1).'. '.$i)->implode("\n");
            }
        }

        return implode("\n\n", $out);
    }

    public static function fromText(?string $text): ?array
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        $lines = preg_split('/\R/', $text);
        $blocks = [];
        $listType = null;
        $listItems = [];

        $flushList = function () use (&$blocks, &$listType, &$listItems) {
            if ($listType && $listItems) {
                $blocks[] = [$listType, $listItems];
            }
            $listType = null;
            $listItems = [];
        };

        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '') {
                $flushList();
                continue;
            }
            if (str_starts_with($line, '## ')) {
                $flushList();
                $blocks[] = ['h', trim(substr($line, 3))];
            } elseif (preg_match('/^[-*•]\s+(.+)$/', $line, $m)) {
                if ($listType !== 'ul') {
                    $flushList();
                    $listType = 'ul';
                }
                $listItems[] = trim($m[1]);
            } elseif (preg_match('/^\d+\.\s+(.+)$/', $line, $m)) {
                if ($listType !== 'ol') {
                    $flushList();
                    $listType = 'ol';
                }
                $listItems[] = trim($m[1]);
            } else {
                $flushList();
                $blocks[] = ['p', $line];
            }
        }

        $flushList();

        return $blocks ?: null;
    }
}
