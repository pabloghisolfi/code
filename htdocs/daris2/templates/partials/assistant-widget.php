<?php
/**
 * Conseillère Azurine — panneau rose clair sur toutes les pages.
 *
 * @var \Daris\View\View $v
 * @var string $assistantGreeting
 * @var list<string> $assistantSuggestions
 */
require __DIR__ . '/app-fallback.php';

$assistantGreeting = $assistantGreeting ?? 'Bonjour, je suis Léa, conseillère Azurine. Je peux vous guider sur les teintes, les montres et le catalogue.';
$assistantSuggestions = $assistantSuggestions ?? ['Quelle teinte pour un teint clair ?', 'Taille de montre pour poignet fin', 'Livraison en Suisse'];
?>
<div class="az-assistant" data-assistant>
    <button class="az-assistant__fab" type="button" data-assistant-toggle aria-expanded="false" aria-controls="az-assistant-panel">
        <span class="az-assistant__fab-label">Conseillère</span>
        <svg aria-hidden="true" viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M12 3c-4.4 0-8 2.7-8 6v3l-2 2v1h20v-1l-2-2V9c0-3.3-3.6-6-8-6Zm0 2c3.3 0 6 1.8 6 4v3.3l1.2 1.2H4.8L6 13.3V9c0-2.2 2.7-4 6-4Zm-1 16h2v-2h-2v2Z"/></svg>
    </button>

    <div class="az-assistant__panel" id="az-assistant-panel" hidden data-assistant-panel role="dialog" aria-label="Conseillère Azurine" aria-modal="false">
        <header class="az-assistant__head">
            <div>
                <p class="az-assistant__kicker">Assistante boutique</p>
                <h2 class="az-assistant__title">Léa · Azurine</h2>
            </div>
            <button class="az-assistant__close" type="button" data-assistant-toggle aria-label="Fermer le panneau">
                <svg aria-hidden="true"><use href="#i-close"/></svg>
            </button>
        </header>
        <p class="az-assistant__intro"><?= $v->e($assistantGreeting) ?></p>
        <div class="az-assistant__log" data-chat-log aria-live="polite">
            <div class="chat__msg chat__msg--bot"><?= $v->e($assistantGreeting) ?></div>
        </div>
        <div class="az-assistant__suggest">
            <?php foreach ($assistantSuggestions as $suggestion) : ?>
            <button type="button" class="chip" data-chat-suggest><?= $v->e($suggestion) ?></button>
            <?php endforeach; ?>
        </div>
        <form class="az-assistant__form" action="<?= $v->url('/chat/repondre') ?>" method="post" data-chat-form>
            <label class="sr-only" for="chat-q-widget">Votre question</label>
            <input id="chat-q-widget" name="question" type="text" maxlength="300" placeholder="Teinte, montre, taille…" autocomplete="off">
            <button class="btn btn--primary btn--sm" type="submit">Envoyer</button>
        </form>
        <p class="az-assistant__note">Réponses instantanées sur le site · <a href="<?= $v->url('/chat') ?>">Page conseillère</a></p>
    </div>
</div>
