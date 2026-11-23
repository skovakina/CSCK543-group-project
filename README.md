# CSCK543 Group Project - Recipe Web Application

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
     - `recipe_ratings`: Stores user ratings broken down into taste, aesthetics, difficulty, and overall rating.

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
   - Also includes 5 demo user accounts (password for all demo accounts: `Password123!`), sample saved favourites, and sample ratings. Demo logins: chef@example.test and demo1@example.test to demo4@example.test.

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
   - Visit `http://localhost/CSCK543-group-project/` (use your folder name if it is different)

---

## Using the Database Connection

In any PHP page (`search.php`, `recipe.php`, `login.php`, `register.php`, `account.php`), include `config/database.php` and call `getDbConnection()`:

```php
<?php
require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();
```

## Ratings, Reviews, and Recipe Sources

- The accounts, favourites, ratings, and reviews included in the SQL seed are sample data for local testing.
- Signed-in users can rate a recipe for overall score, taste, appearance, and difficulty.
- Each user can save one rating per recipe. Submitting again updates that rating.
- Users can include an optional review title and comment. Ratings and written reviews appear on the recipe page, with the average score and rating count also shown on recipe cards.
- Recipe pages include a link to the BBC Food source stored for that recipe.

## Security, Validation, and Accessibility

- Passwords are stored with PHP's `password_hash()` and checked with `password_verify()`.
- Forms that change data use session CSRF tokens. Logging out uses a POST form.
- Queries that use submitted values use PDO prepared statements, and user-entered text is escaped before it is shown in a page.
- Registration, login, search, and rating forms validate input in the browser and again in PHP.
- Pages include a skip link, visible keyboard focus, labelled form controls, and a responsive layout with a mobile navigation menu.

## Screenshots and Local Testing

- The `screenshots/` folder contains 10 screenshots of the main views, including two mobile screenshots.
- After setup, try searching and sorting recipes, registering and logging in, saving a favourite, and submitting or updating a rating and review.
- Check the site at desktop and mobile widths. Use the keyboard to move through the navigation and forms and confirm the focus indicator is visible.
