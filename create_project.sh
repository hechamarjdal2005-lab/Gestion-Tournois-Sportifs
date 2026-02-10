#!/bin/bash

PROJECT_NAME="project"

# Root
mkdir -p "$PROJECT_NAME"

# Public
mkdir -p "$PROJECT_NAME/public/assets/css"
mkdir -p "$PROJECT_NAME/public/assets/js"
mkdir -p "$PROJECT_NAME/public/assets/images"

touch "$PROJECT_NAME/public/index.php"
touch "$PROJECT_NAME/public/login.php"

touch "$PROJECT_NAME/public/index.html"
touch "$PROJECT_NAME/public/login.html"

# CSS files
touch "$PROJECT_NAME/public/assets/css/style.css"
touch "$PROJECT_NAME/public/assets/css/auth.css"

# JS files
touch "$PROJECT_NAME/public/assets/js/main.js"
touch "$PROJECT_NAME/public/assets/js/auth.js"

# App
mkdir -p "$PROJECT_NAME/app/controllers"
mkdir -p "$PROJECT_NAME/app/models"
mkdir -p "$PROJECT_NAME/app/views"

# Routes
mkdir -p "$PROJECT_NAME/routes"
touch "$PROJECT_NAME/routes/web.php"

# Database
mkdir -p "$PROJECT_NAME/database"
touch "$PROJECT_NAME/database/schema.sql"

# API
mkdir -p "$PROJECT_NAME/api"
touch "$PROJECT_NAME/api/matchs.php"
touch "$PROJECT_NAME/api/equipes.php"
touch "$PROJECT_NAME/api/resultats.php"

echo "✅ PFE project structure + HTML/CSS/JS created successfully!"
