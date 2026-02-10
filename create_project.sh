#!/bin/bash

# Dossier principal
mkdir -p GESTION-TOURNOIS
cd GESTION-TOURNOIS || exit

# assets
mkdir -p assets/css assets/js assets/images
touch assets/css/style.css
touch assets/css/bootstrap.min.css
touch assets/js/main.js
touch assets/js/jquery.min.js
touch assets/images/logo.png
touch assets/images/favicon.ico

# includes
mkdir -p includes/config includes/lib includes/templates
touch includes/config/database.php
touch includes/lib/auth.php
touch includes/templates/header.php
touch includes/templates/footer.php
touch includes/templates/navigation.php

# modules
mkdir -p modules/auth modules/equipes modules/matches modules/tournois modules/admin

# auth module
touch modules/auth/login.php
touch modules/auth/register.php
touch modules/auth/logout.php
touch modules/auth/profile.php

# equipes module
touch modules/equipes/index.php
touch modules/equipes/create.php
touch modules/equipes/edit.php
touch modules/equipes/delete.php
touch modules/equipes/view.php

# matches module
touch modules/matches/index.php
touch modules/matches/create.php
touch modules/matches/edit.php
touch modules/matches/results.php

# tournois module
touch modules/tournois/index.php
touch modules/tournois/create.php
touch modules/tournois/bracket.php
touch modules/tournois/standings.php

# admin module
touch modules/admin/dashboard.php
touch modules/admin/users.php
touch modules/admin/settings.php

# api
mkdir -p api
touch api/equipes.php
touch api/matches.php
touch api/tournois.php

# database
mkdir -p database/backup
touch database/schema.sql
touch database/data.sql

# uploads
mkdir -p uploads/logos uploads/documents

# fichiers racine
touch index.php
touch login.php
touch dashboard.php
touch .htaccess
touch config.php
touch functions.php
touch README.md

echo "✅ Structure GESTION-TOURNOIS créée avec succès !"