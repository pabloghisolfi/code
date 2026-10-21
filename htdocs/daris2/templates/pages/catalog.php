<?php
/**
 * Catalogue avec filtres.
 *
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Category|null $category
 * @var list<\Daris\Catalog\Category> $categories
 * @var \Daris\Catalog\ProductFilter $filter
 * @var list<\Daris\Catalog\Product> $products
 * @var int $total
 * @var int $pageNumber
 * @var int $pages
 * @var string $basePath
 * @var string $title
 * @var array<string, string> $sorts
 * @var array<string, string> $priceRanges
 * @var list<string> $brands
 */
$query = $filter->toQuery();
$crumbs = [[$category !== null ? $v->url('/boutique') : '', 'Boutique']];
if ($category !== null) {
    $crumbs[] = ['', $category->name()];
}
?>
<?= $v->partial('partials/page-head', [
    'kicker' => $category !== null ? ($category->slug() === 'montres' ? 'Collection montres' : 'Collection maquillage') : 'Toute la boutique',
    'title' => $title,
    'lead' => $category?->intro() ?? 'Tout le maquillage et toutes les montres, en un seul endroit. Filtrez par rayon, par prix, ou cherchez un produit précis.',
    'crumbs' => $crumbs,
    'tone' => $category?->slug() === 'maquillage' ? 'pink' : 'blue',
]) ?>

<section class="section section--flush">
    <div class="wrap catalog">
        <button class="btn btn--soft catalog__toggle" type="button" data-filters-toggle aria-expanded="false" aria-controls="filtres"><svg aria-hidden="true"><use href="#i-filter"/></svg> Filtres</button>

        <aside class="filters" id="filtres" data-filters>
            <form method="get" action="<?= $v->url($basePath) ?>" data-filter-form>
                <?php if ($filter->query() !== '') : ?>
                <div class="filters__group">
                    <p class="filters__title">Recherche</p>
                    <p class="filters__chip">« <?= $v->e($filter->query()) ?> » <a href="<?= $v->url($basePath) ?>" aria-label="Effacer la recherche">×</a></p>
                    <input type="hidden" name="q" value="<?= $v->e($filter->query()) ?>">
                </div>
                <?php endif; ?>

                <div class="filters__group">
                    <p class="filters__title">Univers</p>
                    <ul class="filters__links">
                        <li><a class="<?= $v->classes(['is-active' => $category === null]) ?>" href="<?= $v->url('/boutique', $query) ?>">Tout</a></li>
                        <?php foreach ($categories as $cat) : ?>
                        <li><a class="<?= $v->classes(['is-active' => $category?->slug() === $cat->slug()]) ?>" href="<?= $v->url('/boutique/' . $cat->slug(), array_diff_key($query, ['sous' => 1])) ?>"><?= $v->e($cat->name()) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php if ($category !== null) : ?>
                <fieldset class="filters__group">
                    <legend class="filters__title">Rayon</legend>
                    <label class="radio"><input type="radio" name="sous" value="" <?= $filter->subcategory() === null ? 'checked' : '' ?>><span>Tous les rayons</span></label>
                    <?php foreach ($category->subcategories() as $sub) : ?>
                    <label class="radio"><input type="radio" name="sous" value="<?= $v->e($sub['slug']) ?>" <?= $filter->subcategory() === $sub['slug'] ? 'checked' : '' ?>><span><?= $v->e($sub['name']) ?></span></label>
                    <?php endforeach; ?>
                </fieldset>
                <?php endif; ?>

                <?php if ($brands !== []) : ?>
                <fieldset class="filters__group">
                    <legend class="filters__title">Maison</legend>
                    <label class="radio"><input type="radio" name="marque" value="" <?= $filter->brand() === null ? 'checked' : '' ?>><span>Toutes les maisons</span></label>
                    <?php foreach ($brands as $brand) : ?>
                    <label class="radio"><input type="radio" name="marque" value="<?= $v->e($brand) ?>" <?= $filter->brand() === $brand ? 'checked' : '' ?>><span><?= $v->e($brand) ?></span></label>
                    <?php endforeach; ?>
                </fieldset>
                <?php endif; ?>

                <fieldset class="filters__group">
                    <legend class="filters__title">Prix</legend>
                    <?php foreach ($priceRanges as $value => $label) : ?>
                    <label class="radio"><input type="radio" name="prix" value="<?= $v->e($value) ?>" <?= $filter->priceRange() === $value ? 'checked' : '' ?>><span><?= $v->e($label) ?></span></label>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset class="filters__group">
                    <legend class="filters__title">Sélection</legend>
                    <label class="radio"><input type="radio" name="badge" value="" <?= $filter->badge() === null ? 'checked' : '' ?>><span>Tout</span></label>
                    <?php foreach (\Daris\Catalog\Product::BADGE_LABELS as $value => $label) : ?>
                    <label class="radio"><input type="radio" name="badge" value="<?= $v->e($value) ?>" <?= $filter->badge() === $value ? 'checked' : '' ?>><span><?= $v->e($label) ?></span></label>
                    <?php endforeach; ?>
                </fieldset>

                <input type="hidden" name="tri" value="<?= $v->e($filter->sort()) ?>">
                <div class="filters__actions">
                    <button class="btn btn--primary btn--block" type="submit">Appliquer</button>
                    <?php if ($filter->isActive()) : ?><a class="link" href="<?= $v->url($basePath) ?>">Tout effacer</a><?php endif; ?>
                </div>
            </form>
        </aside>

        <div class="catalog__main">
            <div class="catalog__bar">
                <p class="catalog__count" aria-live="polite"><strong><?= $total ?></strong> <?= $total > 1 ? 'produits' : 'produit' ?></p>
                <form class="sort" method="get" action="<?= $v->url($basePath) ?>" data-sort-form>
                    <?php foreach (array_diff_key($query, ['tri' => 1]) as $key => $value) : ?>
                    <input type="hidden" name="<?= $v->e($key) ?>" value="<?= $v->e($value) ?>">
                    <?php endforeach; ?>
                    <label for="tri">Trier par</label>
                    <select id="tri" name="tri">
                        <?php foreach ($sorts as $value => $label) : ?>
                        <option value="<?= $v->e($value) ?>" <?= $filter->sort() === $value ? 'selected' : '' ?>><?= $v->e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button class="btn btn--ghost btn--sm" type="submit">OK</button></noscript>
                </form>
            </div>

            <?php if ($products === []) : ?>
            <div class="empty" data-reveal>
                <h2>Rien ne correspond à ces critères.</h2>
                <p>Essayez un autre mot, élargissez le prix, ou laissez la conseillère vous aiguiller.</p>
                <div class="actions"><a class="btn btn--primary" href="<?= $v->url($basePath) ?>">Voir tout le rayon</a><a class="btn btn--soft" href="<?= $v->url('/chat') ?>">Demander à Léa</a></div>
            </div>
            <?php else : ?>
            <div class="grid grid--products grid--catalog">
                <?php foreach ($products as $product) : ?>
                <?= $v->partial('partials/product-card', ['product' => $product]) ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($pages > 1) : ?>
            <nav class="pagination" aria-label="Pagination">
                <?php for ($p = 1; $p <= $pages; $p++) : ?>
                <?php if ($p === $pageNumber) : ?><span class="is-current" aria-current="page"><?= $p ?></span>
                <?php else : ?><a href="<?= $v->url($basePath, $query + ['page' => $p]) ?>"><?= $p ?></a><?php endif; ?>
                <?php endfor; ?>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($category !== null) : ?>
<section class="section section--tint">
    <div class="wrap split">
        <div data-reveal>
            <p class="kicker">Besoin d'aide pour choisir ?</p>
            <h2 class="h2"><?= $category->slug() === 'montres' ? 'Quelle taille de boîtier pour votre poignet ?' : 'Quelle teinte pour votre carnation ?' ?></h2>
            <p class="lead"><?= $category->slug() === 'montres' ? 'Le guide explique comment mesurer son tour de poignet et propose un petit calculateur. En deux minutes, vous savez quels modèles vous iront.' : 'Rosé ou doré, clair ou profond : le test du sous-ton prend trente secondes. Et si vous vous trompez, l\'échange de teinte est gratuit.' ?></p>
            <div class="actions"><a class="btn btn--primary" href="<?= $v->url('/conseils') ?>#<?= $category->slug() === 'montres' ? 'taille-montre' : 'sous-ton' ?>">Ouvrir le guide</a><a class="btn btn--soft" href="<?= $v->url('/chat') ?>">Demander à Léa</a></div>
        </div>
        <ul class="checklist-static" data-reveal>
            <?php if ($category->slug() === 'montres') : ?>
            <li><strong>14 à 15.5 cm</strong> de tour de poignet : boîtier de 24 à 28 mm</li>
            <li><strong>15.5 à 17 cm</strong> : boîtier de 28 à 34 mm</li>
            <li><strong>17 cm et plus</strong> : boîtier de 34 à 38 mm</li>
            <li><strong>Bracelet milanais</strong> : se règle sans outil, au millimètre</li>
            <?php else : ?>
            <li><strong>Veines bleutées</strong> au poignet : sous-ton rosé, teintes « R »</li>
            <li><strong>Veines verdâtres</strong> : sous-ton doré, teintes « D »</li>
            <li><strong>Entre deux ?</strong> Prenez la plus claire, elle se fond mieux</li>
            <li><strong>Échange de teinte</strong> gratuit, même flacon ouvert</li>
            <?php endif; ?>
        </ul>
    </div>
</section>
<?php endif; ?>
