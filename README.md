# P6-SnowTricks

Création d'un site collaboratif de partage de figures de snowboard via le framework Symfony.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/7132d491db1f498ea3f6d97696e14743)](https://www.codacy.com/gh/NicolasHalberstadt/P6-SnowTricks/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=NicolasHalberstadt/P6-SnowTricks&amp;utm_campaign=Badge_Grade)

## Installation

1. Clonez le repository GitHub dans le dossier voulu :

```
    git clone https://github.com/NicolasHalberstadt/P6-SnowTricks.git
```

2. Configurez vos variables d'environnement tel que la connexion à la base de données ou votre serveur SMTP ou adresse
   mail dans le fichier `.env.local` qui devra être créé à la racine du projet en réalisant une copie du fichier `.env` :

```
cp .env .env.local
```

3. Téléchargez et installez les dépendances back-end du projet avec [Composer](https://getcomposer.org/download/) :

```
    composer install
```

4. Créez la base de données si elle n'existe pas déjà, taper la commande ci-dessous en vous plaçant dans le répertoire
   du projet :

```
    php bin/console doctrine:database:create
```

5. Créez les différentes tables de la base de données en appliquant les migrations :

```
    php bin/console doctrine:migrations:migrate
```

6. (Optionnel) Installez les 'fixtures' pour avoir un premier jeu de données :

```
php bin/console doctrine:fixtures:load
```
6bis. Connectez vous avec le compte utilisateur de démo :  
    - *Email* : snowtricks@example.com  
    - *Mot de passe* : SnowTricks123!*  

7. (Optionnel) Lancez le serveur Symfony pour tester le projet localement :
   ```
   symfony server:start
   ```

8. Félicitations 🎉 le projet est installé correctement, vous pouvez désormais commencer à l'utiliser à votre guise ! 👨‍💻
