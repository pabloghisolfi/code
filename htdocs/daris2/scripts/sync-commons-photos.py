#!/usr/bin/env python3
"""Download Wikimedia Commons photos (free licenses only) into assets/img/products/."""
from __future__ import annotations

import json
import re
import urllib.parse
import urllib.request
from html import unescape
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT / "assets" / "img" / "products"
DATA_FILE = ROOT / "data" / "product-photos.json"
UA = "AzurineBot/1.0 (educational; contact: bonjour@azurine.ch)"

# Product slug => exact Commons file title (must be freely licensed).
MANIFEST: dict[str, str] = {
    # Rolex (priority)
    "submariner-date-41": "File:Rolex Submariner Professional.JPG",
    "datejust-36": "File:Rolex Datejust 16200.jpg",
    "day-date-40": "File:Rolex Day Date.jpg",
    "gmt-master-ii": "File:Rolex GMT Master II Pepsi.jpg",
    # Other watch houses
    "nautilus-5711": "File:Patek-Philippe-Nautilus-5711-1A-010-1.jpg",
    "calatrava-5227": "File:Patek Philippe Calatrava ref. 5055.jpg",
    "aquanaut-5167": "File:Patek Philippe Aquanaut Advanced Research ref. 5650G limitato a 500 pezzi.jpg",
    "royal-oak-15500": "File:Audemars Piguet Royal Oak ref. 15202.jpg",
    "royal-oak-offshore": "File:Audemars Piguet Royal Oak Offshore Diver.jpg",
    "big-bang-unico": "File:Hublot chrono.jpg",
    "speedmaster-moonwatch": 'File:Vintage Omega Speedmaster "Pre-moon".jpg',
    "seamaster-diver-300": "File:Omega Seamaster 300 militare.jpg",
    "santos-de-cartier": "File:Cartier Santos wristwatch.jpg",
    "tank-louis": "File:Cartier Tank.jpg",
    "black-bay-58": "File:Tudor Black Bay 58 ref. M79018V, primo modello di casa Tudor interamente in oro.jpg",
    "pelagos-39": "File:Tudor Pelagos for Carolina Watch Club Limited Edition.jpg",
    "carrera-chronograph": "File:TAG Heuer Carrera blue.jpg",
    "monaco": "File:TAG Heuer Monaco Gulf edition (cropped to watch).JPG",
    "pilot-mark-xx": "File:IWC Mark XV.jpg",
}


def strip_html(value: str) -> str:
    value = unescape(re.sub(r"<[^>]+>", "", value))
    return re.sub(r"\s+", " ", value).strip()


def commons_page_url(title: str) -> str:
    name = title.removeprefix("File:")
    return "https://commons.wikimedia.org/wiki/File:" + urllib.parse.quote(name.replace(" ", "_"))


def fetch_meta(title: str) -> dict:
    url = "https://commons.wikimedia.org/w/api.php?" + urllib.parse.urlencode(
        {
            "action": "query",
            "titles": title,
            "prop": "imageinfo",
            "iiprop": "url|extmetadata|mime",
            "format": "json",
        }
    )
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=60) as resp:
        data = json.load(resp)
    pages = data["query"]["pages"]
    page = next(iter(pages.values()))
    if "missing" in page:
        raise RuntimeError(f"Missing on Commons: {title}")
    ii = page["imageinfo"][0]
    meta = ii["extmetadata"]
    lic = meta.get("LicenseShortName", {}).get("value", "")
    allowed = ("CC BY", "CC BY-SA", "CC0", "Public domain")
    if not any(lic.startswith(a) or lic == a for a in allowed):
        raise RuntimeError(f"License not allowed for {title}: {lic}")
    author_raw = meta.get("Artist", {}).get("value", "") or meta.get("Credit", {}).get("value", "")
    return {
        "download_url": ii["url"],
        "mime": ii.get("mime", "image/jpeg"),
        "license": lic,
        "author": strip_html(author_raw) or "Unknown author",
        "source": commons_page_url(title),
        "commons_title": title,
    }


def ext_for_mime(mime: str) -> str:
    return {
        "image/jpeg": ".jpg",
        "image/png": ".png",
        "image/webp": ".webp",
    }.get(mime, ".jpg")


def main() -> None:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    catalog: dict[str, dict] = {}

    for slug, title in MANIFEST.items():
        meta = fetch_meta(title)
        ext = ext_for_mime(meta["mime"])
        rel = f"img/products/{slug}{ext}"
        dest = ROOT / "assets" / rel
        req = urllib.request.Request(meta["download_url"], headers={"User-Agent": UA})
        with urllib.request.urlopen(req, timeout=120) as resp:
            dest.write_bytes(resp.read())
        sidecar = OUT_DIR / f"{slug}.license.json"
        sidecar.write_text(
            json.dumps(
                {
                    "slug": slug,
                    "author": meta["author"],
                    "source": meta["source"],
                    "license": meta["license"],
                    "commons_title": meta["commons_title"],
                    "file": rel,
                },
                ensure_ascii=False,
                indent=2,
            )
            + "\n",
            encoding="utf-8",
        )
        catalog[slug] = {
            "file": rel,
            "author": meta["author"],
            "source": meta["source"],
            "license": meta["license"],
        }
        print("OK", slug, meta["license"])

    DATA_FILE.write_text(json.dumps(catalog, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Wrote {len(catalog)} entries to {DATA_FILE}")


if __name__ == "__main__":
    main()
