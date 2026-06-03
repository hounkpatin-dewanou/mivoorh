# MivooRH — Portail RH MivooPay

Application fullstack pour la gestion des **avances sur salaire** : API **Laravel 12** (Sanctum) + interface **Vue 3** (Vite, Pinia, Tailwind).

Trois rôles : **super-admin** (réseau), **RH** (une entreprise), **employé** (demandes et solde).

---

## Démo en ligne (production)

Application déployée et prête à tester :

| Lien | Description |
|------|-------------|
| **https://mivoorh.kladriva.com** | Application (landing, connexion, espaces RH / employé / admin) |
| **https://mivoorh.kladriva.com/api/health** | Santé API (`{"status":"ok"}`) |
| **https://mivoorh.kladriva.com/login** | Connexion directe |

Comptes de démo : voir la section [Comptes de démonstration](#comptes-de-démonstration) (mot de passe **`password`**).

---

## Démarrage rapide (installation locale avec Docker)

### Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et démarré
- Port libre : **8002** (application), MySQL interne au réseau Docker

### Une seule commande

À la racine du dossier `mivoorh/` :

```bash
docker compose up --build
```

Premier lancement : téléchargement des images, build du frontend, migrations MySQL et **seed** des comptes de test (2–3 minutes selon la connexion).

| URL | Rôle |
|-----|------|
| http://localhost:8002 | Application (landing + SPA) |
| http://localhost:8002/api/health | Santé API (`{"status":"ok"}`) |

Le conteneur **web** (nginx) sert le frontend et proxifie `/api` vers Laravel : **pas besoin de lancer PHP et npm séparément**.

### Réinitialiser la base de données

```bash
docker compose down -v
docker compose up --build
```

Le volume `-v` supprime MySQL ; au redémarrage, migrations + seed sont rejoués.

### Tests API dans Docker

```bash
docker compose exec api php artisan test
```

---

## Comptes de démonstration

Mot de passe pour tous les comptes ci-dessous : **`password`**

| Rôle | Email | Usage |
|------|-------|--------|
| Super Admin | `admin@mivoopay.bj` | Entreprises, employés, demandes (tout le réseau) |
| RH entreprise 1 | `rh1@mivoorh.test` | Société Agro Bénin — employés, validation demandes, export CSV |
| RH entreprise 2 | `rh2@mivoorh.test` | TechHub Cotonou |
| Employé | `emp1_1@mivoorh.test` … `emp2_5@mivoorh.test` | Solde, nouvelle demande, historique |

Données créées par `backend/database/seeders/DatabaseSeeder.php` : 2 entreprises, 5 employés chacune, demandes en attente / approuvées / refusées, notifications exemple.

---

## Parcours de test manuel (15 min)

Utiliser **https://mivoorh.kladriva.com** (production) ou http://localhost:8002 (Docker local).

1. Ouvrir la landing — parcourir la page d’accueil.
2. **Connexion** → `rh1@mivoorh.test` / `password` → tableau de bord RH, liste employés, demandes en attente, approuver ou refuser avec commentaire.
3. **Déconnexion** → `emp1_1@mivoorh.test` / `password` → vérifier le solde, créer une demande d’avance.
4. **Déconnexion** → `admin@mivoopay.bj` / `password` → activer/désactiver une entreprise, consulter les stats.
5. **Inscription** `/register` (ex. https://mivoorh.kladriva.com/register) :
   - **RH** : crée une nouvelle entreprise + compte ;
   - **Employé** : choisit une entreprise existante + salaire / plafond.

---

## Développement local (sans Docker)

### Prérequis

- PHP 8.2+, Composer
- MySQL (ex. XAMPP) **ou** SQLite pour des tests rapides
- Node.js 20+, npm

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configurer `.env` (MySQL) :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mivoorh
DB_USERNAME=root
DB_PASSWORD=
FRONTEND_URL=http://localhost:5173
```

Créer la base `mivoorh` dans phpMyAdmin, puis :

```bash
php artisan migrate --seed
php artisan serve
```

API : **http://127.0.0.1:8000** (routes sous `/api`).

### Frontend

```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

SPA : **http://localhost:5173** — `VITE_API_BASE_URL=http://localhost:8000/api` dans `.env.local`.

Lancer **deux terminaux** : `php artisan serve` et `npm run dev`.

---

## Tests automatisés

### Backend (PHPUnit, SQLite en mémoire)

```bash
cd backend
composer install
php artisan test
```

### Frontend (Vitest)

```bash
cd frontend
npm install
npm run test
```

---

## Structure du dépôt

```
mivoorh/
├── backend/                 # API Laravel (app/, routes/api.php, database/)
├── frontend/                # SPA Vue 3 (src/views, src/api, src/stores)
├── docker/                  # Dockerfiles, nginx, entrypoint API
├── docker-compose.yml       # Stack MySQL + API + nginx (port 8002)
└── README.md
```

### Fichiers utiles

| Fichier | Description |
|---------|-------------|
| `backend/routes/api.php` | Toutes les routes REST commentées |
| `backend/database/seeders/DatabaseSeeder.php` | Données de démo |
| `frontend/src/router/index.js` | Routes et gardes par rôle |
| `frontend/src/api/axios.js` | Client HTTP + token Bearer |
| `backend/.env.docker` | **Config Docker** (API + MySQL) — copiée en `.env` au démarrage |
| `backend/.env.example` | Variables pour dev local (XAMPP) |
| `frontend/.env.example` | URL de l’API pour Vite |

---

## Fonctionnalités livrées (MVP)

- Authentification Sanctum (login, register RH/employé, logout)
- Dashboards **RH**, **employé**, **super-admin**
- CRUD employés (RH + admin), demandes d’avance, notifications
- Calcul du **solde disponible** (jours travaillés × plafond − avances du mois)
- Export **CSV** paie (RH)
- Profil éditable par rôle
- Landing + inscription par étapes

Hors MVP : reset mot de passe par e-mail, E2E Playwright, paiement MivooPay temps réel.

---

## Dépannage Docker

| Problème | Piste |
|----------|--------|
| Port 8002 occupé | Changer `8002:80` dans `docker-compose.yml` |
| Page blanche / API injoignable | `docker compose logs api` puis `docker compose logs web` |
| Données incohérentes | `docker compose down -v` puis `up --build` |
| Build frontend échoue | Vérifier `frontend/package-lock.json` présent |
| Erreur 500 au login | Vérifier `backend/.env.docker` (`DB_HOST=db`) puis `docker compose build api && docker compose up -d` |
| Production (domaine) | Éditer `backend/.env.docker` : `APP_URL`, `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS` → `https://votre-domaine.com`, puis rebuild |

---

## Licence / contexte

Projet académique / démonstration **MivooPay** — Bénin. Documentation technique : commentaires dans le code et ce README.
