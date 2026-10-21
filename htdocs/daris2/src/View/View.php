<?php

declare(strict_types=1);

namespace Daris\View;

use Daris\Exception\ConfigurationException;
use Daris\Http\Csrf;
use Daris\I18n\Translator;
use Daris\Support\Money;
use Daris\Support\Str;
use Daris\Support\Url;

/**
 * Moteur de gabarits PHP natif.
 *
 * Chaque gabarit reçoit `$v` (cette instance : helpers d'échappement, d'URL,
 * de prix) et les variables transmises par le contrôleur. Les pages sont
 * rendues puis injectées dans le gabarit de base (layout/base.php).
 */
final class View
{
    /** @var array<string, mixed> */
    private array $shared = [];

    public function __construct(
        private readonly string $templatesDir,
        private readonly Url $url,
        private readonly Csrf $csrf
    ) {
    }

    /**
     * Variables disponibles dans tous les gabarits (navigation, panier, flash…).
     */
    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    /**
     * Rend une page complète : gabarit de page + layout.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta Titre, description, canonical, noindex, bodyClass…
     */
    public function page(string $template, array $data = [], array $meta = []): string
    {
        $content = $this->render('pages/' . $template, $data);

        return $this->render('layout/base', [
            'content' => $content,
            'meta' => $meta,
        ]);
    }

    /**
     * Rend un gabarit isolé (partial, e-mail, flux…).
     *
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        if (preg_match('#^[a-z0-9/_-]+$#', $template) !== 1) {
            throw new ConfigurationException(sprintf('Nom de gabarit invalide : %s', $template));
        }

        $file = $this->templatesDir . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new ConfigurationException(sprintf('Gabarit introuvable : %s', $template));
        }

        $renderer = static function (string $__file, array $__vars, View $v): string {
            extract($__vars, EXTR_SKIP);
            ob_start();
            try {
                require $__file;
            } finally {
                $output = ob_get_clean();
            }

            return $output === false ? '' : $output;
        };

        return $renderer($file, array_merge($this->shared, $data), $this);
    }

    /**
     * Inclut un partial depuis un gabarit : <?= $v->partial('partials/product-card', ['product' => $p]) ?>
     *
     * @param array<string, mixed> $data
     */
    public function partial(string $template, array $data = []): string
    {
        return $this->render($template, $data);
    }

    // ---- Helpers disponibles dans les gabarits --------------------------

    public function e(string|int|float|null $value): string
    {
        return Str::e($value);
    }

    /**
     * @param array<string, scalar|list<string>> $query
     */
    public function url(string $path = '/', array $query = []): string
    {
        return Str::e($this->url->to($path, $query));
    }

    public function t(string $key, string $fallback = ''): string
    {
        $translator = $this->shared['translator'] ?? null;

        return $translator instanceof Translator ? $translator->t($key, $fallback) : ($fallback !== '' ? $fallback : $key);
    }

    /**
     * Champs cachés pour conserver les paramètres de filtre (y compris marque[]).
     *
     * @param array<string, string|list<string>> $query
     */
    public function queryHiddenFields(array $query, array $except = []): string
    {
        $html = '';
        foreach ($query as $key => $value) {
            if (in_array($key, $except, true)) {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $item) {
                    $html .= '<input type="hidden" name="' . Str::e($key) . '[]" value="' . Str::e($item) . '">';
                }
            } else {
                $html .= '<input type="hidden" name="' . Str::e($key) . '" value="' . Str::e((string) $value) . '">';
            }
        }

        return $html;
    }

    public function absoluteUrl(string $path = '/'): string
    {
        return Str::e($this->url->absolute($path));
    }

    public function asset(string $file): string
    {
        return Str::e($this->url->asset($file));
    }

    public function money(int $cents): string
    {
        return Str::e(Money::format($cents));
    }

    public function csrfField(): string
    {
        return $this->csrf->field();
    }

    public function csrfToken(): string
    {
        return Str::e($this->csrf->token());
    }

    /**
     * Liste de classes conditionnelles : $v->classes(['card' => true, 'is-active' => $active])
     *
     * @param array<string, bool> $map
     */
    public function classes(array $map): string
    {
        return Str::e(implode(' ', array_keys(array_filter($map))));
    }

    /**
     * Texte brut → paragraphes HTML échappés.
     */
    public function paragraphs(string $text): string
    {
        $parts = preg_split('/\n{2,}/', trim($text)) ?: [];
        $html = '';
        foreach ($parts as $part) {
            $html .= '<p>' . nl2br(Str::e(trim($part))) . '</p>';
        }

        return $html;
    }

    /**
     * Date lisible en français (sans dépendre de l'extension intl).
     */
    public function date(string $iso): string
    {
        $time = strtotime($iso);
        if ($time === false) {
            return Str::e($iso);
        }
        $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

        return Str::e(sprintf('%d %s %d', (int) date('j', $time), $months[(int) date('n', $time) - 1], (int) date('Y', $time)));
    }
}
