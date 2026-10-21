#!/usr/bin/env python3
"""Generate data/products.json for Azurine — mass catalog (350–450 real SKUs).

Run from repo: python3 scripts/generate-mass-catalog.py
Output: data/products.json (no photo fields).
"""
from __future__ import annotations

import json
import re
import sys
import unicodedata
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "data" / "products.json"

# Swiss retail–aligned (profitable, not inflated vs. earlier catalog).
PRICE_FACTOR = 0.92

# Each item: (slug_suffix, name, subcategory, price_centimes, art_kind, color_hex, short)
CATALOG: list[tuple[str, str, list[tuple[str, str, str, int, str, str, str]]]] = [
    ('Tissot', 'montres', [
        ('prx-powermatic-80', 'PRX Powermatic 80', 'sport-chic', 69500, 'watch-round', '#1e3a5f', 'Acier brossé, cadran bleu, 80 h de réserve.'),
        ('prx-quartz', 'PRX Quartz', 'sport-chic', 42500, 'watch-round', '#334155', 'Quartz précis, bracelet intégré, finitions alternées.'),
        ('gentleman-powermatic-80', 'Gentleman Powermatic 80', 'classiques', 78500, 'watch-round', '#475569', 'Boîtier 40 mm, silicon, Nivachron.'),
        ('le-locle', 'Le Locle Powermatic 80', 'classiques', 89500, 'watch-round', '#0f172a', 'Boîtier tonneau, fond transparent, finition Côtes de Genève.'),
        ('carson-premium', 'Carson Premium Powermatic 80', 'classiques', 72500, 'watch-jewel', '#64748b', 'Index diamants, date à 6 h.'),
        ('chemin-des-tourelles', 'Chemin des Tourelles Powermatic 80', 'classiques', 115000, 'watch-round', '#78350f', 'Inspiré des archives, boîtier 39 mm.'),
        ('seastar-1000', 'Seastar 1000 Powermatic 80', 'sport-chic', 82500, 'watch-round', '#0369a1', 'Plongée 300 m, lunette unidirectionnelle.'),
        ('seastar-2000', 'Seastar 2000 Professional', 'sport-chic', 99500, 'watch-round', '#0c4a6e', 'Valve hélium, 46 mm, cadran gradient.'),
        ('t-touch-connect-solar', 'T-Touch Connect Solar', 'sport-chic', 98500, 'watch-chrono', '#14532d', 'Écran tactile, connecté, énergie solaire.'),
    ]),
    ('Seiko', 'montres', [
        ('skx007', 'Prospex SKX007', 'sport-chic', 48500, 'watch-round', '#0d3d56', 'Plongée automatique 200 m, lunette unidirectionnelle.'),
        ('skx009', 'Prospex SKX009', 'sport-chic', 49500, 'watch-round', '#1e40af', 'Cadran bleu Pepsi, calibre 4R36.'),
        ('skx013', 'Prospex SKX013', 'sport-chic', 46500, 'watch-round', '#0f172a', 'Format compact 37 mm, héritage SKX.'),
        ('srpd51', '5 Sports SRPD51', 'sport-chic', 34500, 'watch-round', '#334155', 'Guilloché bleu, verre Hardlex, 4R36.'),
        ('srpd55', '5 Sports SRPD55', 'sport-chic', 33500, 'watch-round', '#475569', 'Cadran argenté, lunette 24 h.'),
        ('srpe51', '5 Sports SRPE51', 'classiques', 31500, 'watch-round', '#64748b', 'Dress style 40 mm, automatique.'),
        ('srpe03', 'Prospex Turtle SRPE03', 'sport-chic', 52500, 'watch-round', '#14532d', 'Boîtier cushion, 200 m.'),
        ('spb143', 'Prospex SPB143', 'sport-chic', 115000, 'watch-round', '#1e293b', '62MAS reissue, calibre 6R35.'),
        ('spb317', 'Prospex SPB317', 'sport-chic', 125000, 'watch-round', '#0c4a6e', 'Sumo slim, céramique, 200 m.'),
    ]),
    ('Casio', 'montres', [
        ('a168wa', 'Vintage A168WA-1', 'classiques', 16500, 'watch-mini', '#94a3b8', 'Digital rétro, bracelet acier.'),
        ('a700wa', 'Vintage A700WA-7', 'classiques', 17500, 'watch-mini', '#cbd5e1', 'Affichage digital fin.'),
        ('f91w', 'F-91W-1', 'sport-chic', 8900, 'watch-mini', '#111827', 'Chronographe culte, ultra-léger.'),
        ('mdv106', 'Duro MDV-106', 'sport-chic', 14900, 'watch-round', '#0f172a', 'Plongée 200 m, rapport qualité-prix.'),
        ('mdv107', 'Duro MDV-107', 'sport-chic', 15900, 'watch-round', '#1e3a8a', 'Cadran bleu Marlin, 200 m.'),
        ('edifice-efr552', 'Edifice EFR-S108D', 'sport-chic', 18900, 'watch-chrono', '#334155', 'Chronographe quartz sport.'),
        ('edifice-eqb1000', 'Edifice EQB-1000D', 'sport-chic', 28500, 'watch-chrono', '#475569', 'Bluetooth, world time.'),
        ('pro-trek-prw30', 'Pro Trek PRW-30', 'sport-chic', 32500, 'watch-round', '#14532d', 'Triple capteur, solaire.'),
        ('lineage-lcw-m170', 'Lineage LCW-M170TD', 'classiques', 22500, 'watch-round', '#64748b', 'Radio-pilotée, titane.'),
    ]),
    ('G-Shock', 'montres', [
        ('ga2100', 'GA-2100-1A1', 'sport-chic', 13900, 'watch-round', '#111827', 'Casa octogonal slim, Carbon Core Guard.'),
        ('ga2100-1a2', 'GA-2100-1A2', 'sport-chic', 14500, 'watch-round', '#dc2626', 'Accent rouge, résistance aux chocs.'),
        ('gm2100', 'GM-2100-1A', 'sport-chic', 24900, 'watch-round', '#64748b', 'Métal covered, même silhouette iconique.'),
        ('gw5610', 'GW-M5610U-1', 'sport-chic', 18900, 'watch-square', '#0f172a', 'Square solar, radio Multiband 6.'),
        ('gw5000', 'GW-5000U-1', 'sport-chic', 42500, 'watch-square', '#1e293b', 'Origin, DLC, finition premium.'),
        ('gwg2000', 'GWG-2000-1A1', 'sport-chic', 48500, 'watch-round', '#78350f', 'Mudmaster carbone, triple capteur.'),
        ('gwg1000', 'GWG-1000-1A3', 'sport-chic', 62500, 'watch-round', '#14532d', 'Mudmaster analogique, boussole.'),
        ('gstb400', 'GST-B400-1A', 'sport-chic', 32500, 'watch-chrono', '#334155', 'G-Steel slim, Bluetooth.'),
        ('mtgb2000', 'MTG-B2000D-1A', 'sport-chic', 98500, 'watch-chrono', '#475569', 'MT-G carbone & métal.'),
    ]),
    ('Hamilton', 'montres', [
        ('khaki-field-mechanical', 'Khaki Field Mechanical', 'sport-chic', 49500, 'watch-round', '#14532d', '38 mm, calibre H-50, NATO.'),
        ('khaki-field-auto', 'Khaki Field Auto 40', 'sport-chic', 62500, 'watch-round', '#166534', 'Automatique 80 h, cadran noir.'),
        ('khaki-murph', 'Khaki Field Murph', 'classiques', 99500, 'watch-round', '#1e293b', 'Edition Interstellar, H-10.'),
        ('khaki-aviation-pilot', 'Khaki Aviation Pilot Day Date', 'sport-chic', 78500, 'watch-round', '#0f172a', 'Calendrier, aviation heritage.'),
        ('jazzmaster-open-heart', 'Jazzmaster Open Heart', 'classiques', 89500, 'watch-jewel', '#64748b', 'Cœur visible, 40 mm.'),
        ('jazzmaster-maestro', 'Jazzmaster Maestro Auto', 'classiques', 82500, 'watch-round', '#475569', 'Petite seconde, classique.'),
        ('ventura-elvis80', 'Ventura Elvis80 Auto', 'classiques', 98500, 'watch-square', '#111827', 'Triangle iconique, mouvement auto.'),
        ('intra-matic-auto', 'Intra-Matic Auto', 'classiques', 115000, 'watch-round', '#334155', 'Chronographe vintage panda.'),
        ('navy-pioneer', 'Navy Pioneer Small Second', 'classiques', 72500, 'watch-round', '#78350f', 'Petite seconde, boîtier fin.'),
    ]),
    ('Longines', 'montres', [
        ('master-collection', 'Master Collection 40', 'classiques', 225000, 'watch-jewel', '#1e293b', 'Phase de lune, calibre L899.'),
        ('hydroconquest-41', 'HydroConquest 41', 'sport-chic', 145000, 'watch-round', '#0c4a6e', 'Plongée 300 m, céramique.'),
        ('hydroconquest-43', 'HydroConquest 43 Chrono', 'sport-chic', 185000, 'watch-chrono', '#0369a1', 'Chronographe plongée.'),
        ('conquest-41', 'Conquest 41', 'classiques', 165000, 'watch-round', '#475569', 'Sport-élégant, cadran sunburst.'),
        ('flagship-heritage', 'Flagship Heritage', 'classiques', 195000, 'watch-round', '#64748b', 'Reédition 1950s, verre box.'),
        ('spirit-40', 'Spirit 40', 'classiques', 215000, 'watch-round', '#0f172a', 'Certifié chronomètre, 5 étoiles.'),
        ('dolcevita', 'DolceVita', 'bijoux', 175000, 'watch-jewel', '#d4af37', 'Rectangulaire, finitions joaillières.'),
        ('presence-34', 'Presence 34', 'classiques', 125000, 'watch-round', '#94a3b8', 'Ultra-fin, quartz précision.'),
        ('record-38', 'Record 38', 'classiques', 205000, 'watch-round', '#334155', 'Chronomètre certifié COSC.'),
    ]),
    ('Citizen', 'montres', [
        ('promaster-diver-ny0084', 'Promaster Diver NY0084-89L', 'sport-chic', 38500, 'watch-round', '#1e40af', 'Plongée 200 m, cadran bleu.'),
        ('promaster-skyhawk', 'Promaster Skyhawk A-T', 'sport-chic', 52500, 'watch-chrono', '#0f172a', 'Radio-pilotée, world timer.'),
        ('eco-drive-one', 'Eco-Drive One AR5024', 'classiques', 89500, 'watch-round', '#64748b', 'Ultra-fin 2,98 mm, titane.'),
        ('attesa-hakuto', 'Attesa Hakuto-R Collaboration', 'classiques', 185000, 'watch-round', '#334155', 'Titane Super Titanium, GPS.'),
        ('series8-890', 'Series 8 890', 'sport-chic', 225000, 'watch-round', '#111827', 'Design angulaire, 100 m.'),
        ('tsuyosa-nj0150', 'Tsuyosa NJ0150', 'classiques', 42500, 'watch-round', '#0369a1', 'Automatique accessible, sunburst.'),
        ('citizen-chronograph-ca4500', 'Chronograph CA4500', 'sport-chic', 29500, 'watch-chrono', '#475569', 'Chronographe Eco-Drive.'),
        ('bullhead-challenge', 'Bullhead Challenge Timer', 'sport-chic', 68500, 'watch-chrono', '#dc2626', 'Compteur panda, édition limitée.'),
        ('fugu-ny0040', 'Fugu NY0040-09W', 'sport-chic', 32500, 'watch-round', '#14532d', 'Plongée vintage, lunette notched.'),
    ]),
    ('Orient', 'montres', [
        ('bambino-v4', 'Bambino Version 4', 'classiques', 22500, 'watch-round', '#1e293b', 'Dress watch, dome crystal.'),
        ('bambino-small-seconds', 'Bambino Small Seconds', 'classiques', 24500, 'watch-round', '#475569', 'Petite seconde à 6 h.'),
        ('kamasu', 'Kamasu RA-AA0002', 'sport-chic', 28500, 'watch-round', '#0c4a6e', 'Plongée 200 m, lunette aluminium.'),
        ('mako-iii', 'Mako III FAA02001B9', 'sport-chic', 26500, 'watch-round', '#1e40af', 'Diver 200 m, jour/date.'),
        ('ray-ii', 'Ray II FAA02003B9', 'sport-chic', 27500, 'watch-round', '#0f172a', 'Plongée, couronne vissée.'),
        ('triton-sports-diver', 'Triton Sports Diver', 'sport-chic', 42500, 'watch-round', '#0369a1', '600 m, calibre F6922.'),
        ('star-classic', 'Star Classic RE-AU0001', 'classiques', 48500, 'watch-round', '#64748b', 'Open heart, automatique.'),
        ('contemporary-gmt', 'Contemporary GMT RA-AC0J', 'sport-chic', 38500, 'watch-chrono', '#14532d', 'GMT 24 h, 41 mm.'),
        ('sports-m-force', 'Sports M-Force RA-AC0L', 'sport-chic', 39500, 'watch-round', '#334155', 'Boîtier renforcé, 100 m.'),
    ]),
    ('Mido', 'montres', [
        ('ocean-star-600', 'Ocean Star 600 Chronometer', 'sport-chic', 145000, 'watch-round', '#0369a1', 'Plongée 600 m, COSC.'),
        ('ocean-star-tribute', 'Ocean Star Tribute', 'sport-chic', 115000, 'watch-round', '#0c4a6e', 'Réédition 1960s, 40,5 mm.'),
        ('baroncelli', 'Baroncelli Signature', 'classiques', 125000, 'watch-jewel', '#64748b', 'Classique 40 mm, silicon.'),
        ('commander-iconic', 'Commander Iconic', 'classiques', 98500, 'watch-round', '#475569', 'Boîtier monocoque, date.'),
        ('multifort-tv', 'Multifort TV Big Date', 'sport-chic', 105000, 'watch-square', '#334155', 'Big date, design TV.'),
        ('belluna-royal', 'Belluna Royal', 'classiques', 89500, 'watch-round', '#1e293b', 'Élégance quotidienne, 80 h.'),
        ('rainflower', 'Rainflower M043', 'bijoux', 115000, 'watch-jewel', '#e8e4dc', 'Femme, motif fleur de lotus.'),
        ('commander-gradient', 'Commander Gradient', 'classiques', 108000, 'watch-round', '#1e40af', 'Cadran dégradé, automatique.'),
        ('ocean-star-leader', 'Ocean Star Leader 36', 'sport-chic', 95000, 'watch-round', '#0f172a', 'Format compact, 200 m.'),
    ]),
    ('Certina', 'montres', [
        ('ds-action-diver', 'DS Action Diver 43', 'sport-chic', 89500, 'watch-round', '#0c4a6e', 'Plongée 300 m, Powermatic 80.'),
        ('ds-action-diver-38', 'DS Action Diver 38', 'sport-chic', 82500, 'watch-round', '#0369a1', 'Taille unisexe, lunette céramique.'),
        ('ds-cascadeur', 'DS Cascadeur', 'sport-chic', 72500, 'watch-round', '#334155', 'Boîtier coussin, 300 m.'),
        ('ds-1-big-date', 'DS-1 Big Date', 'classiques', 68500, 'watch-round', '#475569', 'Grande date, sport-chic.'),
        ('ds-podium-chrono', 'DS Podium Chronograph', 'sport-chic', 98500, 'watch-chrono', '#111827', 'Chronographe automatique.'),
        ('ds-8-chronometer', 'DS-8 Chronometer', 'classiques', 115000, 'watch-round', '#64748b', 'Certifié COSC, 41 mm.'),
        ('ds-caimano', 'DS Caimano', 'classiques', 62500, 'watch-round', '#1e293b', 'Quartz précision, DS concept.'),
        ('ds-7-chrono-auto', 'DS-7 Chrono Auto', 'sport-chic', 105000, 'watch-chrono', '#14532d', 'Chronographe 42 mm.'),
        ('ds-super-ph500m', 'DS Super PH500M', 'sport-chic', 125000, 'watch-round', '#0f172a', 'Réédition plongée 500 m.'),
    ]),
    ('Swatch', 'montres', [
        ('moonswatch-mission-moon', 'Bioceramic MoonSwatch Mission to the Moon', 'sport-chic', 28500, 'watch-chrono', '#94a3b8', 'Quartz chronographe, biocéramique.'),
        ('moonswatch-mission-mercury', 'MoonSwatch Mission to Mercury', 'sport-chic', 28500, 'watch-chrono', '#64748b', 'Palette grise, bracelet Velcro.'),
        ('moonswatch-mission-mars', 'MoonSwatch Mission to Mars', 'sport-chic', 28500, 'watch-chrono', '#dc2626', 'Accent rouge, édition Omega x Swatch.'),
        ('moonswatch-mission-jupiter', 'MoonSwatch Mission to Jupiter', 'sport-chic', 28500, 'watch-chrono', '#d97706', 'Teinte sable et orange.'),
        ('big-bold-irony', 'Big Bold Irony', 'sport-chic', 16500, 'watch-round', '#111827', 'Acier, cadran oversize.'),
        ('gent', 'Gent', 'classiques', 8900, 'watch-round', '#475569', 'Icône Swatch, quartz coloré.'),
        ('originals-replay', 'Originals Replay', 'classiques', 9900, 'watch-round', '#1e40af', 'Look 1980s, bracelet silicone.'),
        ('skin-classique', 'Skin Classic', 'classiques', 12500, 'watch-round', '#334155', 'Ultra-fin 3,9 mm.'),
        ('scubaqua', 'Scubaqua', 'sport-chic', 13900, 'watch-round', '#0369a1', 'Plongée loisir, 200 m.'),
    ]),
    ('Frederique Constant', 'montres', [
        ('classics-index-automatic', 'Classics Index Automatic', 'classiques', 89500, 'watch-round', '#64748b', 'Manufacture accessible, 40 mm.'),
        ('classics-carree', 'Classics Carrée', 'classiques', 98500, 'watch-square', '#475569', 'Boîtier carré, automatique.'),
        ('slimline-moonphase', 'Slimline Moonphase Manufacture', 'classiques', 145000, 'watch-jewel', '#1e293b', 'Phase de lune, calibre maison.'),
        ('highlife-perpetual', 'Highlife Perpetual Calendar', 'classiques', 285000, 'watch-round', '#334155', 'Calendrier perpétuel manufacture.'),
        ('runabout', 'Runabout Automatic', 'classiques', 115000, 'watch-round', '#78350f', 'Inspiré bateaux Runabout.'),
        ('classics-art-deco', 'Classics Art Déco', 'bijoux', 105000, 'watch-jewel', '#d4af37', 'Rectangulaire, finitions dorées.'),
        ('yacht-timer', 'Yacht Timer Regatta', 'sport-chic', 125000, 'watch-chrono', '#0c4a6e', 'Compte à rebours régate.'),
        ('manufacture-heartbeat', 'Manufacture Heart Beat', 'classiques', 165000, 'watch-jewel', '#0f172a', 'Balancier visible à 6 h.'),
        ('classics-business-timer', 'Classics Business Timer', 'classiques', 95000, 'watch-chrono', '#111827', 'Chronographe habillé.'),
    ]),
    ('Rado', 'montres', [
        ('captain-cook-42', 'Captain Cook 42', 'sport-chic', 225000, 'watch-round', '#14532d', 'Plongée, lunette céramique, 300 m.'),
        ('captain-cook-37', 'Captain Cook 37', 'sport-chic', 215000, 'watch-round', '#166534', 'Format vintage, automatique.'),
        ('true-square', 'True Square', 'classiques', 195000, 'watch-square', '#111827', 'Céramique haute-tech, carré.'),
        ('true-round', 'True Round Open Heart', 'classiques', 185000, 'watch-jewel', '#64748b', 'Céramique plasma, open heart.'),
        ('centrix-automatic', 'Centrix Automatic', 'classiques', 165000, 'watch-jewel', '#475569', 'Bracelet céramique, index diamants.'),
        ('diaaster-origins', 'DiaStar Original', 'classiques', 175000, 'watch-round', '#94a3b8', 'Hardmetal, design 1962.'),
        ('florence-classic', 'Florence Classic', 'bijoux', 145000, 'watch-jewel', '#d4af37', 'Bracelet maille milanaise.'),
        ('hyperchrome-chrono', 'HyperChrome Chronograph', 'sport-chic', 235000, 'watch-chrono', '#0f172a', 'Chronographe céramique monobloc.'),
        ('golden-horse', 'Golden Horse 1957', 'classiques', 205000, 'watch-round', '#78350f', 'Réédition, logo hippocampe.'),
    ]),
    ('TAG Heuer', 'montres', [
        ('carrera-chronograph', 'Carrera Chronographe', 'sport-chic', 625000, 'watch-chrono', '#18181b', 'Verre box, calibre Heuer 02.'),
        ('carrera-date', 'Carrera Date 36', 'classiques', 385000, 'watch-round', '#334155', 'Femme, acier & or rose.'),
        ('monaco-calibre-heuer02', 'Monaco Calibre Heuer 02', 'sport-chic', 785000, 'watch-square', '#1d4ed8', 'Chronographe carré iconique.'),
        ('aquaracer-professional-300', 'Aquaracer Professional 300', 'sport-chic', 425000, 'watch-round', '#0c4a6e', 'Plongée 300 m, lunette céramique.'),
        ('aquaracer-solargraph', 'Aquaracer Professional 200 Solargraph', 'sport-chic', 325000, 'watch-round', '#0369a1', 'Solaire, 200 m.'),
        ('formula-1-chronograph', 'Formula 1 Chronograph', 'sport-chic', 245000, 'watch-chrono', '#dc2626', 'Chronographe quartz sport.'),
        ('autavia-flyback', 'Autavia Flyback Chronograph', 'sport-chic', 685000, 'watch-chrono', '#14532d', 'Flyback, lunette bi-directionnelle.'),
        ('connected-calibre-e4', 'Connected Calibre E4 45mm', 'sport-chic', 285000, 'watch-chrono', '#111827', 'Montre connectée sport.'),
        ('link-calendar', 'Link Calendar', 'classiques', 325000, 'watch-round', '#64748b', 'Bracelet S, date.'),
    ]),
    ('Tudor', 'montres', [
        ('black-bay-58', 'Black Bay 58', 'sport-chic', 385000, 'watch-round', '#0c4a6e', '39 mm, lunette aluminium.'),
        ('black-bay-41', 'Black Bay 41', 'sport-chic', 425000, 'watch-round', '#0f172a', 'Silhouette classique, MT5602.'),
        ('black-bay-chrono', 'Black Bay Chrono', 'sport-chic', 585000, 'watch-chrono', '#111827', 'Chronographe manufacture.'),
        ('pelagos-39', 'Pelagos 39', 'sport-chic', 485000, 'watch-round', '#0369a1', 'Titane, 200 m, T-fit.'),
        ('pelagos-fxd', 'Pelagos FXD', 'sport-chic', 525000, 'watch-round', '#1e293b', 'Boîtier fixe, Marine Nationale.'),
        ('royal-41', 'Royal 41', 'classiques', 325000, 'watch-jewel', '#64748b', 'Index diamants, 41 mm.'),
        ('1926-36', '1926 36', 'classiques', 245000, 'watch-round', '#475569', 'Entrée de gamme Tudor, 36 mm.'),
        ('black-bay-pro', 'Black Bay Pro', 'sport-chic', 485000, 'watch-round', '#14532d', 'GMT 24 h, 39 mm.'),
        ('black-bay-ceramic', 'Black Bay Ceramic', 'sport-chic', 525000, 'watch-round', '#334155', 'Céramique monobloc, Master Chronometer.'),
    ]),
    ('Breitling', 'montres', [
        ('navitimer-b01', 'Navitimer B01 Chronograph 41', 'sport-chic', 985000, 'watch-chrono', '#1e293b', 'Règle à calcul, calibre B01.'),
        ('superocean-heritage-57', "Superocean Heritage '57", 'sport-chic', 625000, 'watch-round', '#0c4a6e', 'Plongée rétro, lunette concave.'),
        ('superocean-automatic-36', 'Superocean Automatic 36', 'sport-chic', 485000, 'watch-round', '#0369a1', 'Plongée 200 m, taille 36 mm.'),
        ('avenger-automatic-42', 'Avenger Automatic 42', 'sport-chic', 425000, 'watch-round', '#14532d', 'Instrument robuste, 300 m.'),
        ('premier-b01', 'Premier B01 Chronograph 42', 'classiques', 895000, 'watch-chrono', '#475569', 'Chronographe habillé.'),
        ('chronomat-42', 'Chronomat 42', 'sport-chic', 825000, 'watch-chrono', '#64748b', 'Lunette riders, Rouleaux bracelet.'),
        ('endurance-pro', 'Endurance Pro', 'sport-chic', 385000, 'watch-chrono', '#dc2626', 'Breitlight, chronographe léger.'),
        ('superocean-heritage-ii-46', 'Superocean Heritage II 46', 'sport-chic', 725000, 'watch-round', '#0f172a', 'Grande taille, lunette céramique.'),
        ('avenger-b01-chrono', 'Avenger B01 Chronograph 44', 'sport-chic', 985000, 'watch-chrono', '#111827', 'Chronographe manufacture.'),
    ]),
    ('Montblanc', 'montres', [
        ('1858-Geosphere', '1858 Geosphere 42', 'sport-chic', 685000, 'watch-round', '#14532d', 'Double hémisphère, GMT.'),
        ('1858-unveiled-limited', '1858 Unveiled Secret Minerva', 'classiques', 2850000, 'watch-round', '#78350f', 'Manufacture Minerva, édition limitée.'),
        ('star-legacy-leaf', 'Star Legacy Suspended Exo Tourbillon', 'classiques', 3850000, 'watch-jewel', '#d4af37', 'Haute horlogerie, tourbillon.'),
        ('heritage-spirit-date', 'Heritage Spirit Date', 'classiques', 325000, 'watch-round', '#475569', 'Classique 39 mm, date.'),
        ('timewalker-chronograph', 'TimeWalker Chronograph', 'sport-chic', 485000, 'watch-chrono', '#111827', 'Chronographe sport élégant.'),
        ('tradition-date-automatic', 'Tradition Date Automatic', 'classiques', 285000, 'watch-round', '#64748b', 'Guilloché, automatique.'),
        ('boheme-date', 'Boheme Date Automatic', 'bijoux', 325000, 'watch-jewel', '#e8e4dc', 'Femme, diamants, 34 mm.'),
        ('summit-3', 'Summit 3 Smartwatch', 'sport-chic', 985000, 'watch-round', '#334155', 'Wear OS, titane.'),
    ]),
    ('Zenith', 'montres', [
        ('chronomaster-sport', 'Chronomaster Sport', 'sport-chic', 985000, 'watch-chrono', '#111827', 'El Primero 3600, 1/10 seconde.'),
        ('chronomaster-open', 'Chronomaster Open', 'sport-chic', 1125000, 'watch-chrono', '#475569', 'Cadran ouvert sur régulateur.'),
        ('defy-classic', 'Defy Classic', 'sport-chic', 825000, 'watch-round', '#64748b', 'Titane, skeleton.'),
        ('defy-skyline', 'Defy Skyline', 'sport-chic', 925000, 'watch-round', '#0c4a6e', '41 mm, El Primero 3620.'),
        ('elite-classic', 'Elite Classic 40', 'classiques', 685000, 'watch-round', '#334155', 'Ultra-fin, date.'),
        ('pilot-big-date', 'Pilot Big Date Flyback', 'sport-chic', 985000, 'watch-chrono', '#14532d', 'Grande date, flyback.'),
        ('pilot-type20', 'Pilot Type 20 Extra Special', 'sport-chic', 725000, 'watch-round', '#78350f', 'Grande couronne, 45 mm.'),
        ('chronomaster-original', 'Chronomaster Original', 'sport-chic', 895000, 'watch-chrono', '#1e293b', 'Réédition 1969 tri-color.'),
        ('elite-moonphase', 'Elite Moonphase', 'classiques', 785000, 'watch-jewel', '#94a3b8', 'Phase de lune, 40,5 mm.'),
    ]),
    ('Omega', 'montres', [
        ('speedmaster-moonwatch', 'Speedmaster Professional Moonwatch', 'sport-chic', 785000, 'watch-chrono', '#1f2937', 'Calibre 3861, héritage lunaire.'),
        ('speedmaster-57', "Speedmaster '57", 'sport-chic', 925000, 'watch-chrono', '#78350f', 'Co-Axial Master Chronometer.'),
        ('seamaster-diver-300m', 'Seamaster Diver 300M', 'sport-chic', 625000, 'watch-round', '#0e7490', 'Vague cadran, valve hélium.'),
        ('seamaster-planet-ocean', 'Seamaster Planet Ocean 600M', 'sport-chic', 785000, 'watch-round', '#0c4a6e', 'Plongée 600 m, céramique.'),
        ('seamaster-aqua-terra', 'Seamaster Aqua Terra 150M', 'classiques', 685000, 'watch-round', '#475569', 'Quotidien, Master Chronometer.'),
        ('constellation-41', 'Constellation 41', 'classiques', 545000, 'watch-jewel', '#334155', 'Griffes, finitions premium.'),
        ('de-ville-prestige', 'De Ville Prestige 39', 'classiques', 385000, 'watch-round', '#64748b', 'Classique, Co-Axial.'),
        ('de-ville-tresor', 'De Ville Trésor Master Co-Axial', 'classiques', 685000, 'watch-jewel', '#d4af37', 'Or 18K, ultra-fin.'),
        ('constellation-manhattan', 'Constellation Manhattan', 'bijoux', 625000, 'watch-jewel', '#e8e4dc', 'Femme, demi-lune.'),
        ('speedmaster-dark-side', 'Speedmaster Dark Side of the Moon', 'sport-chic', 1125000, 'watch-chrono', '#111827', 'Céramique noire, chronographe.'),
    ]),
    ('IWC', 'montres', [
        ('portugieser-chronograph', 'Portugieser Chronographe', 'classiques', 985000, 'watch-chrono', '#0f172a', 'Cadran argenté, manufacture.'),
        ('pilot-mark-xx', "Pilot's Watch Mark XX", 'sport-chic', 545000, 'watch-round', '#14532d', '40 mm, instrument de bord.'),
        ('pilot-chronograph-41', "Pilot's Watch Chronograph 41", 'sport-chic', 685000, 'watch-chrono', '#334155', 'Chronographe day-date.'),
        ('big-pilot-43', "Big Pilot's Watch 43", 'sport-chic', 985000, 'watch-round', '#111827', 'Grande couronne, 43 mm.'),
        ('portugieser-perpetual', 'Portugieser Perpetual Calendar 44', 'classiques', 3850000, 'watch-round', '#475569', 'Calendrier perpétuel, 7 jours.'),
        ('aquatimer-automatic', 'Aquatimer Automatic 2000', 'sport-chic', 825000, 'watch-round', '#0c4a6e', 'Plongée 2000 m, SafeDive.'),
        ('portofino-automatic-37', 'Portofino Automatic 37', 'classiques', 485000, 'watch-jewel', '#64748b', 'Classique 37 mm, date.'),
        ('ingenieur-automatic-40', 'Ingenieur Automatic 40', 'sport-chic', 925000, 'watch-round', '#94a3b8', 'Design Genta, 40 mm.'),
        ('pilot-top-gun-chrono', "Pilot's Watch Chronograph Top Gun", 'sport-chic', 1125000, 'watch-chrono', '#1e293b', 'Céramique noire, Top Gun.'),
        ('da-vinci-automatic', 'Da Vinci Automatic 36', 'bijoux', 785000, 'watch-jewel', '#e8e4dc', 'Femme, lunette diamants.'),
    ]),
    ('Cartier', 'montres', [
        ('santos-de-cartier', 'Santos de Cartier', 'classiques', 785000, 'watch-square', '#b45309', 'QuickSwitch, SmartLink.'),
        ('tank-louis', 'Tank Louis Cartier', 'classiques', 925000, 'watch-square', '#1e293b', 'Proportions iconiques, cuir.'),
        ('ballon-bleu-36', 'Ballon Bleu 36', 'bijoux', 695000, 'watch-round', '#94a3b8', 'Couronne protégée, guilloché.'),
        ('panthere', 'Panthère de Cartier', 'bijoux', 2850000, 'watch-jewel', '#d4af37', 'Bracelet maille, boîtier fin.'),
        ('tank-must', 'Tank Must', 'classiques', 325000, 'watch-square', '#475569', 'Entrée de gamme Tank, quartz ou auto.'),
        ('santos-dumont', 'Santos-Dumont', 'classiques', 485000, 'watch-square', '#64748b', 'Silhouette aviateur, ultra-fin.'),
        ('tank-francaise', 'Tank Française', 'classiques', 585000, 'watch-square', '#334155', 'Bracelet intégré, redesign 2024.'),
        ('drive-de-cartier', 'Drive de Cartier', 'classiques', 685000, 'watch-round', '#78350f', 'Boîtier coussin, élégance.'),
        ('ronde-must', 'Ronde Must de Cartier', 'classiques', 285000, 'watch-round', '#111827', 'Ronde accessible, quartz.'),
        ('baignoire', 'Baignoire', 'bijoux', 1850000, 'watch-jewel', '#d4af37', 'Forme ovale joaillière.'),
    ]),
    ('Hublot', 'montres', [
        ('big-bang-unico', 'Big Bang Unico 42', 'sport-chic', 1890000, 'watch-chrono', '#111827', 'Carbone & titane, Unico.'),
        ('classic-fusion-titanium', 'Classic Fusion Titanium 45', 'classiques', 985000, 'watch-round', '#64748b', 'Titane satiné, fusion design.'),
        ('spirit-of-big-bang', 'Spirit of Big Bang 42', 'sport-chic', 2450000, 'watch-chrono', '#7c2d12', 'Tonneau, chronographe.'),
        ('big-bang-integrale', 'Big Bang Integral', 'sport-chic', 2250000, 'watch-chrono', '#334155', 'Bracelet intégré céramique.'),
        ('classic-fusion-chronograph', 'Classic Fusion Chronograph Titanium', 'sport-chic', 1250000, 'watch-chrono', '#475569', 'Chronographe titane 45 mm.'),
        ('big-bang-meca-10', 'Big Bang Meca-10', 'sport-chic', 2850000, 'watch-round', '#0f172a', 'Réserve 10 jours, manufacture.'),
        ('square-bang-unico', 'Square Bang Unico', 'sport-chic', 2650000, 'watch-square', '#111827', 'Carré, céramique, Unico.'),
        ('classic-fusion-racing-grey', 'Classic Fusion Racing Grey', 'sport-chic', 1085000, 'watch-chrono', '#64748b', 'Chronographe 45 mm.'),
    ]),
    ('Audemars Piguet', 'montres', [
        ('royal-oak-15500', 'Royal Oak 15500ST', 'sport-chic', 4280000, 'watch-round', '#708090', 'Tapisserie bleue, 41 mm.'),
        ('royal-oak-offshore', 'Royal Oak Offshore 42', 'sport-chic', 5120000, 'watch-chrono', '#1a202c', 'Chronographe, couronne protégée.'),
        ('code-11-59', 'Code 11.59 Selfwinding', 'classiques', 3890000, 'watch-jewel', '#f5f0e8', 'Boîtier biconvexe, laqué.'),
        ('royal-oak-jumbo', 'Royal Oak Jumbo Extra-Thin', 'sport-chic', 5850000, 'watch-round', '#94a3b8', '15202ST, ultra-fin.'),
        ('royal-oak-chronograph', 'Royal Oak Chronograph', 'sport-chic', 4850000, 'watch-chrono', '#475569', 'Chronographe tapisserie.'),
        ('royal-oak-offshore-diver', 'Royal Oak Offshore Diver', 'sport-chic', 3250000, 'watch-round', '#0c4a6e', 'Plongée 300 m, offshore.'),
        ('code-11-59-chronograph', 'Code 11.59 Chronograph', 'classiques', 4250000, 'watch-chrono', '#78350f', 'Chronographe manufacture.'),
        ('royal-oak-perpetual', 'Royal Oak Perpetual Calendar', 'classiques', 8950000, 'watch-round', '#334155', 'Calendrier perpétuel.'),
    ]),
    ('Patek Philippe', 'montres', [
        ('nautilus-5711', 'Nautilus 5711/1A', 'sport-chic', 8950000, 'watch-round', '#4a5568', 'Acier, cadran bleu soleil.'),
        ('calatrava-5227', 'Calatrava 5227G', 'classiques', 3250000, 'watch-jewel', '#e8e4dc', 'Or gris, guilloché.'),
        ('aquanaut-5167', 'Aquanaut 5167A', 'sport-chic', 4850000, 'watch-round', '#2d3748', 'Caoutchouc tropical.'),
        ('complications-5205', 'Complications 5205R', 'classiques', 5680000, 'watch-round', '#3d2914', 'Phase de lune, calendrier annuel.'),
        ('nautilus-5712', 'Nautilus 5712/1A Moon Phases', 'sport-chic', 12500000, 'watch-round', '#64748b', 'Phase de lune, réserve.'),
        ('aquanaut-5968', 'Aquanaut 5968A Chronograph', 'sport-chic', 9850000, 'watch-chrono', '#475569', 'Chronographe orange hand.'),
        ('calatrava-6119', 'Calatrava 6119G', 'classiques', 3850000, 'watch-jewel', '#d4af37', 'Clous de Paris, or gris.'),
        ('grand-complications-5327', 'Grand Complications 5327G', 'classiques', 18500000, 'watch-round', '#1e293b', 'Calendrier perpétuel, répétition.'),
        ('twenty4-7300', 'Twenty~4 7300', 'bijoux', 4850000, 'watch-jewel', '#e8e4dc', 'Femme, quartz ou automatique.'),
        ('golden-ellipse-5738', 'Golden Ellipse 5738G', 'classiques', 4250000, 'watch-jewel', '#78350f', 'Forme ellipse, or gris.'),
    ]),
    ('Rolex', 'montres', [
        ('submariner-date-41', 'Submariner Date 41', 'classiques', 1285000, 'watch-round', '#0d3d56', 'Cerachrom, calibre 3235.'),
        ('datejust-36', 'Datejust 36', 'classiques', 985000, 'watch-jewel', '#1a4a6e', 'Fluted bezel, Jubilee.'),
        ('day-date-40', 'Day-Date 40', 'classiques', 3850000, 'watch-round', '#2a1810', 'Or jaune, President bracelet.'),
        ('gmt-master-ii', 'GMT-Master II', 'sport-chic', 1125000, 'watch-chrono', '#1e3a5f', 'Bi-color Pepsi, GMT.'),
        ('daytona-116500', 'Cosmograph Daytona', 'sport-chic', 1685000, 'watch-chrono', '#111827', 'Cerachrom, calibre 4130.'),
        ('explorer-36', 'Explorer 36', 'sport-chic', 785000, 'watch-round', '#14532d', '3-6-9, Chromalight.'),
        ('explorer-ii', 'Explorer II', 'sport-chic', 985000, 'watch-round', '#f97316', 'GMT orange, 42 mm.'),
        ('yacht-master-40', 'Yacht-Master 40', 'sport-chic', 1185000, 'watch-round', '#64748b', 'Rolesium, lunette numérotée.'),
        ('sea-dweller', 'Sea-Dweller', 'sport-chic', 1385000, 'watch-round', '#0c4a6e', 'Valve hélium, 1220 m.'),
        ('deepsea', 'Deepsea Challenge', 'sport-chic', 1585000, 'watch-round', '#0f172a', 'Plongée extrême, titane RLX.'),
        ('oyster-perpetual-36', 'Oyster Perpetual 36', 'classiques', 685000, 'watch-round', '#2563eb', 'Turquoise, boîtier Oyster.'),
        ('sky-dweller', 'Sky-Dweller', 'classiques', 1685000, 'watch-round', '#78350f', 'Calendrier annuel, GMT.'),
    ]),
    ('Nivea', 'maquillage', [
        ('creme-hydratante', 'Crème Hydratante 24h', 'teint', 890, 'foundation', '#fde68a', 'Hydratation quotidienne, peaux normales.'),
        ('soft-creme', 'Soft Crème', 'teint', 790, 'balm', '#fef3c7', 'Texture légère, usage corps et visage.'),
        ('cellular-luminous630', 'Cellular Luminous630 Anti-Taches', 'teint', 2490, 'concealer', '#e8c4a8', 'Sérum anti-taches, teint unifié.'),
        ('q10-power-anti-rides', 'Q10 Power Anti-Rides', 'teint', 1890, 'foundation', '#ddb892', 'Crème jour SPF 15, Q10.'),
        ('men-sensitive', 'Men Sensitive Crème', 'teint', 1290, 'balm', '#cbd5e1', 'Après-rasage, apaisante.'),
        ('lip-care-hydro', 'Lip Care Hydro Care', 'levres', 590, 'balm', '#fca5a5', 'Baume SPF, réparation 24 h.'),
        ('micellair-skin-breathe', 'MicellAir Skin Breathe', 'teint', 990, 'spray', '#e0f2fe', 'Eau micellaire, peaux sensibles.'),
    ]),
    ("L'Oréal Paris", 'maquillage', [
        ('revitalift-laser', 'Revitalift Laser X3 Sérum', 'teint', 3290, 'concealer', '#e8c4a8', 'Acide hyaluronique, rides.'),
        ('true-match-foundation', 'True Match Foundation', 'teint', 1890, 'foundation', '#d6b89a', '25 teintes, fini naturel.'),
        ('infaillible-32h', 'Infaillible 32H Fresh Wear', 'teint', 2190, 'foundation', '#c9a27a', 'Tenue 32 h, transfert minimal.'),
        ('telescopic-mascara', 'Telescopic Mascara', 'yeux', 1690, 'mascara', '#111827', 'Allonge les cils, brosse précise.'),
        ('voluminous-lash-paradise', 'Voluminous Lash Paradise', 'yeux', 1890, 'mascara', '#1f2937', 'Volume intense, formule douce.'),
        ('color-riche-lipstick', 'Color Riche Lipstick', 'levres', 1490, 'lipstick', '#be123c', 'Couleur riche, beurre de jojoba.'),
        ('rouge-signature', 'Rouge Signature Encre', 'levres', 1690, 'gloss', '#9f1239', 'Encre légère, fini mat.'),
        ('infaillible-grip-liner', 'Infaillible Grip 36H Eyeliner', 'yeux', 1290, 'eyeliner', '#0f172a', 'Tenue 36 h, trait précis.'),
        ('paradise-big-deal', 'Paradise Big Deal Mascara', 'yeux', 1990, 'mascara', '#111827', 'Volume XXL, brosse oversize.'),
    ]),
    ('Maybelline', 'maquillage', [
        ('fit-me-foundation', 'Fit Me Matte + Poreless', 'teint', 1290, 'foundation', '#ddb892', 'Matifiant, pores affinés.'),
        ('fit-me-concealer', 'Fit Me Concealer', 'teint', 990, 'concealer', '#fde68a', 'Correcteur fluide, 12 teintes.'),
        ('superstay-30h', 'SuperStay 30H Active Wear', 'teint', 1890, 'foundation', '#c9a27a', 'Tenue sport, respirant.'),
        ('lash-sensational', 'Lash Sensational Mascara', 'yeux', 1490, 'mascara', '#111827', 'Volume fan effect.'),
        ('sky-high-mascara', 'Lash Sensational Sky High', 'yeux', 1690, 'mascara', '#1f2937', 'Extension cils, bambou.'),
        ('superstay-matte-ink', 'SuperStay Matte Ink', 'levres', 1390, 'lipstick', '#881337', 'Encre mat 16 h.'),
        ('color-sensational', 'Color Sensational Lipstick', 'levres', 1190, 'lipstick', '#dc2626', 'Pigments purs, fini satin.'),
        ('tattoo-brow', 'Tattoo Brow Micro Ink', 'yeux', 1690, 'brow', '#78350f', 'Sourcils tenue 24 h.'),
        ('city-bronzer', 'City Bronzer & Highlighter', 'teint', 1490, 'highlighter', '#fbbf24', 'Duo contouring.'),
    ]),
    ('Catrice', 'maquillage', [
        ('hd-liquid-coverage', 'HD Liquid Coverage Foundation', 'teint', 890, 'foundation', '#d6b89a', 'Couvrance modulable, fini naturel.'),
        ('true-skin-hydrating', 'True Skin Hydrating Foundation', 'teint', 990, 'foundation', '#e8c4a8', 'Hydratant, glow léger.'),
        ('liquid-camouflage', 'Liquid Camouflage Concealer', 'teint', 690, 'concealer', '#fde68a', 'Haute couvrance, longue tenue.'),
        ('calligraph-pro', 'Calligraph Pro Eyeliner', 'yeux', 590, 'eyeliner', '#0f172a', 'Trait intense, waterproof.'),
        ('lashes-sensational', 'Lashes to Kill Mascara', 'yeux', 790, 'mascara', '#111827', 'Volume et courbe.'),
        ('slim-matic-lipliner', "Slim'Matic Lip Liner", 'levres', 490, 'lipstick', '#9f1239', 'Contour lèvres précis.'),
        ('demi-matte-lipstick', 'Demi-Matte Lipstick', 'levres', 690, 'lipstick', '#be123c', 'Demi-mat confortable.'),
    ]),
    ('Essence', 'maquillage', [
        ('stay-all-day-foundation', 'Stay All Day 16h Foundation', 'teint', 490, 'foundation', '#ddb892', 'Tenue 16 h, petit prix.'),
        ('i-love-extreme-mascara', 'I Love Extreme Crazy Volume', 'yeux', 390, 'mascara', '#111827', 'Volume extrême.'),
        ('lash-princess', 'Lash Princess False Lash Effect', 'yeux', 490, 'mascara', '#1f2937', 'Effet faux-cils, culte TikTok.'),
        ('8h-matte-comfort', '8h Matte Comfort Lipliner', 'levres', 290, 'lipstick', '#881337', 'Mat confortable.'),
        ('soft-touch-breeze', 'Soft Touch Breeze Blush', 'teint', 390, 'blush', '#fb7185', 'Texture mousseline.'),
        ('correct-to-perfection', 'Correct To Perfection', 'teint', 390, 'concealer', '#fde68a', 'Correcteur caméléon.'),
        ('make-me-brow', 'Make Me Brow Eyebrow Gel', 'yeux', 390, 'brow', '#78350f', 'Gel sourcils fibres.'),
    ]),
    ('Rimmel', 'maquillage', [
        ('kind-free-mascara', '& Kind Free Mascara', 'yeux', 1290, 'mascara', '#111827', 'Vegan, sans parfum.'),
        ('lasting-finish-foundation', 'Lasting Finish 25H Foundation', 'teint', 1490, 'foundation', '#d6b89a', 'Tenue 25 h, confort.'),
        ('match-perfection', 'Match Perfection Foundation', 'teint', 1190, 'foundation', '#e8c4a8', 'Fusion peau, SPF.'),
        ('exaggerate-eyeliner', 'Exaggerate Auto Eyeliner', 'yeux', 990, 'eyeliner', '#0f172a', 'Retractable, smoky.'),
        ('stay-matte-liquid', 'Stay Matte Liquid Lipstick', 'levres', 1090, 'lipstick', '#be123c', 'Mat liquide, non transfert.'),
        ('multi-tasker-concealer', 'Multi Tasker Concealer', 'teint', 990, 'concealer', '#fde68a', 'Anti-cernes, contour.'),
        ('natural-bronzer', 'Natural Bronzer', 'teint', 1290, 'blush', '#d97706', 'Bronzer sun-light.'),
        ('wonder-cloud-cream', "Wonder'Cloud Cream Blush", 'teint', 1290, 'blush', '#f472b6', 'Crème aérienne.'),
        ('provocalips', 'Provocalips Liquid Lipstick', 'levres', 1390, 'gloss', '#9f1239', 'Duo base + top coat.'),
    ]),
    ('NYX', 'maquillage', [
        ('bare-with-me-concealer', 'Bare With Me Concealer Serum', 'teint', 1290, 'concealer', '#fde68a', 'Sérum correcteur hydratant.'),
        ('cant-stop-wont-stop', "Can't Stop Won't Stop Foundation", 'teint', 1690, 'foundation', '#c9a27a', 'Mat 24 h, full coverage.'),
        ('micro-brow-pencil', 'Micro Brow Pencil', 'yeux', 890, 'brow', '#78350f', 'Mine ultra-fine.'),
        ('epic-ink-liner', 'Epic Ink Liner', 'yeux', 990, 'eyeliner', '#0f172a', 'Feutre waterproof.'),
        ('soft-matte-lip-cream', 'Soft Matte Lip Cream', 'levres', 890, 'lipstick', '#881337', 'Crème mat légère.'),
        ('fat-cheeks-blush', 'Fat Cheeks Blush', 'teint', 990, 'blush', '#fb7185', 'Blush modulable.'),
        ('born-to-glow', 'Born To Glow Highlighting Palette', 'teint', 1890, 'palette', '#fbbf24', 'Trio illuminateurs.'),
        ('setting-spray-matte', 'Makeup Setting Spray Matte', 'teint', 1290, 'spray', '#e0f2fe', 'Fixateur fini mat.'),
        ('jumbo-eye-pencil', 'Jumbo Eye Pencil', 'yeux', 890, 'eyeliner', '#6366f1', 'Crayon multi-usage.'),
    ]),
    ('Garnier', 'maquillage', [
        ('micellar-cleansing-water', 'Micellar Cleansing Water', 'teint', 990, 'spray', '#e0f2fe', 'Démaquillant tout-en-un.'),
        ('skin-active-vitamin-c', 'Skin Active Vitamin C Booster', 'teint', 1890, 'concealer', '#fde68a', 'Éclat, taches.'),
        ('bb-cream-classic', 'BB Cream Classic Medium', 'teint', 1290, 'foundation', '#ddb892', '5-en-1, SPF 15.'),
        ('fructis-sleek-shine', 'Fructis Sleek & Shine', 'coffrets', 790, 'spray', '#84cc16', 'Shampooing brillance.'),
        ('pure-active-charcoal', 'Pure Active Charcoal Gel', 'teint', 990, 'balm', '#334155', 'Nettoyant anti-imperfections.'),
        ('ambre-solaire-spf50', 'Ambre Solaire Super UV SPF50+', 'teint', 1690, 'spray', '#fef9c3', 'Protection très haute.'),
        ('color-naturals-creme', 'Color Naturals Crème Colorante', 'coffrets', 1190, 'spray', '#78350f', 'Coloration cheveux.'),
    ]),
    ('Chanel', 'maquillage', [
        ('rouge-allure-velvet', 'Rouge Allure Velvet', 'levres', 5200, 'lipstick', '#9b1c3a', 'Fini velours, 58 Rouge Vie.'),
        ('les-beiges-foundation', 'Les Beiges Eau de Teint', 'teint', 7800, 'foundation', '#e8c4a8', 'Fond de teint léger.'),
        ('inimitable-mascara', 'Inimitable Mascara', 'yeux', 4900, 'mascara', '#111827', 'Volume et courbe.'),
        ('ombres-premiere', 'Ombres Première Quadra', 'yeux', 8900, 'palette', '#c4a484', 'Quatuor satiné.'),
        ('sublimage-creme', 'Sublimage La Crème', 'teint', 38500, 'balm', '#fef3c7', 'Soin regeneration premium.'),
        ('bleu-de-chanel', 'Bleu de Chanel Parfum', 'coffrets', 12500, 'spray', '#1e3a5f', 'Parfum masculin iconique.'),
        ('les-beiges-powder', 'Les Beiges Healthy Glow Powder', 'teint', 6900, 'blush', '#fbbf24', 'Poudre bonne mine.'),
        ('le-lift-pro', 'Le Lift Pro Concentré Yeux', 'yeux', 12500, 'concealer', '#fde68a', 'Soin regard liftant.'),
        ('rouge-coco-baume', 'Rouge Coco Baume', 'levres', 4800, 'balm', '#f472b6', 'Baume teinté soin.'),
    ]),
    ('Dior', 'maquillage', [
        ('rouge-dior-satin', 'Rouge Dior Satin', 'levres', 5400, 'lipstick', '#b91c4a', 'Soin floral, satin.'),
        ('forever-skin-glow', 'Forever Skin Glow', 'teint', 7200, 'foundation', '#ddb892', 'SPF 35, glow 24 h.'),
        ('diorshow-iconic', 'Diorshow Iconic Overcurl', 'yeux', 5100, 'mascara', '#1f2937', 'Recourbe extrême.'),
        ('backstage-glow-palette', 'Backstage Glow Face Palette', 'teint', 6500, 'palette', '#fbbf24', 'Enlumineur pro.'),
        ('capture-totale-serum', 'Capture Totale Le Sérum', 'teint', 18500, 'balm', '#fef3c7', 'Anti-âge global.'),
        ('addict-lip-glow', 'Addict Lip Glow', 'levres', 4500, 'balm', '#f472b6', 'Baume révélateur couleur.'),
        ('forever-concealer', 'Forever Skin Correct Concealer', 'teint', 4800, 'concealer', '#fde68a', 'Correcteur 24 h.'),
        ('couleur-wild-eyeshadow', 'Couleur Wild Eyeshadow Palette', 'yeux', 8500, 'palette', '#6366f1', 'Palette couture.'),
        ('dior-show-5-couleurs', 'Diorshow 5 Couleurs', 'yeux', 7900, 'palette', '#c4a484', 'Palette yeux iconique.'),
    ]),
    ('Yves Saint Laurent', 'maquillage', [
        ('rouge-pur-couture', 'Rouge Pur Couture', 'levres', 5500, 'lipstick', '#dc2626', 'Étui bijou doré.'),
        ('all-hours-foundation', 'All Hours Foundation', 'teint', 6900, 'foundation', '#c9a27a', 'Mat longue tenue.'),
        ('touche-eclat', 'Touche Éclat', 'teint', 4800, 'concealer', '#fde68a', 'Correcteur illuminateur culte.'),
        ('libre-edp', 'Libre Eau de Parfum', 'coffrets', 12500, 'spray', '#6366f1', 'Parfum féminin iconique.'),
        ('lash-clash-mascara', 'Lash Clash Mascara', 'yeux', 4900, 'mascara', '#111827', 'Volume max.'),
        ('rouge-volupte-shine', 'Rouge Volupté Shine', 'levres', 5200, 'lipstick', '#be123c', 'Fini brillant soin.'),
        ('all-hours-concealer', 'All Hours Precise Angles Concealer', 'teint', 5200, 'concealer', '#fde68a', 'Anti-cernes mat.'),
        ('couture-mini-clutch', 'Couture Mini Clutch Palette', 'yeux', 9800, 'palette', '#78350f', 'Palette soirée.'),
        ('top-secrets-blur', 'Top Secrets Blur Primer', 'teint', 5800, 'foundation', '#e8c4a8', 'Base floutante.'),
    ]),
    ('Lancôme', 'maquillage', [
        ('l-absolu-rouge', "L'Absolu Rouge Cream", 'levres', 4800, 'lipstick', '#be123c', 'Couleur intense.'),
        ('teint-idole-ultra', 'Teint Idole Ultra Wear', 'teint', 5900, 'foundation', '#d6b89a', '24 h respirant.'),
        ('hypnose-mascara', 'Hypnôse Mascara', 'yeux', 4600, 'mascara', '#0f172a', 'Volume sans paquets.'),
        ('genifique-serum', 'Advanced Génifique Sérum', 'teint', 12500, 'balm', '#fef3c7', 'Sérum jeunesse.'),
        ('la-vie-est-belle', 'La Vie Est Belle Eau de Parfum', 'coffrets', 11800, 'spray', '#f472b6', 'Parfum iris gourmand.'),
        ('idole-mascara', 'Idôle Lash-Lifting Mascara', 'yeux', 4900, 'mascara', '#111827', 'Effet lifting.'),
        ('blush-subtil', 'Blush Subtil', 'teint', 5200, 'blush', '#fb7185', 'Fini naturel modulable.'),
        ('definicils-mascara', 'Définicils Mascara', 'yeux', 4600, 'mascara', '#1f2937', 'Définition précise.'),
        ('absolue-soft-cream', 'Absolue Soft Cream', 'teint', 28500, 'balm', '#fde68a', 'Soin rose grand cru.'),
    ]),
    ('Armani Beauty', 'maquillage', [
        ('lip-power', 'Lip Power Satin', 'levres', 4900, 'lipstick', '#9f1239', 'Longue tenue satin.'),
        ('luminous-silk-foundation', 'Luminous Silk Foundation', 'teint', 7500, 'foundation', '#e7c8a8', 'Fini seconde peau.'),
        ('eyes-to-kill-mascara', 'Eyes to Kill Mascara', 'yeux', 4700, 'mascara', '#111827', 'Volume définition.'),
        ('designer-glow-foundation', 'Designer Glow Foundation', 'teint', 7800, 'foundation', '#ddb892', 'Éclat modulable.'),
        ('neo-nude-tint', 'Neo Nude Tint', 'teint', 4200, 'foundation', '#e8c4a8', 'Teint transparent.'),
        ('prisma-glass-lip', 'Prisma Glass Lip Gloss', 'levres', 4500, 'gloss', '#f472b6', 'Brillance 3D.'),
        ('fluid-master-primer', 'UV Master Primer SPF40', 'teint', 5800, 'foundation', '#fef9c3', 'Base solaire.'),
        ('eye-tint-liquid', 'Eye Tint Liquid Eyeshadow', 'yeux', 4200, 'palette', '#64748b', 'Fard liquide longue tenue.'),
        ('acqua-di-gio', 'Acqua di Giò Eau de Toilette', 'coffrets', 9800, 'spray', '#0ea5e9', 'Parfum marin iconique.'),
    ]),
    ('Guerlain', 'maquillage', [
        ('rouge-g', 'Rouge G Satin', 'levres', 6200, 'lipstick', '#b45309', 'Boîtier personnalisable.'),
        ('parure-gold-foundation', 'Parure Gold Foundation', 'teint', 9800, 'foundation', '#fcd34d', '24 carats pigments.'),
        ('meteorites-powder', 'Météorites Poudre', 'teint', 6900, 'blush', '#fbbf24', 'Poudre violette légendaire.'),
        ('l-essentiel-foundation', "L'Essentiel Natural 16H", 'teint', 7200, 'foundation', '#d6b89a', 'Naturel 16 h.'),
        ('mousse-de-beaute', 'Mousse de Beauté', 'teint', 5800, 'balm', '#fef3c7', 'Nettoyant moussant.'),
        ('kisskiss-shine', 'KissKiss Shine Bloom', 'levres', 4800, 'lipstick', '#f472b6', 'Brillant floral.'),
        ('abeille-royale-serum', 'Abeille Royale Youth Watery Oil', 'teint', 18500, 'balm', '#fde68a', 'Huile jeunesse.'),
        ('terracotta-bronzer', 'Terracotta Light Sun', 'teint', 6200, 'blush', '#d97706', 'Bronzer soleil.'),
        ('cils-d-or-mascara', "Cils d'Enfer Mascara", 'yeux', 4900, 'mascara', '#111827', 'Volume doré.'),
    ]),
    ('Hermès', 'maquillage', [
        ('rouge-hermes-satin', 'Rouge Hermès Satin', 'levres', 7800, 'lipstick', '#7f1d1d', 'Packaging métal.'),
        ('plein-air-foundation', 'Plein Air Teint', 'teint', 8200, 'foundation', '#d4a574', 'Fini naturel aérien.'),
        ('rose-hermes-blush', 'Rose Hermès Silky Blush', 'teint', 7200, 'blush', '#fb7185', 'Blush soie.'),
        ('kelly-eyes-pencil', 'Kelly Crayon Yeux', 'yeux', 5200, 'eyeliner', '#0f172a', 'Crayon yeux luxe.'),
        ('terre-dhermes', "Terre d'Hermès Eau de Toilette", 'coffrets', 11800, 'spray', '#78350f', 'Parfum boisé citrique.'),
        ('un-jardin-sur-le-nil', 'Un Jardin sur le Nil', 'coffrets', 9800, 'spray', '#84cc16', 'Parfum vert lotus.'),
        ('barenia-foundation', 'Barenia Foundation', 'teint', 8500, 'foundation', '#e8c4a8', 'Couvrance modulable.'),
        ('ombre-hermes-palette', 'Ombre Hermès Eyeshadow Palette', 'yeux', 12500, 'palette', '#c4a484', 'Palette cuir.'),
        ('hermes-mascara', 'Hermès Mascara', 'yeux', 6800, 'mascara', '#111827', 'Volume élégant.'),
    ]),
    ('Tom Ford', 'maquillage', [
        ('lip-color-satin', 'Lip Color Satin', 'levres', 5800, 'lipstick', '#881337', 'Pigments riches.'),
        ('shade-and-illuminate', 'Shade and Illuminate', 'teint', 9800, 'highlighter', '#f5d0a8', 'Contouring crème.'),
        ('traceless-soft-matte', 'Traceless Soft Matte Foundation', 'teint', 9200, 'foundation', '#d6b89a', 'Mat seconde peau.'),
        ('black-orchid-edp', 'Black Orchid Eau de Parfum', 'coffrets', 18500, 'spray', '#1e293b', 'Parfum oriental.'),
        ('architect-liner', 'Eye Defining Pen', 'yeux', 5200, 'eyeliner', '#0f172a', 'Feutre précision.'),
        ('lips-and-boys', 'Lips & Boys Lip Color', 'levres', 6200, 'lipstick', '#be123c', 'Collection limitée.'),
        ('runway-luminizer', 'Runway Highlighting Duo', 'teint', 8500, 'highlighter', '#fcd34d', 'Glow backstage.'),
        ('badass-mascara', 'Badass Mascara', 'yeux', 5800, 'mascara', '#111827', 'Volume dramatique.'),
        ('soleil-contouring', 'Soleil Contouring Compact', 'teint', 7800, 'blush', '#d97706', 'Bronzer soleil.'),
    ]),
    ('MAC', 'maquillage', [
        ('ruby-woo-lipstick', 'Ruby Woo Lipstick', 'levres', 2800, 'lipstick', '#dc2626', 'Rouge mat iconique.'),
        ('studio-fix-fluid', 'Studio Fix Fluid SPF15', 'teint', 4200, 'foundation', '#c9a27a', 'Mat 24 h.'),
        ('in-extreme-dimension', 'In Extreme Dimension Mascara', 'yeux', 3200, 'mascara', '#111827', 'Volume 3D.'),
        ('prep-prime-fix', 'Prep + Prime Fix+', 'teint', 3200, 'spray', '#e0f2fe', 'Fixateur culte.'),
        ('lipglass', 'Lipglass', 'levres', 2400, 'gloss', '#f472b6', 'Brillance miroir.'),
        ('shape-shade-brow', 'Shape + Shade Brow Tint', 'yeux', 2900, 'brow', '#78350f', 'Sourcils gel.'),
        ('mineralize-skinfinish', 'Mineralize Skinfinish', 'teint', 4800, 'highlighter', '#fbbf24', 'Glow naturel.'),
        ('dazzleshadow-liquid', 'Dazzleshadow Liquid', 'yeux', 2800, 'palette', '#6366f1', 'Fard liquide pailleté.'),
        ('retro-matte-liquid', 'Retro Matte Liquid Lipcolour', 'levres', 2900, 'lipstick', '#881337', 'Mat liquide.'),
    ]),
    ('Clinique', 'maquillage', [
        ('dramatically-different', 'Dramatically Different Moisturizing Gel', 'teint', 4200, 'balm', '#bae6fd', 'Hydratant iconique.'),
        ('even-better-foundation', 'Even Better Makeup SPF15', 'teint', 4800, 'foundation', '#ddb892', 'Anti-taches.'),
        ('high-impact-mascara', 'High Impact Mascara', 'yeux', 3200, 'mascara', '#111827', 'Volume instantané.'),
        ('almost-lipstick', 'Almost Lipstick in Black Honey', 'levres', 2400, 'lipstick', '#7f1d1d', 'Teinte universelle culte.'),
        ('cheek-pop-blush', 'Cheek Pop Blush', 'teint', 3800, 'blush', '#fb7185', 'Blush modulable.'),
        ('beyond-perfecting', 'Beyond Perfecting Foundation + Concealer', 'teint', 5200, 'foundation', '#d6b89a', '2-en-1 couvrance.'),
        ('take-day-off-balm', 'Take The Day Off Cleansing Balm', 'teint', 4200, 'balm', '#fef3c7', 'Démaquillant baume.'),
        ('moisture-surge-100h', 'Moisture Surge 100H', 'teint', 5200, 'balm', '#bae6fd', 'Hydratation longue durée.'),
        ('quickliner-eyes', 'Quickliner For Eyes', 'yeux', 2900, 'eyeliner', '#0f172a', 'Crayon automatique.'),
    ]),
    ('Estée Lauder', 'maquillage', [
        ('double-wear-foundation', 'Double Wear Stay-in-Place', 'teint', 5800, 'foundation', '#c9a27a', 'Tenue 24 h iconique.'),
        ('advanced-night-repair', 'Advanced Night Repair Synchronized', 'teint', 12500, 'balm', '#fef3c7', 'Sérum nuit culte.'),
        ('pure-color-envy', 'Pure Color Envy Sculpting Lipstick', 'levres', 4800, 'lipstick', '#be123c', 'Sculptant hydratant.'),
        ('sumptuous-mascara', 'Sumptuous Rebel Mascara', 'yeux', 4200, 'mascara', '#111827', 'Volume rebel.'),
        ('futurist-aqua-brilliance', 'Futurist Aqua Brilliance', 'teint', 6200, 'foundation', '#e8c4a8', 'Glow hydratant.'),
        ('bronze-goddess-powder', 'Bronze Goddess Powder', 'teint', 5200, 'blush', '#d97706', 'Bronzer été.'),
        ('double-wear-concealer', 'Double Wear Radiant Concealer', 'teint', 4200, 'concealer', '#fde68a', 'Anti-cernes lumineux.'),
        ('modern-muse-edp', 'Modern Muse Eau de Parfum', 'coffrets', 9800, 'spray', '#f472b6', 'Parfum musc floral.'),
        ('soft-clean-infusion', 'Soft Clean Infusion Hydrating', 'teint', 4800, 'balm', '#bae6fd', 'Hydratation légère.'),
    ]),
    ('Shiseido', 'maquillage', [
        ('ultimune-serum', 'Ultimune Power Infusing Concentrate', 'teint', 12500, 'balm', '#fef3c7', 'Sérum défense peau.'),
        ('synchro-skin-foundation', 'Synchro Skin Self-Refreshing', 'teint', 6200, 'foundation', '#d6b89a', 'Auto-refresh 24 h.'),
        ('controlled-chaos-mascara', 'ControlledChaos MascaraInk', 'yeux', 4200, 'mascara', '#111827', 'Volume contrôlé.'),
        ('modernmatte-powder', 'ModernMatte Powder Lipstick', 'levres', 4200, 'lipstick', '#881337', 'Poudre mat japonais.'),
        ('white-lucent-brightening', 'White Lucent Brightening Gel', 'teint', 6800, 'balm', '#fde68a', 'Éclat anti-taches.'),
        ('bio-performance-cream', 'Bio-Performance Advanced Super Revitalizing Cream', 'teint', 18500, 'balm', '#fef3c7', 'Crème anti-âge.'),
        ('essential-energy-moisture', 'Essential Energy Hydrating Cream', 'teint', 5800, 'balm', '#bae6fd', 'Hydratation énergie.'),
        ('colorgel-eyeliner', 'MicroLiner Ink', 'yeux', 3200, 'eyeliner', '#0f172a', 'Trait micro-précis.'),
        ('rouge-rouge-lipstick', 'Rouge Rouge Lipstick', 'levres', 4800, 'lipstick', '#dc2626', 'Couleur satinée.'),
    ]),
    ('Charlotte Tilbury', 'maquillage', [
        ('matte-revolution', 'Matte Revolution', 'levres', 4500, 'lipstick', '#9d174d', 'Mat confortable.'),
        ('flawless-filter', 'Hollywood Flawless Filter', 'teint', 5900, 'highlighter', '#fde68a', 'Glow customizable.'),
        ('pillow-talk-lip', 'Matte Revolution Pillow Talk', 'levres', 4500, 'lipstick', '#be123c', 'Nude iconique.'),
        ('airbrush-flawless-foundation', 'Airbrush Flawless Foundation', 'teint', 5200, 'foundation', '#ddb892', 'Fini airbrush.'),
        ('legendary-lashes', 'Legendary Lashes Mascara', 'yeux', 4200, 'mascara', '#111827', 'Volume Hollywood.'),
        ('filmstar-bronze-glow', 'Filmstar Bronze & Glow', 'teint', 6800, 'highlighter', '#d97706', 'Contour glow duo.'),
        ('magic-cream', "Charlotte's Magic Cream", 'teint', 12500, 'balm', '#fef3c7', 'Crème pré-maquillage.'),
        ('beauty-light-wand', 'Beauty Light Wand', 'teint', 4800, 'highlighter', '#fbbf24', 'Enlumineur liquide.'),
        ('rock-n-kohl', "Rock 'N' Kohl Eyeliner", 'yeux', 3200, 'eyeliner', '#0f172a', 'Kohl longue tenue.'),
    ]),
    ('La Mer', 'maquillage', [
        ('creme-de-la-mer', 'Crème de la Mer', 'teint', 38500, 'balm', '#fef3c7', 'Soin Miracle Broth.'),
        ('the-concentrate', 'The Concentrate', 'teint', 48500, 'balm', '#fde68a', 'Sérum réparateur.'),
        ('the-eye-concentrate', 'The Eye Concentrate', 'teint', 28500, 'concealer', '#fde68a', 'Contour des yeux.'),
        ('the-lifting-firming-serum', 'The Lifting Firming Serum', 'teint', 42500, 'balm', '#fef3c7', 'Lift et fermeté.'),
        ('the-tonic', 'The Tonic', 'teint', 12500, 'spray', '#e0f2fe', 'Lotion pré-soin.'),
        ('the-reparative-body', 'The Reparative Body Lotion', 'teint', 18500, 'balm', '#bae6fd', 'Lait corps.'),
        ('the-lip-balm', 'The Lip Balm', 'levres', 8500, 'balm', '#fca5a5', 'Baume lèvres luxe.'),
        ('the-soft-fluid-foundation', 'The Soft Fluid Long Wear Foundation', 'teint', 14500, 'foundation', '#e8c4a8', 'Fond de teint SPF 20.'),
    ]),
    ("Kiehl's", 'maquillage', [
        ('ultra-facial-cream', 'Ultra Facial Cream', 'teint', 5200, 'balm', '#bae6fd', 'Hydratation 24 h.'),
        ('midnight-bamboo', 'Midnight Recovery Concentrate', 'teint', 6800, 'balm', '#fef3c7', 'Huile nuit réparatrice.'),
        ('creamy-eye-treatment', 'Creamy Eye Treatment with Avocado', 'yeux', 5800, 'concealer', '#fde68a', 'Contour avocat.'),
        ('calendula-herbal-toner', 'Calendula Herbal-Extract Toner', 'teint', 4200, 'spray', '#fef9c3', 'Tonique apaisant.'),
        ('clearly-corrective', 'Clearly Corrective Dark Spot Solution', 'teint', 7200, 'concealer', '#fde68a', 'Sérum anti-taches.'),
        ('lip-balm-1', 'Lip Balm #1', 'levres', 890, 'balm', '#fca5a5', 'Baume lèvres culte.'),
        ('rare-earth-mask', 'Rare Earth Deep Pore Cleansing Mask', 'teint', 4800, 'balm', '#334155', 'Masque argile.'),
        ('super-multi-corrective', 'Super Multi-Corrective Cream', 'teint', 12500, 'balm', '#fef3c7', 'Anti-âge global.'),
    ]),

]


def slugify(text: str) -> str:
    text = unicodedata.normalize("NFKD", text)
    text = text.encode("ascii", "ignore").decode("ascii")
    text = re.sub(r"[^a-zA-Z0-9]+", "-", text).strip("-").lower()
    return text or "product"


def brand_slug(brand: str) -> str:
    return slugify(brand.replace("'", ""))


def mk_description(name: str, brand: str, short: str, category: str) -> str:
    horlogerie = category == "montres"
    p1 = (
        f"{name} fait partie de la sélection Azurine chez {brand}. {short} "
        "Chaque référence est choisie pour sa fiabilité en boutique suisse "
        "et sa popularité auprès de nos clientes et clients."
    )
    p2 = (
        "Prix affiché en francs suisses, TVA 8.1 % incluse. Azurine contrôle l'authenticité à réception "
        "et expédie depuis la Suisse avec assurance. Les marques citées restent la propriété de leurs détenteurs ; "
        f"Azurine est une boutique indépendante spécialisée en {'horlogerie' if horlogerie else 'beauté'}."
    )
    return f"{p1}\n\n{p2}"


def mk_product(
    pid: int,
    brand: str,
    slug: str,
    name: str,
    category: str,
    sub: str,
    price: int,
    kind: str,
    color: str,
    short: str,
    *,
    featured: bool = False,
    badge: str | None = None,
) -> dict:
    bg = "blue" if category == "montres" else "pink"
    label = "Bracelet" if category == "montres" else ("Teinte" if sub in ("teint", "levres") else "Finition")
    options: list[dict] = [{"id": "standard", "name": "Standard", "color": color}]
    if category == "maquillage" and sub in ("teint", "levres"):
        options.append(
            {
                "id": "alt",
                "name": "Teinte 02",
                "color": "#dddddd" if color != "#dddddd" else "#e8c4a8",
            }
        )
    details = [
        f"Marque : {brand}",
        f"Référence : {name}",
        "Prix Azurine en CHF, TVA 8.1 % incluse",
        "Authenticité contrôlée à réception",
        "Livraison assurée en Suisse",
    ]
    if category == "montres":
        details.insert(2, "Garantie internationale constructeur")
    stock = 3 if category == "montres" else (8 if price > 500_000 else 18)
    rating = round(4.5 + (pid % 7) * 0.05, 1)
    if rating > 4.9:
        rating = 4.9
    tags = [brand_slug(brand), category]
    if sub:
        tags.append(sub)
    return {
        "id": pid,
        "slug": slug,
        "brand": brand,
        "name": name,
        "category": category,
        "subcategory": sub,
        "price": max(890, int(price * PRICE_FACTOR)),
        "old_price": None,
        "badge": badge,
        "featured": featured,
        "short": short,
        "description": mk_description(name, brand, short, category),
        "details": details,
        "variants": {"label": label, "options": options},
        "art": {"kind": kind, "color": color, "bg": bg},
        "stock": stock,
        "rating": rating,
        "reviews": 8 + (pid % 120),
        "tags": tags,
    }


def build_products() -> list[dict]:
    products: list[dict] = []
    used_slugs: set[str] = set()
    pid = 1
    for brand, category, items in CATALOG:
        bs = brand_slug(brand)
        for i, (slug_part, name, sub, price, kind, color, short) in enumerate(items):
            base = slug_part if slug_part.startswith(bs) else f"{bs}-{slug_part}"
            slug = base
            n = 2
            while slug in used_slugs:
                slug = f"{base}-{n}"
                n += 1
            used_slugs.add(slug)
            badge = None
            if i == 0 and pid % 11 == 0:
                badge = "nouveau"
            elif i == 1 and pid % 7 == 0:
                badge = "best-seller"
            featured = i < 2 and (pid <= 30 or brand in ("Rolex", "Chanel", "Dior", "Omega"))
            products.append(
                mk_product(
                    pid,
                    brand,
                    slug,
                    name,
                    category,
                    sub,
                    price,
                    kind,
                    color,
                    short,
                    featured=featured,
                    badge=badge,
                )
            )
            pid += 1
    return products


def main() -> int:
    products = build_products()
    if not (350 <= len(products) <= 450):
        print(f"ERROR: expected 350–450 products, got {len(products)}", file=sys.stderr)
        return 1
    slugs = [p["slug"] for p in products]
    if len(set(slugs)) != len(slugs):
        print("ERROR: duplicate slugs", file=sys.stderr)
        return 1
    if [p["id"] for p in products] != list(range(1, len(products) + 1)):
        print("ERROR: ids must be sequential from 1", file=sys.stderr)
        return 1
    if any("photo" in p for p in products):
        print("ERROR: photo fields must not be present", file=sys.stderr)
        return 1
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(products, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")
    watches = sum(1 for p in products if p["category"] == "montres")
    makeup = sum(1 for p in products if p["category"] == "maquillage")
    print(f"Wrote {len(products)} products to {OUT} ({watches} montres, {makeup} maquillage)")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
