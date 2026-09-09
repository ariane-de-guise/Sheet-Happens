# Guide de Démarrage du Projet

Ce document explique comment configurer et exécuter ce projet Laravel sur votre machine locale en utilisant **Laravel Sail** (l'environnement Docker de Laravel).

## 📦 Prérequis

Puisque ce projet utilise la conteneurisation, vous n'avez pas besoin d'installer PHP ou d'autres outils localement. Vous devez uniquement avoir :

1. **Docker Engine**
2. **Docker Compose**
3. **PHP 8.4

*Note pour les utilisateurs Linux : assurez-vous que le service Docker tourne et que votre utilisateur est dans le groupe `docker` pour éviter les conflits de permissions.*

## 🚀 Installation et Configuration

### 1. Installation des dépendances Composer

Lorsque vous venez de cloner ce projet, le dossier `vendor` (qui contient toutes les dépendances PHP) est absent. Vous devez d'abord l'installer via un conteneur éphémère. 

Exécutez cette commande à la racine du projet (assurez-vous d'être dans le dossier du projet) :

```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
```

## ⛵ Démarrer l'application

### 1. Lancer les conteneurs

Une fois les dépendances installées, vous pouvez démarrer les services en arrière-plan (serveur web, base de données, etc.) avec Sail :

```bash
./vendor/bin/sail up -d
```

*Votre application devrait maintenant être accessible à l'adresse : [http://localhost](http://localhost).*

### 2. Initialiser l'application

Lors du tout premier lancement, il est nécessaire de générer la clé de sécurité de l'application et de construire l'architecture de la base de données :

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
```

## 🛠 Simplifier les commandes (Optionnel)

Pour éviter de devoir taper `./vendor/bin/sail` à chaque fois, vous pouvez créer un alias dans votre terminal :

```bash
alias sail='sh $([ -f artisan ] && echo vendor/bin/sail || echo vendor/bin/sail)'
```

Vous pourrez ensuite utiliser `sail` directement pour vos opérations de développement :

## 🛑 Arrêter les services

Lorsque vous avez terminé de travailler, vous pouvez stopper proprement l'environnement avec :

```bash
./vendor/bin/sail down
```
