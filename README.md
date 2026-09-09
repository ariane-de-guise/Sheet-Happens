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

#### Cas normal — PHP est installé localement

```bash
composer install
```

#### Cas particulier — PHP 8.4 n'est pas installé (ou mauvaise version)

Pas besoin d'installer PHP sur la machine : on utilise un conteneur Docker jetable pour exécuter Composer une seule fois.

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

> Sur Windows (PowerShell), remplace `-u "$(id -u):$(id -g)"` par rien (pas nécessaire) et adapte le chemin du volume avec `${PWD}` :
> ```powershell
> docker run --rm -v ${PWD}:/var/www/html -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
> ```

### 3. Copier le fichier d'environnement

```bash
cp .env.example .env
```

### 4. Démarrer les conteneurs Sail
```bash
./vendor/bin/sail up -d
```

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
APP_PORT=8080
```

Le site sera alors accessible sur `http://localhost:8080`. Le même principe s'applique si un autre service local occupe déjà un port utilisé par Sail (MySQL 3306, Postgres 5432, Redis 6379, etc.) :

```env
FORWARD_DB_PORT=33061
FORWARD_REDIS_PORT=63791
```

Puis relance :

```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

### 5. Ajouter l'alias `sail` au bash

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

### Commandes utiles

```bash
sail up -d              # démarrer les conteneurs en arrière-plan
sail down                # arrêter les conteneurs
sail artisan migrate      # lancer les migrations
sail artisan tinker       # ouvrir Tinker
sail composer install     # exécuter Composer dans le conteneur
sail npm run dev          # lancer le build front-end
sail test                 # lancer les tests
sail logs                 # voir les logs des conteneurs
```

### Autres problèmes fréquents

- **`Permission denied` sur le script sail** :
```bash
  chmod +x vendor/bin/sail
```
- **Docker daemon non démarré** : ouvre Docker Desktop (ou `sudo systemctl start docker` sous Linux) avant de relancer `sail up`.
- **MySQL/Postgres local déjà en cours d'exécution** (conflit de port 3306/5432) : arrête le service local (`sudo systemctl stop mysql`) ou change `FORWARD_DB_PORT` dans `.env` comme indiqué plus haut.
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

Générer le rapport de couverture HTML :

```bash
sail artisan test --coverage-html=tests/reports/coverage
```

Ouvrir `tests/reports/index.html` dans le navigateur et vérifier :
- que tous les tests passent (aucun rouge)
- que le pourcentage de couverture global n'a pas baissé à moins de 90%
- qu'il n'y a pas de fichier touché par la PR avec une couverture anormalement basse

### Autres vérifications utiles

- **`.env.example` à jour** si de nouvelles variables d'environnement ont été ajoutées.
- **Migrations propres** — vérifier qu'une base fraîche migre sans erreur :
  ```bash
  sail artisan migrate:fresh --seed
  ```
  
- **Caches vidés** avant de tester en local pour éviter les faux positifs :
  ```bash
  sail artisan config:clear && sail artisan cache:clear && sail artisan route:clear
  ```
