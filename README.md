# Sheet Happens

Application Laravel de création et de gestion de fiches de personnage D&D 5e (règles PHB 2014) : création guidée, fiche complète, inventaire, sorts, dons, capacités de classe et montée de niveau jusqu'au niveau 5.

## Comment démarrer le projet

### Prérequis

- Docker Desktop (Windows/macOS) ou Docker Engine + Docker Compose (Linux), démarré
- Git

PHP et Composer **ne sont pas requis en local** grâce à Docker — voir la section dédiée si tu ne les as pas.

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd <nom-du-projet>
```

### 2. Installer les dépendances PHP (Composer)

#### Cas normal — PHP (8.3 ou plus) est installé localement

```bash
composer install
```

#### Cas particulier — PHP n'est pas installé (ou version trop ancienne)

Pas besoin d'installer PHP sur la machine : on utilise un conteneur Docker jetable pour exécuter Composer une seule fois.

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

> Sur Windows (PowerShell), retire `-u "$(id -u):$(id -g)"` (pas nécessaire) et adapte le chemin du volume avec `${PWD}` :
> ```powershell
> docker run --rm -v ${PWD}:/var/www/html -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
> ```

L'image `php84-composer` sert uniquement à installer les dépendances ; l'application elle-même tourne ensuite en PHP 8.5 dans Sail.

### 3. Copier le fichier d'environnement

```bash
cp .env.example .env
```

### 4. Installer les dépendances JavaScript
La clé d'application sera générée à l'étape 5, une fois les conteneurs démarrés.

```bash
npm install
```

La première fois que le conteneur démarre, Sail installe automatiquement le
`chrome-headless-shell` correspondant à la version de Puppeteer utilisée par
l'application. Le navigateur est conservé dans le volume Docker
`puppeteer-cache`.

### 5. Démarrer les conteneurs Sail
```bash
./vendor/bin/sail up -d --build
```

Trois services sont lancés :

| Service | Adresse |
|---|---|
| Application | http://localhost (port `APP_PORT`, 80 par défaut) |
| phpMyAdmin | http://localhost:8080 |
| MySQL | `localhost:33060` depuis la machine hôte (`FORWARD_DB_PORT`) |

#### Cas particulier — Apache (ou un autre service) occupe déjà le port 80

Erreur typique :

```
Error starting userland proxy: listen tcp4 0.0.0.0:80: bind: address already in use
```

**Identifier le coupable :**

```bash
# Linux/macOS
sudo lsof -i :80
sudo ss -tulpn | grep :80
```

**Option A — Arrêter Apache** (si tu n'en as pas besoin) :

```bash
# Linux
sudo systemctl stop apache2
sudo systemctl disable apache2   # pour qu'il ne redémarre pas au boot

# macOS
sudo apachectl stop
```

Sous Windows avec XAMPP/WAMP : arrête le service Apache depuis le panneau de contrôle XAMPP/WAMP.

**Option B — Changer le port utilisé par Sail** (si tu veux garder Apache actif) :

Dans le fichier `.env` :

```env
APP_PORT=8000
```

Le site sera alors accessible sur `http://localhost:8000`. N'utilise pas `8080` : ce port est déjà pris par phpMyAdmin.

Le même principe s'applique si MySQL tourne déjà localement et occupe le port redirigé :

```env
FORWARD_DB_PORT=33061
```

Puis relance :

```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

### 5. Générer la clé, lier le stockage et remplir la base de données

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan migrate --seed
```

`storage:link` crée le lien `public/storage` qui permet d'afficher les photos de personnage (non versionné : à refaire sur chaque nouvelle installation).

Les seeders remplissent les données de référence (races, classes, sous-classes, historiques, traits, capacités de classe, dons, objets, sorts, limites de magie) et créent un compte de test :

- **Courriel** : `test@example.com`
- **Mot de passe** : `test`

Pour repartir d'une base vide à tout moment :

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### 6. Ajouter l'alias `sail` (optionnel)

Pour éviter de taper `./vendor/bin/sail` à chaque commande, ajoute un alias.

**Bash** — édite `~/.bashrc` (ou `~/.bash_profile` sur macOS) :

```bash
echo "alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc
```

**Zsh** — édite `~/.zshrc` :

```bash
echo "alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'" >> ~/.zshrc
source ~/.zshrc
```

Tu peux ensuite lancer les commandes simplement avec :

```bash
sail up -d
sail artisan migrate
```

La suite du README utilise cet alias.

### Commandes utiles

```bash
sail up -d                   # démarrer les conteneurs en arrière-plan
sail down                    # arrêter les conteneurs
sail artisan migrate         # lancer les nouvelles migrations
sail artisan db:seed         # relancer les seeders
sail artisan tinker          # ouvrir Tinker
sail composer install        # exécuter Composer dans le conteneur
sail test                    # lancer les tests PHP
sail npm run test:js         # lancer les tests JavaScript (aucune dépendance npm requise)
sail logs                    # voir les logs des conteneurs
```

Le CSS et le JavaScript de l'application sont servis directement depuis `public/` : aucun build front-end (`npm run dev` / `npm run build`) n'est nécessaire pour faire tourner le site.

### Autres problèmes fréquents

- **`Permission denied` sur le script sail** :
  ```bash
  chmod +x vendor/bin/sail
  ```
- **Docker daemon non démarré** : ouvre Docker Desktop (ou `sudo systemctl start docker` sous Linux) avant de relancer `sail up`.
- **MySQL local déjà en cours d'exécution** (conflit de port) : arrête le service local (`sudo systemctl stop mysql`) ou change `FORWARD_DB_PORT` dans `.env` comme indiqué plus haut.
- **Le site affiche une erreur SQL (table manquante)** : les migrations n'ont pas été lancées, voir l'étape 5.
- **WSL2 (Windows)** : s'assurer que Docker Desktop utilise le backend WSL2, et placer le projet dans le système de fichiers WSL (pas sous `/mnt/c/...`) pour de meilleures performances.

## À faire avant d'appliquer les modifications sur sa branche

### 1. Linter — Laravel Pint

Vérifier le style de code (sans modifier les fichiers) :

```bash
sail pint --test
```

Si des fichiers ne respectent pas le style, laisser Pint corriger automatiquement :

```bash
sail pint
```

### 2. Tests + couverture de code

Générer le rapport de couverture HTML (Xdebug est déjà activé en mode `coverage` via `SAIL_XDEBUG_MODE` dans `.env.example`) :

```bash
sail artisan test --coverage-html=tests/reports/coverage
```

Ouvrir `tests/reports/coverage/index.html` dans le navigateur et vérifier :
- que tous les tests passent (aucun rouge)
- que le pourcentage de couverture global n'a pas baissé sous 90 %
- qu'il n'y a pas de fichier touché par la PR avec une couverture anormalement basse

Lancer aussi les tests JavaScript :

```bash
sail npm run test:js
```

### Autres vérifications utiles

- **`.env.example` à jour** si de nouvelles variables d'environnement ont été ajoutées.
- **Migrations et seeders propres** — vérifier qu'une base fraîche migre et se remplit sans erreur :
  ```bash
  sail artisan migrate:fresh --seed
  ```
- **Caches vidés** avant de tester en local pour éviter les faux positifs :
  ```bash
  sail artisan config:clear && sail artisan cache:clear && sail artisan route:clear
  ```
