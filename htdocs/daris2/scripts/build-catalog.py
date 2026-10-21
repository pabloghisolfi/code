#!/usr/bin/env python3
"""Generate data/products.json for Azurine multi-brand catalog (CHF centimes)."""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1] / "data"

WATCH_BRANDS = [
    ("Rolex", [
        ("submariner-date-41", "Submariner Date 41", "classiques", 1285000, "watch-round", "#0d3d56", "Cadran noir, lunette Cerachrom, calibre 3235."),
        ("datejust-36", "Datejust 36", "classiques", 985000, "watch-jewel", "#1a4a6e", "Fluted bezel, index diamants, bracelet Jubilee."),
        ("day-date-40", "Day-Date 40", "classiques", 3850000, "watch-round", "#2a1810", "Or jaune, jour en français, bracelet President."),
        ("gmt-master-ii", "GMT-Master II", "sport-chic", 1125000, "watch-chrono", "#1e3a5f", "Bi-color Pepsi, deux fuseaux horaires."),
    ]),
    ("Patek Philippe", [
        ("nautilus-5711", "Nautilus 5711/1A", "sport-chic", 8950000, "watch-round", "#4a5568", "Acier, cadran bleu soleil, bracelet intégré."),
        ("calatrava-5227", "Calatrava 5227G", "classiques", 3250000, "watch-jewel", "#e8e4dc", "Or gris, guilloché, fond transparent."),
        ("aquanaut-5167", "Aquanaut 5167A", "sport-chic", 4850000, "watch-round", "#2d3748", "Caoutchouc tropical, cadran embossé."),
        ("complications-5205", "Complications 5205R", "classiques", 5680000, "watch-round", "#3d2914", "Phase de lune, calendrier annuel."),
    ]),
    ("Audemars Piguet", [
        ("royal-oak-15500", "Royal Oak 15500ST", "sport-chic", 4280000, "watch-round", "#708090", "Tapisserie bleue, bracelet acier."),
        ("royal-oak-offshore", "Royal Oak Offshore 42", "sport-chic", 5120000, "watch-chrono", "#1a202c", "Chronographe, couronne protégée."),
        ("code-11-59", "Code 11.59 Selfwinding", "classiques", 3890000, "watch-jewel", "#f5f0e8", "Boîtier biconvexe, cadran laqué."),
    ]),
    ("Hublot", [
        ("big-bang-unico", "Big Bang Unico 42", "sport-chic", 1890000, "watch-chrono", "#111827", "Carbone & titane, mouvement manufacture."),
        ("classic-fusion-titanium", "Classic Fusion Titanium 45", "classiques", 985000, "watch-round", "#64748b", "Titane satiné, cadran vertical satin."),
        ("spirit-of-big-bang", "Spirit of Big Bang 42", "sport-chic", 2450000, "watch-chrono", "#7c2d12", "Tonneau, céramique, chronographe."),
    ]),
    ("Omega", [
        ("speedmaster-moonwatch", "Speedmaster Moonwatch", "sport-chic", 785000, "watch-chrono", "#1f2937", "Calibre 3861, verre saphir, héritage lunaire."),
        ("seamaster-diver-300", "Seamaster Diver 300M", "sport-chic", 625000, "watch-round", "#0e7490", "Cadran vague, valve hélium, co-axial."),
        ("constellation-41", "Constellation 41", "classiques", 545000, "watch-jewel", "#334155", "Griffes, cadran aventurine, acier & or."),
        ("de-ville-prestige", "De Ville Prestige 39", "classiques", 385000, "watch-round", "#475569", "Quartz Master Chronometer, cadran argenté."),
    ]),
    ("Cartier", [
        ("santos-de-cartier", "Santos de Cartier", "classiques", 785000, "watch-square", "#b45309", "Boîtier carré, bracelet QuickSwitch."),
        ("tank-louis", "Tank Louis Cartier", "classiques", 925000, "watch-square", "#1e293b", "Proportions iconiques, cuir alligator."),
        ("ballon-bleu-36", "Ballon Bleu 36", "bijoux", 695000, "watch-round", "#94a3b8", "Couronne protégée, cadran guilloché."),
        ("panthere", "Panthère de Cartier", "bijoux", 2850000, "watch-jewel", "#d4af37", "Bracelet maille, boîtier fin or."),
    ]),
    ("IWC", [
        ("portugieser-chronograph", "Portugieser Chronographe", "classiques", 985000, "watch-chrono", "#0f172a", "Cadran argenté, mouvement manufacture."),
        ("pilot-mark-xx", "Pilot's Watch Mark XX", "sport-chic", 545000, "watch-round", "#14532d", "Instrument de bord, 40 mm."),
    ]),
    ("Tudor", [
        ("black-bay-58", "Black Bay 58", "sport-chic", 385000, "watch-round", "#0c4a6e", "Acier, lunette aluminium, 39 mm."),
        ("pelagos-39", "Pelagos 39", "sport-chic", 485000, "watch-round", "#0369a1", "Titane, 200 m, bracelet caoutchouc."),
    ]),
    ("TAG Heuer", [
        ("carrera-chronograph", "Carrera Chronographe", "sport-chic", 625000, "watch-chrono", "#18181b", "Verre box, index appliqués."),
        ("monaco", "Monaco Calibre Heuer 02", "sport-chic", 785000, "watch-square", "#1d4ed8", "Carré bleu, chronographe automatique."),
    ]),
]

MAKEUP_BRANDS = [
    ("Chanel", [
        ("rouge-allure-velvet", "Rouge Allure Velvet", "levres", 5200, "lipstick", "#9b1c3a", "Fini velours, teinte iconique 58 Rouge Vie."),
        ("les-beiges-foundation", "Les Beiges Eau de Teint", "teint", 7800, "foundation", "#e8c4a8", "Fond de teint léger, éclat naturel."),
        ("inimitable-mascara", "Inimitable Mascara", "yeux", 4900, "mascara", "#111827", "Volume et courbe, brosse conique."),
        ("ombres-premiere", "Ombres Première Quadra", "yeux", 8900, "palette", "#c4a484", "Quatuor satiné, tenue longue."),
    ]),
    ("Dior", [
        ("rouge-dior-satin", "Rouge Dior Satin", "levres", 5400, "lipstick", "#b91c4a", "Soin floral, fini satin lumineux."),
        ("forever-skin-glow", "Forever Skin Glow", "teint", 7200, "foundation", "#ddb892", "SPF 35, fini glow, 24 h de tenue."),
        ("diorshow-iconic", "Diorshow Iconic Overcurl", "yeux", 5100, "mascara", "#1f2937", "Recourbe et volume extrême."),
        ("backstage-glow-palette", "Backstage Glow Face Palette", "teint", 6500, "palette", "#fbbf24", "Enlumineur multi-zones."),
    ]),
    ("Yves Saint Laurent", [
        ("rouge-pur-couture", "Rouge Pur Couture", "levres", 5500, "lipstick", "#dc2626", "Étui bijou doré, formule confort."),
        ("all-hours-foundation", "All Hours Foundation", "teint", 6900, "foundation", "#c9a27a", "Mat longue tenue, fini flouté."),
        ("touche-eclat", "Touche Éclat", "teint", 4800, "concealer", "#fde68a", "Correcteur illuminateur culte."),
    ]),
    ("Lancôme", [
        ("l-absolu-rouge", "L'Absolu Rouge Cream", "levres", 4800, "lipstick", "#be123c", "Couleur intense, soin absolu."),
        ("teint-idole-ultra", "Teint Idole Ultra Wear", "teint", 5900, "foundation", "#d6b89a", "24 h, respirant, large gamme de teintes."),
        ("hypnose-mascara", "Hypnôse Mascara", "yeux", 4600, "mascara", "#0f172a", "Volume sans paquets."),
    ]),
    ("Armani Beauty", [
        ("lip-power", "Lip Power Satin", "levres", 4900, "lipstick", "#9f1239", "Couleur longue tenue, fini satin."),
        ("luminous-silk-foundation", "Luminous Silk Foundation", "teint", 7500, "foundation", "#e7c8a8", "Fini seconde peau, micro-films de pigments."),
        ("eyes-to-kill-mascara", "Eyes to Kill Mascara", "yeux", 4700, "mascara", "#111827", "Volume et définition."),
    ]),
    ("Hermès", [
        ("rouge-hermes-satin", "Rouge Hermès Satin", "levres", 7800, "lipstick", "#7f1d1d", "Packaging métal, formule soin."),
        ("plein-air-foundation", "Plein Air Teint", "teint", 8200, "foundation", "#d4a574", "Fini naturel, texture aérienne."),
    ]),
    ("Guerlain", [
        ("rouge-g", "Rouge G Satin", "levres", 6200, "lipstick", "#b45309", "Boîtier personnalisable, recharge."),
        ("parure-gold-foundation", "Parure Gold Foundation", "teint", 9800, "foundation", "#fcd34d", "24 carats de pigments, éclat."),
    ]),
    ("Tom Ford", [
        ("lip-color-satin", "Lip Color Satin", "levres", 5800, "lipstick", "#881337", "Pigments riches, fini satin."),
        ("shade-and-illuminate", "Shade and Illuminate", "teint", 9800, "highlighter", "#f5d0a8", "Contouring crème, fini film."),
    ]),
    ("Charlotte Tilbury", [
        ("matte-revolution", "Matte Revolution", "levres", 4500, "lipstick", "#9d174d", "Mat confortable, teintes iconiques."),
        ("flawless-filter", "Hollywood Flawless Filter", "teint", 5900, "highlighter", "#fde68a", "Glow customizable."),
    ]),
]

def mk_product(pid, brand, slug, name, category, sub, price, kind, color, short, featured=False, badge=None):
    bg = "blue" if category == "montres" else "pink"
    return {
        "id": pid,
        "slug": slug,
        "brand": brand,
        "name": name,
        "category": category,
        "subcategory": sub,
        "price": price,
        "old_price": None,
        "badge": badge,
        "featured": featured,
        "short": short,
        "description": (
            f"{name} sélectionné par Azurine chez {brand}. {short} "
            f"Prix boutique Azurine en francs suisses, TVA incluse. "
            f"Azurine est une boutique indépendante : les marques citées sont la propriété de leurs détenteurs."
        ),
        "details": [
            f"Marque : {brand}",
            "Prix Azurine en CHF, TVA 8.1 % incluse",
            "Authenticité contrôlée à réception",
            "Livraison assurée en Suisse",
        ],
        "variants": {"label": "Finition", "options": [
            {"id": "standard", "name": "Standard", "color": color},
        ]},
        "art": {"kind": kind, "color": color, "bg": bg},
        "stock": 3 if category == "montres" else 18,
        "rating": 4.7 if category == "montres" else 4.6,
        "reviews": 12 + (pid % 40),
        "tags": [brand.lower().replace(" ", "-"), category],
    }

products = []
pid = 1
for brand, items in WATCH_BRANDS:
    for i, (slug, name, sub, price, kind, color, short) in enumerate(items):
        badge = "nouveau" if i == 0 and pid % 5 == 0 else ("best-seller" if i == 1 else None)
        products.append(mk_product(
            pid, brand, f"{slug}", name, "montres", sub, price, kind, color, short,
            featured=i < 2, badge=badge,
        ))
        pid += 1

for brand, items in MAKEUP_BRANDS:
    for i, (slug, name, sub, price, kind, color, short) in enumerate(items):
        products.append(mk_product(
            pid, brand, f"{slug}", name, "maquillage", sub, price, kind, color, short,
            featured=i < 2, badge="best-seller" if i == 0 else None,
        ))
        pid += 1

ROOT.joinpath("products.json").write_text(
    json.dumps(products, ensure_ascii=False, indent=4) + "\n",
    encoding="utf-8",
)
print(f"Wrote {len(products)} products")
