(function () {
  'use strict';

  const body = document.body;
  const base = body.dataset.base || '/';
  const csrf = body.dataset.csrf || '';

  const qs = (sel, root) => (root || document).querySelector(sel);
  const qsa = (sel, root) => Array.from((root || document).querySelectorAll(sel));

  /* Ribbon rotation */
  const ribbon = qs('[data-ribbon]');
  if (ribbon) {
    const items = qsa('.promo-bar__item, .ribbon__item', ribbon);
    if (items.length > 1) {
      let i = 0;
      setInterval(() => {
        items[i].classList.remove('is-visible');
        i = (i + 1) % items.length;
        items[i].classList.add('is-visible');
      }, 5000);
    }
  }

  /* Mobile drawer */
  const drawer = qs('[data-drawer]');
  const openBtn = qs('[data-drawer-open]');
  const closeEls = qsa('[data-drawer-close]');

  function setDrawer(open) {
    if (!drawer) return;
    drawer.hidden = !open;
    drawer.classList.toggle('is-open', open);
    if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.documentElement.classList.toggle('drawer-open', open);
    if (open) {
      const close = qs('.drawer__close', drawer);
      if (close) close.focus();
    }
  }

  openBtn?.addEventListener('click', () => setDrawer(true));
  closeEls.forEach((el) => el.addEventListener('click', () => setDrawer(false)));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && drawer && !drawer.hidden) setDrawer(false);
  });

  /* Flash dismiss */
  const flash = qs('[data-flash]');
  qs('[data-flash-close]', flash || document)?.addEventListener('click', () => flash?.remove());

  /* Scroll reveal */
  const revealEls = qsa('[data-reveal]');
  if (revealEls.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      },
      { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
    );
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  }

  /* Shipbar fill */
  const shipFill = qs('[data-shipbar-fill]');
  if (shipFill) {
    const p = parseInt(shipFill.dataset.progress || '0', 10);
    requestAnimationFrame(() => {
      shipFill.style.width = Math.min(100, Math.max(0, p)) + '%';
    });
  }

  /* Quantity controls */
  qsa('[data-qty]').forEach((wrap) => {
    const input = qs('[data-qty-input]', wrap) || qs('input[type="number"]', wrap);
    const min = parseInt(input?.min || '0', 10);
    const max = parseInt(input?.max || '99', 10);
    qs('[data-qty-minus]', wrap)?.addEventListener('click', () => {
      if (!input) return;
      const v = parseInt(input.value, 10) || min;
      input.value = String(Math.max(min, v - 1));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
    qs('[data-qty-plus]', wrap)?.addEventListener('click', () => {
      if (!input) return;
      const v = parseInt(input.value, 10) || min;
      input.value = String(Math.min(max, v + 1));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  /* Product tabs */
  qsa('[data-tabs]').forEach((tabs) => {
    const tabBtns = qsa('[role="tab"]', tabs);
    const panels = qsa('[role="tabpanel"]', tabs);
    tabBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('aria-controls');
        tabBtns.forEach((t) => {
          const on = t === btn;
          t.classList.toggle('is-active', on);
          t.setAttribute('aria-selected', on ? 'true' : 'false');
          t.tabIndex = on ? 0 : -1;
        });
        panels.forEach((p) => {
          const show = p.id === id;
          p.hidden = !show;
        });
      });
    });
  });

  /* Variant label */
  qsa('[data-buy]').forEach((form) => {
    qsa('input[name="variant"]', form).forEach((radio) => {
      radio.addEventListener('change', () => {
        const name = radio.dataset.variantName || '';
        const label = qs('[data-variant-label]', form);
        const stage = qs('[data-variant-name]');
        if (label) label.textContent = name;
        if (stage) stage.textContent = name;
      });
    });
  });

  /* Catalog filters toggle */
  const filtersToggle = qs('[data-filters-toggle]');
  const filters = qs('[data-filters]');
  filtersToggle?.addEventListener('click', () => {
    const open = filters?.classList.toggle('is-open');
    filtersToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  /* Auto-submit sort */
  const sortForm = qs('[data-sort-form]');
  qs('select', sortForm || document)?.addEventListener('change', () => sortForm?.submit());

  /* Favorites (localStorage) */
  const FAV_KEY = 'daris_favs';
  function getFavs() {
    try {
      return JSON.parse(localStorage.getItem(FAV_KEY) || '[]');
    } catch {
      return [];
    }
  }
  function setFavs(list) {
    localStorage.setItem(FAV_KEY, JSON.stringify(list));
  }
  qsa('[data-fav]').forEach((btn) => {
    const slug = btn.dataset.fav;
    const favs = getFavs();
    if (favs.includes(slug)) btn.setAttribute('aria-pressed', 'true');
    btn.addEventListener('click', () => {
      let list = getFavs();
      const on = btn.getAttribute('aria-pressed') === 'true';
      if (on) {
        list = list.filter((s) => s !== slug);
        btn.setAttribute('aria-pressed', 'false');
      } else {
        list.push(slug);
        btn.setAttribute('aria-pressed', 'true');
      }
      setFavs(list);
    });
  });

  /* Watch size calculator */
  const wrist = qs('#wrist');
  const wristOut = qs('#wrist-out');
  const sizeResult = qs('[data-size-result]');
  if (wrist && sizeResult) {
    const update = () => {
      const cm = parseFloat(wrist.value);
      if (wristOut) wristOut.textContent = cm + ' cm';
      let range = '28 à 34 mm';
      if (cm < 15) range = '24 à 28 mm';
      else if (cm >= 17) range = '34 à 38 mm';
      sizeResult.innerHTML = 'Boîtier conseillé : <strong>' + range + '</strong>';
    };
    wrist.addEventListener('input', update);
    update();
  }

  async function postJson(url, data) {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'fetch',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ ...data, _token: csrf }),
    });
    return res.json();
  }

  function updateCartCount(count) {
    const el = qs('[data-cart-count]');
    if (!el) return;
    if (count > 0) {
      el.textContent = String(count);
      el.hidden = false;
    } else {
      el.hidden = true;
    }
  }

  function showToast(message, ok) {
    let node = qs('[data-toast]');
    if (!node) {
      node = document.createElement('div');
      node.dataset.toast = '';
      node.className = 'flash flash--' + (ok ? 'success' : 'error');
      node.setAttribute('role', 'status');
      node.style.cssText = 'position:fixed;bottom:1rem;left:50%;transform:translateX(-50%);z-index:300;max-width:90vw;box-shadow:var(--shadow)';
      document.body.appendChild(node);
    }
    node.textContent = message;
    node.className = 'flash flash--' + (ok ? 'success' : 'error');
    clearTimeout(node._t);
    node._t = setTimeout(() => node.remove(), 4500);
  }

  /* Add to cart (fetch) */
  qsa('[data-add-to-cart]').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      if (!window.fetch) return;
      e.preventDefault();
      const fd = new FormData(form);
      const payload = {
        product_id: fd.get('product_id'),
        quantity: fd.get('quantity') || '1',
        variant: fd.get('variant') || '',
      };
      const action = form.getAttribute('action') || base + 'panier/ajouter';
      try {
        const json = await postJson(action, payload);
        if (json.ok) {
          updateCartCount(json.count);
          showToast(json.message, true);
        } else {
          showToast(json.message || 'Impossible d\'ajouter au panier.', false);
        }
      } catch {
        form.submit();
      }
    });
  });

  /* Cart line update/remove */
  async function cartFormSubmit(form, e) {
    if (!window.fetch) return;
    e.preventDefault();
    const fd = new FormData(form);
    const action = form.getAttribute('action');
    const payload = {};
    fd.forEach((v, k) => {
      if (k !== '_token') payload[k] = v;
    });
    try {
      const json = await postJson(action, payload);
      if (!json.ok) {
        showToast(json.message, false);
        return;
      }
      updateCartCount(json.count);
      if (form.matches('[data-cart-remove]') || parseInt(payload.quantity, 10) === 0) {
        const line = form.closest('[data-cart-line]');
        line?.remove();
        if (!qs('[data-cart-line]')) location.reload();
      }
      const sub = qs('[data-summary-subtotal]');
      const total = qs('[data-summary-total]');
      const ship = qs('[data-summary-shipping]');
      if (sub) sub.textContent = json.subtotal;
      if (total) total.textContent = json.total;
      if (ship) ship.textContent = json.shipping;
      const rem = qs('[data-shipbar-remaining]');
      if (rem && typeof json.remaining === 'number') {
        rem.textContent = 'CHF ' + json.remaining.toFixed(2).replace('.', ',');
      }
    } catch {
      form.submit();
    }
  }

  qsa('[data-cart-update]').forEach((form) => {
    form.addEventListener('change', (e) => cartFormSubmit(form, e));
    form.addEventListener('submit', (e) => cartFormSubmit(form, e));
  });
  qsa('[data-cart-remove]').forEach((form) => {
    form.addEventListener('submit', (e) => cartFormSubmit(form, e));
  });

  /* Newsletter */
  const nl = qs('[data-newsletter]');
  const nlStatus = qs('[data-newsletter-status]');
  nl?.addEventListener('submit', async (e) => {
    if (!window.fetch) return;
    e.preventDefault();
    const fd = new FormData(nl);
    try {
      const res = await fetch(nl.action, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-Requested-With': 'fetch' },
        credentials: 'same-origin',
        body: fd,
      });
      const json = await res.json();
      if (nlStatus) nlStatus.textContent = json.message || '';
      if (json.ok) nl.reset();
    } catch {
      nl.submit();
    }
  });

  /* Style advisor chat (page + panneau global) */
  function appendMsg(chatLog, text, who) {
    if (!chatLog) return;
    const div = document.createElement('div');
    div.className = 'chat__msg chat__msg--' + who;
    div.textContent = text;
    chatLog.appendChild(div);
    chatLog.scrollTop = chatLog.scrollHeight;
  }

  qsa('[data-chat-suggest]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const host = btn.closest('[data-assistant]') || btn.closest('[data-chat]');
      const form = host ? qs('[data-chat-form]', host) : qs('[data-chat-form]');
      const input = form ? qs('input[name="question"]', form) : null;
      if (input && form) {
        input.value = btn.textContent.trim();
        form.requestSubmit();
      }
    });
  });

  qsa('[data-chat-form]').forEach((chatForm) => {
    const host = chatForm.closest('[data-assistant]') || chatForm.closest('[data-chat]');
    const chatLog = host ? qs('[data-chat-log]', host) : qs('[data-chat-log]');
    const chatInput = qs('input[name="question"]', chatForm);

    chatForm.addEventListener('submit', async (e) => {
      if (!window.fetch) return;
      e.preventDefault();
      const question = (chatInput?.value || '').trim();
      if (!question) return;
      appendMsg(chatLog, question, 'user');
      if (chatInput) chatInput.value = '';
      try {
        const json = await postJson(chatForm.action, { question });
        if (json.ok) {
          appendMsg(chatLog, json.answer, 'bot');
          if (json.links?.length) {
            const links = json.links.map((l) => '<a href="' + l.url + '">' + l.label + '</a>').join(' · ');
            const div = document.createElement('div');
            div.className = 'chat__msg chat__msg--bot';
            div.innerHTML = links;
            chatLog?.appendChild(div);
            if (chatLog) chatLog.scrollTop = chatLog.scrollHeight;
          }
        } else {
          appendMsg(chatLog, json.error || 'Une erreur est survenue.', 'bot');
        }
      } catch {
        appendMsg(chatLog, 'Connexion impossible. Réessayez dans un instant.', 'bot');
      }
    });
  });

  /* Panneau conseillère (toutes les pages) */
  const assistantPanel = qs('[data-assistant-panel]');
  qsa('[data-assistant-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (!assistantPanel) return;
      const open = assistantPanel.hidden;
      assistantPanel.hidden = !open;
      assistantPanel.classList.toggle('is-open', open);
      qsa('[data-assistant-toggle]').forEach((b) => b.setAttribute('aria-expanded', open ? 'true' : 'false'));
      if (open) {
        const input = qs('[data-assistant-panel] input[name="question"]');
        input?.focus();
      }
    });
  });
})();
