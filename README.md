# CSCK543 Group Project — Recipe Web Application

## Completed Foundation Tasks (Issues #1, #2, and #3)

This setup provides the PHP 7/8 + MySQL foundation for the Recipe Web Application:

1. **[Issue #1](https://github.com/skovakina/CSCK543-group-project/issues/1) — Set Up PHP Application and MySQL Connection**
   - `config/database.php`: Provides `getDbConnection()` returning a configured `PDO` instance (`ERRMODE_EXCEPTION`, `FETCH_ASSOC`, prepared statements enabled).
   - Automatically connects to XAMPP MySQL (`127.0.0.1:3306`, database `chef_recipes_db`, user `root`) and auto-imports `sql/chef_recipes_db.sql` on first run if the database has not been created yet.
   - `.vscode/settings.json`: VS Code workspace configuration for PHP 7/8 development.
   - `index.php`: Starter entry point verifying the PHP + MySQL connection and displaying the loaded schema tables and sample BBC Food recipes.

2. **[Issue #2](https://github.com/skovakina/CSCK543-group-project/issues/2) — Design and Create the MySQL Database**
   - `sql/chef_recipes_db.sql`: Relational MySQL Workbench schema (`chef_recipes_db`) in Third Normal Form (3NF) with 8 InnoDB tables:
     - `users`: Stores user name, email, BCRYPT password hash, dietary preferences, cooking skill level, allergies, and bio.
     - `recipes`: Stores recipe title, slug, description, chef notes, BBC Food URL, prep/cook/total time, servings, difficulty, cost tier, and nutritional macros.
     - `categories` & `recipe_categories`: Stores course, dietary, and style categories (Starter, Main, Dessert, Breakfast & Brunch, Side Dish, Meat & Poultry, Vegetarian, Vegan, Healthy & Light, Baking & Pastry, Quick & Easy, Seafood & Fish) linked many-to-many to recipes.
     - `recipe_ingredients`: Stores ordered ingredient lists, numeric & formatted quantities, measurement units, preparation notes, and allergen flags.
     - `recipe_steps`: Stores ordered cooking steps (`step_number`), stage title, instructions, and explicit duration per step (`duration_minutes`).
     - `user_favourites`: Stores users' saved favourite recipes and personal notes.
     - `recipe_ratings`: Stores user ratings broken down into taste, aesthetics, difficulty, and overall rating.

3. **[Issue #3](https://github.com/skovakina/CSCK543-group-project/issues/3) — Populate the Database With Sample Recipes**
   - `sql/chef_recipes_db.sql` seeds all **8 specified BBC Food recipes** from the assignment brief (plus 4 additional BBC Food recipes = 12 total):
     1. [Spaghetti Bolognese](https://www.bbc.co.uk/food/recipes/spaghettibolognese_67868)
     2. [Vegan Pancakes](https://www.bbc.co.uk/food/recipes/vegan_american_pancakes_76094)
     3. [Healthy Pizza](https://www.bbc.co.uk/food/recipes/healthy_pizza_55143)
     4. [Easy Lamb Biryani](https://www.bbc.co.uk/food/recipes/easy_lamb_biryani_46729)
     5. [Couscous Salad](https://www.bbc.co.uk/food/recipes/dried_fruits_and_nuts_18053)
     6. [Plum Clafoutis](https://www.bbc.co.uk/food/recipes/plumclafoutis_11536)
     7. [Mango Pie](https://www.bbc.co.uk/food/recipes/mango_pie_18053)
     8. [Mushroom Doner](https://www.bbc.co.uk/food/recipes/mushroom_doner_22676)
   - Also includes 5 demo user accounts (password for all demo accounts: `Password123!`), sample saved favourites, and sample ratings.

---

## How to Run Locally on XAMPP & MySQL Workbench

1. **Place the project folder inside XAMPP `htdocs`:**
   - Windows: `C:\xampp\htdocs\CSCK543-group-project`
   - macOS: `/Applications/XAMPP/xamppfiles/htdocs/CSCK543-group-project`
2. **Start Apache and MySQL** in the XAMPP Control Panel.
3. **Import Database (Optional — Auto-Imported on First Load):**
   - Open `sql/chef_recipes_db.sql` in **MySQL Workbench** or **phpMyAdmin** and execute it, OR simply load `index.php` in your browser and `config/database.php` will automatically create and seed `chef_recipes_db`.
4. **Open in Google Chrome:**
   - Visit `http://localhost/CSCK543-group-project/`

---

## Using the Database Connection in Other Tasks (Issues #4–#8)

In any PHP page (`search.php`, `recipe.php`, `login.php`, `register.php`, `account.php`), include `config/database.php` and call `getDbConnection()`:

```php
<?php
require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();
```
