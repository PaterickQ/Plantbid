# PlantBid

Jednoduchá webová aukční aplikace zaměřená na prodej a nákup rostlin mezi uživateli. Projekt je reorganizován do jednoduchého MVC bez použití frameworku.

## Funkce
- Registrace, přihlášení, odhlášení
- Vytváření aukcí, správa vlastních aukcí, editace a mazání
- Prohlížení aktivních aukcí a archivu
- Přihazování a historie příhozů
- Komentáře u aukcí (administrátor může mazat)
- Administrace uživatelů a aukcí (role, blokace)
- Oblíbené aukce ukládané v `localStorage`

## Datový model (hlavní entity)
- Uživatel
- Aukce
- Příhoz
- Komentář
- Kategorie (volitelné)

## Architektura (MVC)
- **public/**: vstupní bod aplikace a statické soubory
- **app/Controllers/**: řídicí logika
- **app/Repositories/**: přístup k databázi (SQL)
- **app/Views/**: šablony (HTML)
- **config/**: konfigurace a routy

## Spuštění
1. Nastav document root na `plantbid/public`.
2. Zkontroluj přihlašovací údaje k databázi v `plantbid/config/database.php`.
3. Otevři aplikaci v prohlížeči:
   - Bez rewrite: `public/index.php?route=` (query routing)
   - S rewrite: aktivuj `.htaccess` a přepni `use_query_routes` na `false` v `plantbid/config/app.php`.

## Routing
Routy jsou definované v `plantbid/config/routes.php`. Výchozí režim používá query parametr `route`.

## LocalStorage
Oblíbené aukce se ukládají pod klíčem `plantbid.favorites`.

