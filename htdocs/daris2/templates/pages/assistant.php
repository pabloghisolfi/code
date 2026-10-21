<?php
/**
 * @var \Daris\View\View $v
 * @var string $greeting
 * @var list<string> $suggestions
 * @var list<array{topic: string, items: list<array{q: string, a: string}>}> $faq
 */
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Conseillère', 'title' => 'Léa, conseillère de style', 'lead' => 'Posez une question sur une teinte, une montre, la livraison ou un cadeau. Réponse instantanée, sans inscription.', 'crumbs' => [['', 'Conseillère']]]) ?>

<section class="section section--flush">
    <div class="wrap assistant">
        <div class="chat" data-chat>
            <div class="chat__log" data-chat-log aria-live="polite" aria-relevant="additions">
                <div class="chat__msg chat__msg--bot">
                    <p><?= $v->e($greeting) ?></p>
                </div>
            </div>
            <div class="chat__suggestions" data-chat-suggestions>
                <?php foreach ($suggestions as $suggestion) : ?>
                <button type="button" class="chip" data-chat-suggest><?= $v->e($suggestion) ?></button>
                <?php endforeach; ?>
            </div>
            <form class="chat__form" data-chat-form action="<?= $v->url('/chat/repondre') ?>" method="post">
                <label class="sr-only" for="chat-q">Votre question</label>
                <input id="chat-q" name="question" type="text" maxlength="300" placeholder="Ex. Quelle teinte de fond de teint ?" required autocomplete="off">
                <button class="btn btn--primary" type="submit" aria-label="Envoyer"><svg aria-hidden="true"><use href="#i-send"/></svg></button>
            </form>
        </div>

        <div class="assistant-faq" id="faq">
            <h2 class="h2">Questions fréquentes</h2>
            <?php foreach ($faq as $topic) : ?>
            <h3 class="h3"><?= $v->e($topic['topic']) ?></h3>
            <div class="faq">
                <?php foreach ($topic['items'] as $item) : ?>
                <details class="faq__item"><summary><?= $v->e($item['q']) ?></summary><p><?= $v->e($item['a']) ?></p></details>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
