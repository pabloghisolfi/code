#!/usr/bin/env bash
# Smoke-test des routes principales (serveur PHP intégré + routeur htdocs).
set -euo pipefail
HOST="${HOST:-127.0.0.1:8765}"
BASE="http://${HOST}/daris2"
FILTER_PATH="/boutique/montres?prix_min=100&prix_max=300&marque%5B%5D=Rolex&tri=prix-asc"
FAIL=0

check() {
  local path="$1"
  local expect="${2:-200}"
  local code
  code=$(curl -sS -o /dev/null -w '%{http_code}' "${BASE}${path}")
  if [[ "$code" != "$expect" ]]; then
    echo "FAIL $code (want $expect) $path"
    FAIL=1
  else
    echo "OK   $code $path"
  fi
}

check "/"
check "/boutique"
check "$FILTER_PATH"
check "/produit/rolex-submariner-date-41"
check "/produit/rolex-submariner-date-41/photo"
check "/panier"
check "/commande"
check "/contact"
check "/conseils"
check "/exemples"
check "/a-propos"
check "/confidentialite"
check "/tarifs"
check "/chat"
check "/sitemap.xml"
check "/robots.txt"
check "/page-inexistante-azurine" "404"

exit "$FAIL"
