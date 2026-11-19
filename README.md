# CSCK543 Group Project — Recipe Web Application

## Application Foundation

This setup provides the PHP 7/8 + MySQL foundation for the Recipe Web Application:

1. **PHP Application and MySQL Connection**
   - `config/database.php`: Provides `getDbConnection()` returning a configured `PDO` instance (`ERRMODE_EXCEPTION`, `FETCH_ASSOC`, prepared statements enabled).
   - Connects to XAMPP MySQL (`127.0.0.1:3306`, database `chef_recipes_db`, user `root`). Database setup requires an explicit import; normal requests never import or reset data.
   - `.vscode/settings.json`: VS Code workspace configuration for PHP 7/8 development.
   - `index.php`: Recipe grid from MySQL, with shared navigation.

2. **MySQL Database**
   - `sql/chef_recipes_db.sql`: Relational MySQL Workbench schema (`chef_recipes_db`) in Third Normal Form (3NF) with 8 InnoDB tables:
     - `users`: Stores user name, email, BCRYPT password hash, dietary preferences, cooking skill level, allergies, and bio.
     - `recipes`: Stores recipe title, slug, description, chef notes, BBC Food URL, prep/cook/total time, servings, difficulty, cost tier, and nutritional macros.
     - `categories` & `recipe_categories`: Stores course, dietary, and style categories (Starter, Main, Dessert, Breakfast & Brunch, Side Dish, Meat & Poultry, Vegetarian, Vegan, Healthy & Light, Baking & Pastry, Quick & Easy, Seafood & Fish) linked many-to-many to recipes.
     - `recipe_ingredients`: Stores ordered ingredient lists, numeric & formatted quantities, measurement units, preparation notes, and allergen flags.
     - `recipe_steps`: Stores ordered cooking steps (`step_number`), stage title, instructions, and explicit duration per step (`duration_minutes`).
     - `user_favourites`: Stores users' saved favourite recipes and personal notes.
     - `recipe_ratings`: Stores one rating and written review per user and recipe, including taste, appearance, difficulty, and overall scores.

3. **Sample Recipes**
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

4. **Ratings, Reviews, and Sources**
   - Signed-in users can add or update their ratings and optionally include a written review on a recipe page.
   - Recipe pages and cards show the average overall score and number of ratings.
   - Recipe pages link to the original BBC Food recipe when a BBC source URL is stored.

5. **Security and Accessibility**
   - Passwords use PHP's `password_hash()` and `password_verify()` functions.
   - State-changing forms use session CSRF tokens, and sign-out is a POST form.
   - Database queries use PDO prepared statements; user-provided review text is escaped before display.
   - Pages include a skip link, visible keyboard focus, labelled controls, and responsive layouts.

---

## macOS Setup

Install PHP: `brew install php`. If MySQL is not already installed and running: `brew install mysql && brew services start mysql`.

From the project folder, import once (this resets application tables):

```zsh
mysql -u root -p < sql/chef_recipes_db.sql
```

Start the website in Terminal (zsh), entering your MySQL root password when prompted:

```zsh
read -r -s "DB_PASS?MySQL root password: "
echo
export DB_PASS
php -S localhost:8000
```

## How to Run Locally on XAMPP & MySQL Workbench

1. **Place the project folder inside XAMPP `htdocs`:**
   - Windows: `C:\xampp\htdocs\CSCK543-group-project`
   - macOS: `/Applications/XAMPP/xamppfiles/htdocs/CSCK543-group-project`
2. **Start Apache and MySQL** in the XAMPP Control Panel.
3. **Import Database (Required for a Fresh Installation):**
   - Open `sql/chef_recipes_db.sql` in **MySQL Workbench** or **phpMyAdmin** and execute it to create and seed `chef_recipes_db`.
   - **Warning:** this script drops and recreates application tables. Do not re-import it into an existing database unless you intend to reset its data and have a backup.
   - If the database is already set up, skip the import. Connection or schema errors must be investigated, not fixed by re-importing the seed script.
4. **Open in Google Chrome:**
   - Visit `http://localhost/CSCK543-group-project/`
5. **Try ratings and reviews:**
   - Register or log in, open a recipe, choose a score for each rating field, and submit a short review.
   - Submit another review for the same recipe while signed in to update your existing entry.
   - Open the recipe while signed out to view its ratings and reviews.

---

## Using the Database Connection

In any PHP page (`search.php`, `recipe.php`, `login.php`, `register.php`, `account.php`), include `config/database.php` and call `getDbConnection()`:

```php
<?php
require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();
```
