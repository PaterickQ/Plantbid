# PlantBid – Funkční a technická specifikace

## 1. Úvod

PlantBid je jednoduchá webová aukční aplikace zaměřená na prodej a nákup rostlin mezi uživateli.  
Cílem aplikace je umožnit:

- vytvářet aukce,
- procházet aktivní aukce,
- přihazovat,
- sledovat historii příhozů.

Tento dokument obsahuje funkční a technickou specifikaci pro účely předmětu *Tvorba webových aplikací*. Projekt vychází z původní databázové aplikace a je rozšířen tak, aby splnil požadavky zadání.

---

## 2. Funkční specifikace

### 2.1 Datový konceptuální model

**Entity:**

- **Uživatel** – přihlašovací údaje, role, základní informace.
- **Aukce** – prodávaná rostlina, ceny, čas ukončení, obrázek.
- **Příhoz** – nabídka uživatele v aukci.
- **Kategorie (volitelné)** – tematické zařazení aukcí.

**Vztahy:**

- Uživatel 1:N Aukce  
- Uživatel 1:N Příhoz  
- Aukce 1:N Příhoz  
- Kategorie 1:N Aukce (volitelné)

### 2.2 Charakteristika funkčností

#### Hlavní funkce:

1. **Registrace a přihlášení**
2. **Správa profilu**
3. **Procházení a filtrování aukcí**
4. **Zobrazení detailu aukce**
5. **Přihazování**
6. **Zakládání a správa vlastních aukcí**
7. **Administrace** (blokace uživatelů, mazání aukcí)

### 2.3 Uživatelské role a oprávnění

| Funkce                                  | Neregistrovaný | Registrovaný | Administrátor |
|----------------------------------------|----------------|--------------|---------------|
| Prohlížet aukce                        | ✔              | ✔            | ✔             |
| Zobrazit detail                        | ✔              | ✔            | ✔             |
| Registrovat se                         | ✔              | ✖            | ✖             |
| Přihlásit / odhlásit                   | ✔              | ✔            | ✔             |
| Přihazovat                             | ✖              | ✔            | ✔             |
| Vytvářet a spravovat vlastní aukce     | ✖              | ✔            | ✔             |
| Mazání cizích aukcí                    | ✖              | ✖            | ✔             |
| Správa uživatelů                       | ✖              | ✖            | ✔             |

### 2.4 Uživatelské grafické rozhraní

**Hlavní obrazovky:**

- Domovská stránka – seznam aktivních aukcí, filtry, vyhledávání.
- Detail aukce – informace, příhozy, graf historie (Canvas).
- Přihlášení / registrace.
- Profil uživatele – vlastní aukce, příhozy, oblíbené aukce (localStorage).
- Administrace – seznam uživatelů a aukcí.

---

## 3. Technická specifikace

### 3.1 Datový logický model

#### `users`
- id (PK)  
- username  
- email  
- password  
- role (user/admin)  
- created_at  

#### `auctions`
- id (PK)  
- user_id (FK)  
- title  
- description  
- image  
- starting_price  
- current_price  
- category_id (FK, volitelné)  
- status (active/ended/cancelled)  
- end_time  
- created_at  

#### `bids`
- id (PK)  
- auction_id (FK)  
- user_id (FK)  
- bid_amount  
- bid_time  

#### `categories` (volitelné)
- id (PK)  
- name  
- slug  

### 3.2 Architektura

Aplikace používá klasické rozdělení **klient – server**.

**Klient:**
- HTML5 (šablony, formuláře),
- CSS3 (vzhled, responzivita),
- JavaScript (ES6):
  - fetch API pro načítání JSON,
  - odpočet času,
  - filtrace aukcí,
  - localStorage pro oblíbené aukce,
  - HTML5 Canvas pro graf historie příhozů.

**Server:**
- PHP 8+, OOP architektura,
- Repozitáře pro práci s databází,
- REST-like endpointy `/api/*`,
- PDO pro komunikaci s MySQL.

**Databáze:** MySQL.

### 3.3 Popis tříd

#### `Database`
- Správa PDO připojení.

#### `User`
- Atributy: id, username, email, passwordHash, role.
- Metody: gettery, isAdmin().

#### `Auction`
- Atributy: id, owner, title, description, prices, times, status.
- Metody: isActive(), getTimeRemaining(), raisePrice().

#### `Bid`
- Atributy: id, auction, user, amount, time.

#### Repozitáře
- UserRepository – práce s uživateli.
- AuctionRepository – získávání a ukládání aukcí.
- BidRepository – příhozy a jejich historie.

#### Servisy
- `AuthService` – login, logout, session.
- `BidService` – validace a provedení příhozů.

### 3.4 Použité technologie

- HTML5
- CSS3
- JavaScript (ES6+)
- Canvas / Chart.js pro grafy
- PHP 8+
- MySQL
- Git pro verzování

---

## 4. Výstup aplikace

Aplikace bude publikována na webovém hostingu a její zdrojový kód bude dostupný v Git repozitáři.
