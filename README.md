# CSCK543 Recipe Web App

This PHP and MySQL project lets visitors browse and search recipes. Registered users can save favourites and add a rating and optional review to a recipe.

## Project files

- `index.php`, `search.php`, `recipe.php`, `account.php`: the main pages.
- `login.php`, `register.php`, `logout.php`: account access.
- `includes/`: shared page layout, form helpers and recipe cards.
- `assets/`: CSS, JavaScript and recipe images.
- `config/database.php`: MySQL connection settings.
- `sql/chef_recipes_db.sql`: database tables and local sample data.

## Run the project with XAMPP

1. Copy the project folder into `C:\xampp\htdocs\CSCK543-group-project`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. For a fresh installation, import `sql/chef_recipes_db.sql` using phpMyAdmin or MySQL Workbench. The script drops and recreates the project's tables, so back up any existing project data before importing it.
4. The default connection in `config/database.php` uses database `chef_recipes_db`, MySQL user `root`, and an empty password. Change the settings there if your local MySQL account is different.
5. Open `http://localhost/CSCK543-group-project/` in Chrome.

The database script contains sample recipes, accounts, favourites, ratings and reviews for local demonstrations. These are test records, not real user activity. You can also register an account and create your own test data.

## Try the main features

1. Open **Search** and try a keyword, dietary filter, category, time limit or sort order.
2. Register or log in, then save a recipe with its heart button.
3. Open **My account** to see saved recipes.
4. Open a recipe, submit ratings and an optional review, then submit again to update your entry.
5. Log out and open the recipe again to see the saved ratings and reviews.
