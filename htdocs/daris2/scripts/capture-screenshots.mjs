#!/usr/bin/env node
/**
 * Captures publish screenshots (needs Chrome + local PHP server on 8765).
 */
import puppeteer from 'puppeteer-core';
import { mkdir } from 'node:fs/promises';

const OUT = process.env.SCREENSHOT_DIR || '/cursor/stores/bc-b166a0c2-cf01-4367-bd8d-f06e41546fa6/media/daris2';
const BASE = process.env.BASE_URL || 'http://127.0.0.1:8765/daris2';

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: process.env.CHROME_PATH || '/usr/local/bin/google-chrome',
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
});
const page = await browser.newPage();
await page.setViewport({ width: 1400, height: 900 });

await page.goto(`${BASE}/boutique/montres?prix_min=100&prix_max=300&marque%5B%5D=Rolex&tri=prix-asc`, { waitUntil: 'networkidle2' });
await page.screenshot({ path: `${OUT}/catalog-filters.png`, fullPage: true });

await page.goto(`${BASE}/produit/rolex-submariner-date-41`, { waitUntil: 'networkidle2' });
await page.click('.product__view-photo');
await page.waitForSelector('[data-photo-lightbox-root]:not([hidden])', { timeout: 8000 });
await new Promise((r) => setTimeout(r, 400));
await page.screenshot({ path: `${OUT}/product-lightbox-rolex.png` });

await page.goto(`${BASE}/produit/rolex-submariner-date-41`, { waitUntil: 'networkidle2' });
await page.click('form[data-add-to-cart] button[type="submit"]');
await new Promise((r) => setTimeout(r, 800));
await page.goto(`${BASE}/panier`, { waitUntil: 'networkidle2' });
await page.screenshot({ path: `${OUT}/cart.png`, fullPage: true });

await page.goto(`${BASE}/boutique`, { waitUntil: 'networkidle2' });
await page.$eval('#locale-select', (el, value) => {
  el.value = value;
  el.form?.requestSubmit();
}, 'de');
await page.waitForNavigation({ waitUntil: 'networkidle2' });
await page.screenshot({ path: `${OUT}/language-switcher.png`, fullPage: false });

await browser.close();
console.log('Screenshots saved to', OUT);
