<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Emojis oficiais do chat: PNGs em public/emojis/expression_N.png.
 * No texto da mensagem o emoji é gravado como ":eN:" (ex.: ":e12:").
 */
class Emojis
{
    private static ?array $ids = null;

    /** Números dos emojis que existem em disco (a numeração tem buracos). */
    public static function ids(): array
    {
        return self::$ids ??= collect(glob(public_path('emojis/expression_*.png')) ?: [])
            ->map(fn($path) => (int) preg_replace('/\D/', '', basename($path)))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Escapa o texto e troca ":eN:" por <img>. Só vira imagem se o arquivo existir;
     * qualquer outra coisa continua texto escapado (sem brecha para HTML).
     */
    public static function render(string $text): HtmlString
    {
        $valid = array_flip(self::ids());

        $html = preg_replace_callback('/:e(\d{1,3}):/', function ($m) use ($valid) {
            $n = (int) $m[1];

            // "07" não é código válido: só aceita o número sem zero à esquerda
            if ((string) $n !== $m[1] || !isset($valid[$n])) {
                return $m[0];
            }

            return '<img class="emoji" src="' . "/emojis/expression_{$n}.png" . '" alt="emoji ' . $n
                . '" width="24" height="24" loading="lazy" decoding="async">';
        }, e($text));

        return new HtmlString($html);
    }

    /** Mensagem feita só de 1 a 3 emojis (aparece em tamanho maior). */
    public static function isOnlyEmojis(string $text): bool
    {
        $valid = array_flip(self::ids());
        $count = 0;

        $rest = preg_replace_callback('/:e(\d{1,3}):/', function ($m) use ($valid, &$count) {
            $n = (int) $m[1];
            if ((string) $n === $m[1] && isset($valid[$n])) {
                $count++;
                return '';
            }

            return $m[0];
        }, $text);

        return $count >= 1 && $count <= 3 && trim($rest) === '';
    }
}
