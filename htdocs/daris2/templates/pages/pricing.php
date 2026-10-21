<?php
/**
 * @var \Daris\View\View $v
 * @var list<array{category: \Daris\Catalog\Category, products: list<\Daris\Catalog\Product>}> $byCategory
 * @var array<string, mixed> $shippingMethods
 * @var int $freeShippingFrom
 * @var int $giftWrapPrice
 * @var float $vatRate
 * @var list<array{name: string, price: int, note: string}> $services
 * @var list<array{item: string, market: string, azurine: int}> $market
 * @var array<string, mixed> $promoCodes
 * @var list<array{topic: string, items: list<array{q: string, a: string}>}> $faq
 */
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Transparence', 'title' => 'Prix, livraison et services', 'lead' => 'Tous les montants sont en francs suisses, TVA de ' . rtrim(rtrim(number_format($vatRate, 1), '0'), '.') . ' % incluse. Pas de frais cachés au moment de payer.', 'crumbs' => [['', 'Prix & livraison']]]) ?>

<section class="section section--flush" id="livraison">
    <div class="wrap">
        <div class="pricing-ship">
            <?php foreach ($shippingMethods as $key => $method) : ?>
            <article class="price-card" data-reveal>
                <h2 class="h3"><?= $v->e((string) $method['label']) ?></h2>
                <p class="price price--lg"><span class="price__now"><?= $key === 'economy' ? 'CHF 7.00' : 'CHF 9.00' ?></span></p>
                <p><?= $v->e((string) $method['delay']) ?></p>
                <?php if ($key === 'economy') : ?><p class="badge-inline">Offerte dès <?= $v->money($freeShippingFrom) ?></p><?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php foreach ($byCategory as $block) : $cat = $block['category']; ?>
<section class="section <?= $cat->slug() === 'maquillage' ? 'section--pink' : '' ?>">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => $cat->name(), 'title' => 'Grille tarifaire · ' . $cat->name(), 'text' => $cat->intro(), 'linkHref' => $v->url('/boutique/' . $cat->slug()), 'linkLabel' => 'Voir le rayon']) ?>
        <div class="table-wrap" tabindex="0">
            <table class="data-table">
                <thead><tr><th>Produit</th><th>Rayon</th><th class="num">Prix</th></tr></thead>
                <tbody>
                    <?php foreach ($block['products'] as $product) : ?>
                    <tr>
                        <td><a href="<?= $v->url('/produit/' . $product->slug()) ?>"><?= $v->e($product->name()) ?></a></td>
                        <td><?= $v->e($cat->subcategoryName($product->subcategory()) ?? $product->subcategory()) ?></td>
                        <td class="num"><?= $v->money($product->price()) ?><?php if ($product->oldPrice() !== null) : ?> <s><?= $v->money($product->oldPrice()) ?></s><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endforeach; ?>

<section class="section section--tint" id="services">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Atelier', 'title' => 'Services montres & cadeaux', 'center' => true]) ?>
        <div class="grid grid--services">
            <?php foreach ($services as $service) : ?>
            <article class="service-card" data-reveal>
                <h3><?= $v->e($service['name']) ?></h3>
                <p class="price"><?= $service['price'] === 0 ? 'Gratuit' : $v->money($service['price']) ?></p>
                <p><?= $v->e($service['note']) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Marché suisse', 'title' => 'Où se situent nos prix ?', 'text' => 'Fourchettes habituellement observées en boutique et en ligne en Suisse, à titre indicatif.']) ?>
        <div class="table-wrap" tabindex="0">
            <table class="data-table">
                <thead><tr><th>Produit type</th><th>Marché</th><th>Azurine</th></tr></thead>
                <tbody>
                    <?php foreach ($market as $row) : ?>
                    <tr>
                        <th scope="row"><?= $v->e($row['item']) ?></th>
                        <td><?= $v->e($row['market']) ?></td>
                        <td class="num"><strong><?= $v->money($row['azurine']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($promoCodes !== []) : ?>
        <p class="promo-note">Codes actifs : <?php foreach ($promoCodes as $code => $def) : ?><code><?= $v->e($code) ?></code> (<?= $v->e((string) $def['label']) ?>)<?php endforeach; ?>.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section section--tint" id="retours">
    <div class="wrap split">
        <div data-reveal>
            <p class="kicker">Retours</p>
            <h2 class="h2">30 jours pour changer d'avis</h2>
            <p class="lead">Maquillage non ouvert, montre non portée dans son écrin. Étiquette de retour gratuite, remboursement sous 5 jours ouvrés après réception.</p>
            <p>Échange de teinte de fond de teint gratuit, même flacon ouvert, une fois par commande.</p>
        </div>
        <div class="faq" data-reveal>
            <?php foreach ($faq as $topic) : foreach (array_slice($topic['items'], 0, 2) as $item) : ?>
            <details class="faq__item"><summary><?= $v->e($item['q']) ?></summary><p><?= $v->e($item['a']) ?></p></details>
            <?php endforeach; endforeach; ?>
            <a class="link" href="<?= $v->url('/chat') ?>">Toutes les questions <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
        </div>
    </div>
</section>
