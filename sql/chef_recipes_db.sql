-- ============================================================================
-- CSCK543 Networks and Web Technology - End of Module Group Assignment (Group B)
-- Database: chef_recipes_db
-- Group Members: Lolwa Al-Kuwari, Svetlana Kovakina, Namakau Mbumwae, Kyle Andrew Sutherland
-- Target DBMS: MySQL 5.7+ / 8.0+ / MariaDB 10.4+ (XAMPP & MySQL Workbench Compatible)
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `chef_recipes_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `chef_recipes_db`;

DROP TABLE IF EXISTS `user_favourites`;
DROP TABLE IF EXISTS `recipe_ratings`;
DROP TABLE IF EXISTS `recipe_steps`;
DROP TABLE IF EXISTS `recipe_ingredients`;
DROP TABLE IF EXISTS `recipe_categories`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `recipes`;
DROP TABLE IF EXISTS `users`;

-- ----------------------------------------------------------------------------
-- 1. Table structure for `users`
-- ----------------------------------------------------------------------------
CREATE TABLE `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `dietary_preference` VARCHAR(60) NOT NULL DEFAULT 'No Restriction',
  `cooking_skill` ENUM('Beginner', 'Intermediate', 'Advanced', 'Professional') NOT NULL DEFAULT 'Intermediate',
  `allergies` VARCHAR(255) DEFAULT 'None',
  `favourite_cuisine` VARCHAR(80) DEFAULT 'Mediterranean & British',
  `bio` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Table structure for `recipes`
-- ----------------------------------------------------------------------------
CREATE TABLE `recipes` (
  `recipe_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL,
  `subtitle` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `chef_notes` TEXT DEFAULT NULL,
  `bbc_url` VARCHAR(255) NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `prep_time_mins` INT UNSIGNED NOT NULL DEFAULT 15,
  `cook_time_mins` INT UNSIGNED NOT NULL DEFAULT 20,
  `total_time_mins` INT UNSIGNED NOT NULL DEFAULT 35,
  `servings` INT UNSIGNED NOT NULL DEFAULT 4,
  `difficulty` ENUM('Easy', 'Medium', 'Hard') NOT NULL DEFAULT 'Easy',
  `cost_tier` ENUM('Budget', 'Moderate', 'Gourmet') NOT NULL DEFAULT 'Budget',
  `calories_per_serving` INT UNSIGNED NOT NULL DEFAULT 350,
  `protein_g` DECIMAL(5,1) NOT NULL DEFAULT 12.0,
  `carbs_g` DECIMAL(5,1) NOT NULL DEFAULT 45.0,
  `fat_g` DECIMAL(5,1) NOT NULL DEFAULT 10.0,
  `fibre_g` DECIMAL(5,1) NOT NULL DEFAULT 5.0,
  `is_vegetarian` TINYINT(1) NOT NULL DEFAULT 0,
  `is_vegan` TINYINT(1) NOT NULL DEFAULT 0,
  `is_gluten_free` TINYINT(1) NOT NULL DEFAULT 0,
  `is_dairy_free` TINYINT(1) NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recipe_id`),
  UNIQUE KEY `uq_recipes_slug` (`slug`),
  KEY `idx_recipes_difficulty` (`difficulty`),
  KEY `idx_recipes_total_time` (`total_time_mins`),
  KEY `idx_recipes_calories` (`calories_per_serving`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Table structure for `categories`
-- ----------------------------------------------------------------------------
CREATE TABLE `categories` (
  `category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(60) NOT NULL,
  `category_group` ENUM('Course', 'Dietary', 'Style') NOT NULL DEFAULT 'Course',
  `icon_symbol` VARCHAR(20) NOT NULL DEFAULT '🍽️',
  `description` VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Table structure for `recipe_categories` (Many-to-Many)
-- ----------------------------------------------------------------------------
CREATE TABLE `recipe_categories` (
  `recipe_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`recipe_id`, `category_id`),
  CONSTRAINT `fk_rc_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rc_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Table structure for `recipe_ingredients`
-- ----------------------------------------------------------------------------
CREATE TABLE `recipe_ingredients` (
  `ingredient_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipe_id` INT UNSIGNED NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 1,
  `ingredient_name` VARCHAR(120) NOT NULL,
  `quantity_numeric` DECIMAL(8,2) DEFAULT NULL,
  `quantity_display` VARCHAR(60) NOT NULL,
  `unit` VARCHAR(40) DEFAULT '',
  `prep_note` VARCHAR(150) DEFAULT NULL,
  `is_allergen` TINYINT(1) NOT NULL DEFAULT 0,
  `allergen_type` VARCHAR(60) DEFAULT NULL,
  PRIMARY KEY (`ingredient_id`),
  KEY `idx_ri_recipe` (`recipe_id`),
  KEY `idx_ri_name` (`ingredient_name`),
  CONSTRAINT `fk_ri_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Table structure for `recipe_steps`
-- ----------------------------------------------------------------------------
CREATE TABLE `recipe_steps` (
  `step_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipe_id` INT UNSIGNED NOT NULL,
  `step_number` INT UNSIGNED NOT NULL,
  `stage_title` VARCHAR(120) NOT NULL,
  `instruction` TEXT NOT NULL,
  `duration_minutes` INT UNSIGNED NOT NULL DEFAULT 5,
  `tip_or_temp` VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (`step_id`),
  UNIQUE KEY `uq_recipe_step_num` (`recipe_id`, `step_number`),
  CONSTRAINT `fk_rs_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. Table structure for `recipe_ratings`
-- ----------------------------------------------------------------------------
CREATE TABLE `recipe_ratings` (
  `rating_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipe_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `taste_rating` TINYINT UNSIGNED NOT NULL CHECK (`taste_rating` BETWEEN 1 AND 5),
  `aesthetics_rating` TINYINT UNSIGNED NOT NULL CHECK (`aesthetics_rating` BETWEEN 1 AND 5),
  `difficulty_rating` TINYINT UNSIGNED NOT NULL CHECK (`difficulty_rating` BETWEEN 1 AND 5),
  `overall_rating` DECIMAL(3,2) NOT NULL,
  `review_title` VARCHAR(120) NOT NULL,
  `review_comment` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `uq_recipe_user_rating` (`recipe_id`, `user_id`),
  KEY `idx_rr_recipe` (`recipe_id`),
  KEY `idx_rr_overall` (`overall_rating`),
  CONSTRAINT `fk_rr_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 8. Table structure for `user_favourites`
-- ----------------------------------------------------------------------------
CREATE TABLE `user_favourites` (
  `user_id` INT UNSIGNED NOT NULL,
  `recipe_id` INT UNSIGNED NOT NULL,
  `personal_note` VARCHAR(255) DEFAULT NULL,
  `saved_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `recipe_id`),
  CONSTRAINT `fk_uf_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uf_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- 1. Seed Users (Password for all accounts is: Password123!)
INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `dietary_preference`, `cooking_skill`, `allergies`, `favourite_cuisine`, `bio`, `created_at`) VALUES
(1, 'Lolwa Al-Kuwari', 'sglalkuw@liverpool.ac.uk', '$2y$10$Ih4FvvupWK2wtZjZ2ol2SuA3CB3t/tOW5fWihIO1RNDM2W1oshvkC', 'Halal & Healthy', 'Advanced', 'None', 'Middle Eastern & Mediterranean', 'Passionate about nutritious family meals, aromatic spices, and accessible web design.', '2026-10-15 10:00:00'),
(2, 'Svetlana Kovakina', 'svetlana@liverpool.ac.uk', '$2y$10$Ih4FvvupWK2wtZjZ2ol2SuA3CB3t/tOW5fWihIO1RNDM2W1oshvkC', 'Vegetarian', 'Intermediate', 'Peanuts', 'French Patisserie & Salads', 'Loves seasonal fruit desserts, French baking techniques, and vibrant plant-forward dishes.', '2026-10-16 11:30:00'),
(3, 'Namakau Mbumwae', 'namakau@liverpool.ac.uk', '$2y$10$Ih4FvvupWK2wtZjZ2ol2SuA3CB3t/tOW5fWihIO1RNDM2W1oshvkC', 'Vegan', 'Advanced', 'Dairy, Eggs', 'Plant-Based Street Food', 'Exploring creative vegan alternatives, high-fibre grains, and hearty weekend brunches.', '2026-10-17 14:15:00'),
(4, 'Kyle Andrew Sutherland', 'kyle@liverpool.ac.uk', '$2y$10$Ih4FvvupWK2wtZjZ2ol2SuA3CB3t/tOW5fWihIO1RNDM2W1oshvkC', 'No Restriction', 'Intermediate', 'Shellfish', 'Italian & British Classics', 'Enjoys slow-simmered ragùs, artisan pizzas, and bold aromatic curries.', '2026-10-18 09:45:00'),
(5, 'Chef Marco Bellini', 'chef@culinarystudio.co.uk', '$2y$10$Ih4FvvupWK2wtZjZ2ol2SuA3CB3t/tOW5fWihIO1RNDM2W1oshvkC', 'No Restriction', 'Professional', 'None', 'Modern British & Global Fusion', 'Celebrity Chef & founder of Chef Marco Culinary Studio. Dedicated to making restaurant-grade cooking approachable for every home kitchen.', '2026-10-10 08:00:00');

-- 2. Seed Categories (12 Categories across Course, Dietary, and Style)
INSERT INTO `categories` (`category_id`, `name`, `slug`, `category_group`, `icon_symbol`, `description`) VALUES
(1, 'Starter', 'starter', 'Course', '🥗', 'Light appetizers, salads, and sharing plates to begin a meal.'),
(2, 'Main', 'main', 'Course', '🍲', 'Hearty centrepiece dishes for lunch and dinner.'),
(3, 'Dessert', 'dessert', 'Course', '🥧', 'Sweet bakes, fruit tarts, and indulgent puddings.'),
(4, 'Breakfast & Brunch', 'breakfast', 'Course', '🥞', 'Morning classics and weekend brunch favourites.'),
(5, 'Side Dish', 'side', 'Course', '🥙', 'Flavorful accompaniments and grain salads.'),
(6, 'Meat & Poultry', 'meat', 'Dietary', '🥩', 'Savory beef, lamb, and chicken dishes rich in protein.'),
(7, 'Vegetarian', 'vegetarian', 'Dietary', '🌿', 'Meat-free recipes packed with vegetables, fruits, or dairy.'),
(8, 'Vegan', 'vegan', 'Dietary', '🌱', '100% plant-based dishes free from all animal products.'),
(9, 'Healthy & Light', 'healthy', 'Style', '💚', 'Nutritionally balanced recipes under 500 kcal per serving.'),
(10, 'Baking & Pastry', 'baking', 'Style', '🧁', 'Oven-baked pastries, pies, and patisserie classics.'),
(11, 'Quick & Easy', 'quick', 'Style', '⚡', 'Approachable recipes ready in 35 minutes or under.'),
(12, 'Seafood & Fish', 'seafood', 'Dietary', '🐟', 'Omega-rich salmon and coastal seafood dishes.');

-- 3. Seed 12 Authentic BBC Food Recipes with Official BBC Food Photography (.jpg)
INSERT INTO `recipes` (
  `recipe_id`, `title`, `slug`, `subtitle`, `description`, `chef_notes`, `bbc_url`, `image_path`,
  `prep_time_mins`, `cook_time_mins`, `total_time_mins`, `servings`, `difficulty`, `cost_tier`,
  `calories_per_serving`, `protein_g`, `carbs_g`, `fat_g`, `fibre_g`,
  `is_vegetarian`, `is_vegan`, `is_gluten_free`, `is_dairy_free`, `is_featured`
) VALUES
(
  1,
  'Spaghetti Bolognese',
  'spaghetti-bolognese',
  'Rich, slow-simmered beef ragù with sun-dried tomatoes, red wine, and fresh basil',
  'Everyone needs a great Spaghetti Bolognese recipe, and this classic BBC Food crowd-pleaser delivers deep, comforting Italian flavour. Finely diced onions, carrots, celery, and pancetta form a traditional soffritto base before lean minced beef is browned and simmered slowly with tomatoes, herbs, and sun-dried tomato paste.',
  'Chef Marco Tip: Take your time browning the beef in batches so it caramelises rather than steams, and splash in a little starchy pasta cooking water right before tossing.',
  'https://www.bbc.co.uk/food/recipes/spaghettibolognese_67868',
  'assets/images/spaghetti_bolognese.jpg',
  25, 90, 115, 6, 'Easy', 'Budget',
  540, 34.5, 58.0, 16.5, 6.2,
  0, 0, 0, 0, 1
),
(
  2,
  'Vegan Pancakes',
  'vegan-pancakes',
  'Fluffy American-style plant-based pancakes with maple syrup and fresh berries',
  'Try this easy vegan American pancake recipe for a decadent plant-based breakfast or brunch. Using self-raising flour, baking powder, and creamy plant milk whisked into a smooth batter, these golden stacks cook to airy perfection in minutes without eggs or dairy.',
  'Chef Marco Tip: Let the batter rest for 5 minutes before ladling into the pan so the raising agents activate for maximum fluffiness.',
  'https://www.bbc.co.uk/food/recipes/vegan_american_pancakes_76094',
  'assets/images/vegan_pancakes.jpg',
  10, 15, 25, 4, 'Easy', 'Budget',
  295, 6.4, 52.0, 6.2, 3.1,
  1, 1, 0, 1, 1
),
(
  3,
  'Healthy Pizza',
  'healthy-pizza',
  'Crispy seeded wholemeal flatbread pizza with courgette ribbons, mozzarella, and rocket',
  'A lighter, nutrient-packed homemade pizza that needs no yeast proofing time. Made with wholemeal self-raising dough and topped with a vibrant passata sauce, ribbons of fresh courgette, sweet red pepper, reduced-fat mozzarella, and peppery fresh rocket leaves.',
  'Chef Marco Tip: Pre-heat your baking tray in the oven for 10 minutes before sliding the pizza onto it—this guarantees a crisp, pizzeria-style base.',
  'https://www.bbc.co.uk/food/recipes/healthy_pizza_55143',
  'assets/images/healthy_pizza.jpg',
  15, 20, 35, 2, 'Easy', 'Budget',
  430, 21.0, 56.0, 12.0, 8.5,
  1, 0, 0, 0, 1
),
(
  4,
  'Easy Lamb Biryani',
  'easy-lamb-biryani',
  'Fragrant one-pot spiced lamb and basmati rice layered with caramelised onions and mint',
  'This streamlined Easy Lamb Biryani captures all the aromatic warmth of traditional Indian celebratory rice dishes without hours of complex prep. Tender cubed lamb shoulder is marinated with ginger, garlic, garam masala, and cumin, then steamed under fluffy saffron-tinted basmati rice.',
  'Chef Marco Tip: Wrap a clean tea towel around the pot lid during the final 10-minute resting steam (dum) to trap every bit of fragrant steam.',
  'https://www.bbc.co.uk/food/recipes/easy_lamb_biryani_46729',
  'assets/images/easy_lamb_biryani.jpg',
  20, 55, 75, 4, 'Medium', 'Moderate',
  610, 38.0, 64.0, 21.0, 4.4,
  0, 0, 1, 0, 1
),
(
  5,
  'Couscous Salad',
  'couscous-salad',
  'Jewelled Moroccan-style couscous with dried apricots, roasted nuts, herbs, and citrus dressing',
  'Inspired by BBC Food''s dried fruits and nuts couscous salad, this vibrant dish pairs fluffy vegetable-stock-infused couscous with sweet chopped dried apricots, cranberries, toasted almonds, pistachio nuts, spring onions, and a zesty lemon-coriander olive oil dressing.',
  'Chef Marco Tip: Fluff the steamed couscous with a fork rather than a spoon while still warm, drizzling olive oil first so every grain stays separate and light.',
  'https://www.bbc.co.uk/food/recipes/dried_fruits_and_nuts_18053',
  'assets/images/couscous_salad.jpg',
  15, 10, 25, 4, 'Easy', 'Budget',
  365, 9.8, 48.0, 14.5, 6.8,
  1, 1, 0, 1, 1
),
(
  6,
  'Plum Clafoutis',
  'plum-clafoutis',
  'Classic French baked custard dessert studded with ruby-red roasted plums and vanilla',
  'Originating from the Limousin region of France, this Plum Clafoutis bakes halved ripe plums in a silky vanilla-scented pancake-like batter enriched with eggs, milk, and double cream until golden and puffed, dusted generously with icing sugar.',
  'Chef Marco Tip: Serve lukewarm straight from the baking dish—about 15 minutes out of the oven is when the custard texture is at its silkier peak.',
  'https://www.bbc.co.uk/food/recipes/plumclafoutis_11536',
  'assets/images/plum_clafoutis.jpg',
  15, 40, 55, 6, 'Medium', 'Moderate',
  340, 8.2, 39.0, 16.8, 2.9,
  1, 0, 0, 0, 1
),
(
  7,
  'Mango Pie',
  'mango-pie',
  'Buttery spiced shortcrust pie filled with fragrant Alfonso mango, lime zest, and cardamom',
  'A tropical twist on the classic fruit pie from BBC Food. Sweet, ripe mango slices are tossed with aromatic green cardamom, lime juice, and golden caster sugar, then baked inside a crisp, buttery pastry lattice crust until bubbling and golden brown.',
  'Chef Marco Tip: Brush the pastry lattice with a little milk and sprinkle demerara sugar right before baking for a sparkling, bakery-worthy crunch.',
  'https://www.bbc.co.uk/food/recipes/mango_pie_18053',
  'assets/images/mango_pie.jpg',
  30, 45, 75, 6, 'Medium', 'Moderate',
  415, 5.5, 54.0, 19.5, 3.6,
  1, 0, 0, 0, 1
),
(
  8,
  'Mushroom Doner',
  'mushroom-doner',
  'Charred shawarma-spiced oyster & portobello mushroom flatbread wrap with garlic tahini slaw',
  'All the smoky, savoury satisfaction of a late-night doner kebab reimagined as a nutritious plant-based showstopper. Thinly shaved portobello and oyster mushrooms are marinated in smoked paprika, cumin, coriander, and oregano, roasted until crispy-edged, and piled into warm flatbreads with pickled red cabbage and cooling garlic sauce.',
  'Chef Marco Tip: Spread the marinated mushrooms in a single layer across two large baking trays so the edges crisp up like authentic rotisserie shavings.',
  'https://www.bbc.co.uk/food/recipes/mushroom_doner_22676',
  'assets/images/mushroom_doner.jpg',
  15, 20, 35, 4, 'Easy', 'Budget',
  380, 12.5, 49.0, 13.0, 7.4,
  1, 1, 0, 1, 1
),
(
  9,
  'Chicken Tikka Masala',
  'chicken-tikka-masala',
  'Chargrilled yogurt-marinated chicken breast simmered in a velvety spiced tomato and cream sauce',
  'One of Britain and India''s most beloved restaurant classics from BBC Food. Pieces of chicken breast are marinated in spiced yogurt, seared until lightly charred, and folded into a rich, aromatic masala gravy infused with ginger, garlic, garam masala, paprika, and single cream.',
  'Chef Marco Tip: Toast your whole spices for 45 seconds in a dry pan before grinding—it doubles the warmth and fragrance of the masala sauce.',
  'https://www.bbc.co.uk/food/recipes/chickentikkamasala_67780',
  'assets/images/chicken_tikka_masala.jpg',
  20, 30, 50, 4, 'Medium', 'Moderate',
  490, 39.5, 18.0, 28.0, 3.8,
  0, 0, 1, 0, 1
),
(
  10,
  'Salmon en Croûte',
  'salmon-en-croute',
  'Succulent Scottish salmon fillet baked in golden puff pastry with creamy spinach and watercress',
  'An effortless dinner-party centrepiece from BBC Food. Prime skinless salmon fillets are layered with a vibrant cream cheese, spinach, lemon zest, and fresh dill filling, wrapped in crisp puff pastry, and baked until golden brown.',
  'Chef Marco Tip: Chill the assembled pastry parcel for 15 minutes before baking so the puff pastry rises evenly with sharp, flaky layers.',
  'https://www.bbc.co.uk/food/recipes/salmon_in_pastry_84486',
  'assets/images/salmon_en_croute.jpg',
  20, 25, 45, 4, 'Medium', 'Gourmet',
  585, 34.0, 31.0, 35.5, 2.8,
  0, 0, 0, 0, 1
),
(
  11,
  'Roasted Butternut Squash Risotto',
  'roasted-butternut-squash-risotto',
  'Creamy Italian Arborio rice slowly cooked with sweet caramelised squash, sage, and Parmesan',
  'Sweet cubes of oven-roasted butternut squash and crispy fried sage leaves elevate this velvety vegetarian risotto from BBC Food. Slowly ladling hot vegetable stock into toasted Arborio rice releases natural starches for a luxurious texture.',
  'Chef Marco Tip: Rest the risotto with the lid on for 2 minutes off the heat after stirring in the cold butter and Parmesan (mantecatura) for silkiness.',
  'https://www.bbc.co.uk/food/recipes/tom_kerridges_butternut_56661',
  'assets/images/butternut_risotto.jpg',
  15, 35, 50, 4, 'Medium', 'Budget',
  445, 13.0, 66.0, 13.5, 5.9,
  1, 0, 1, 0, 1
),
(
  12,
  'Sticky Toffee Pudding',
  'sticky-toffee-pudding',
  'Warm date-studded sponge pudding drenched in glossy dark muscovado butterscotch sauce',
  'The ultimate British comfort pudding from BBC Food. Finely chopped Medjool dates softened in boiling tea create an irresistibly moist sponge cake, baked until springy and served swimming in a rich double cream and dark muscovado toffee sauce.',
  'Chef Marco Tip: Prick the warm sponge all over with a cocktail stick before pouring over the first half of the hot toffee sauce so it soaks right to the centre.',
  'https://www.bbc.co.uk/food/recipes/sticky_toffee_pudding_05454',
  'assets/images/sticky_toffee_pudding.jpg',
  20, 35, 55, 6, 'Easy', 'Budget',
  520, 6.2, 68.0, 24.5, 3.4,
  1, 0, 0, 0, 1
);

-- 4. Seed Recipe-Category Associations
INSERT INTO `recipe_categories` (`recipe_id`, `category_id`) VALUES
(1, 2), (1, 6),
(2, 4), (2, 3), (2, 7), (2, 8), (2, 9), (2, 10), (2, 11),
(3, 2), (3, 7), (3, 9), (3, 11),
(4, 2), (4, 6),
(5, 1), (5, 5), (5, 7), (5, 8), (5, 9), (5, 11),
(6, 3), (6, 7), (6, 10),
(7, 3), (7, 7), (7, 10),
(8, 2), (8, 7), (8, 8), (8, 9), (8, 11),
(9, 2), (9, 6), (9, 9),
(10, 2), (10, 10), (10, 12),
(11, 2), (11, 7), (11, 9),
(12, 3), (12, 7), (12, 10);

-- 5. Seed Recipe Ingredients (for all 12 recipes)
INSERT INTO `recipe_ingredients` (`recipe_id`, `sort_order`, `ingredient_name`, `quantity_numeric`, `quantity_display`, `unit`, `prep_note`, `is_allergen`, `allergen_type`) VALUES
-- Recipe 1: Spaghetti Bolognese
(1, 1, 'Olive oil', 2.00, '2 tbsp', 'tbsp', 'for frying', 0, NULL),
(1, 2, 'Smoked pancetta or bacon lardons', 100.00, '100g', 'g', 'finely cubed', 0, NULL),
(1, 3, 'Large onions', 2.00, '2', 'whole', 'finely chopped', 0, NULL),
(1, 4, 'Garlic cloves', 3.00, '3', 'cloves', 'crushed', 0, NULL),
(1, 5, 'Carrots', 2.00, '2', 'medium', 'finely diced', 0, NULL),
(1, 6, 'Celery sticks', 2.00, '2', 'sticks', 'finely trimmed and diced', 1, 'Celery'),
(1, 7, 'Lean minced beef (5-10% fat)', 750.00, '750g', 'g', 'room temperature', 0, NULL),
(1, 8, 'Chopped plum tomatoes', 800.00, '2 x 400g tins', 'g', 'tinned in rich juice', 0, NULL),
(1, 9, 'Sun-dried tomato purée', 2.00, '2 tbsp', 'tbsp', 'concentrated paste', 0, NULL),
(1, 10, 'Beef stock', 250.00, '250ml', 'ml', 'hot, freshly dissolved', 0, NULL),
(1, 11, 'Fresh basil & dried oregano', 15.00, '15g', 'g', 'leaves torn', 0, NULL),
(1, 12, 'Dried spaghetti pasta', 500.00, '500g', 'g', 'bronze-die durum wheat', 1, 'Gluten'),
(1, 13, 'Parmesan cheese', 50.00, '50g', 'g', 'freshly grated to serve', 1, 'Dairy'),

-- Recipe 2: Vegan Pancakes
(2, 1, 'Self-raising flour', 250.00, '250g', 'g', 'sifted', 1, 'Gluten'),
(2, 2, 'Baking powder', 1.50, '1.5 tsp', 'tsp', 'level', 0, NULL),
(2, 3, 'Golden caster sugar', 2.00, '2 tbsp', 'tbsp', 'fine grain', 0, NULL),
(2, 4, 'Unsweetened soya or oat milk', 300.00, '300ml', 'ml', 'room temperature', 1, 'Soya/Oats'),
(2, 5, 'Vanilla extract', 1.00, '1 tsp', 'tsp', 'pure extract', 0, NULL),
(2, 6, 'Sunflower or vegetable oil', 2.00, '2 tbsp', 'tbsp', 'plus extra for brushing pan', 0, NULL),
(2, 7, 'Maple syrup', 60.00, '4 tbsp', 'tbsp', 'to drizzle', 0, NULL),
(2, 8, 'Fresh blueberries and strawberries', 150.00, '150g', 'g', 'washed and halved', 0, NULL),

-- Recipe 3: Healthy Pizza
(3, 1, 'Wholemeal self-raising flour', 175.00, '175g', 'g', 'plus extra for dusting', 1, 'Gluten'),
(3, 2, 'Mixed seeds (pumpkin, sunflower, flax)', 2.00, '2 tbsp', 'tbsp', 'toasted', 0, NULL),
(3, 3, 'Natural low-fat yogurt', 125.00, '125g', 'g', 'plain', 1, 'Dairy'),
(3, 4, 'Passata with garlic and herbs', 120.00, '120ml', 'ml', 'thick tomato sauce', 0, NULL),
(3, 5, 'Courgette', 1.00, '1 small', 'whole', 'shaved into thin ribbons', 0, NULL),
(3, 6, 'Red pepper', 1.00, '1', 'whole', 'deseeded and thinly sliced', 0, NULL),
(3, 7, 'Reduced-fat mozzarella', 100.00, '100g', 'g', 'drained and torn into pieces', 1, 'Dairy'),
(3, 8, 'Fresh rocket leaves', 40.00, '40g', 'g', 'washed, to scatter after baking', 0, NULL),

-- Recipe 4: Easy Lamb Biryani
(4, 1, 'Boneless lamb shoulder', 600.00, '600g', 'g', 'trimmed and cut into 3cm cubes', 0, NULL),
(4, 2, 'Basmati rice', 300.00, '300g', 'g', 'rinsed until water runs clear', 0, NULL),
(4, 3, 'Large onions', 2.00, '2', 'whole', 'thinly sliced into half-moons', 0, NULL),
(4, 4, 'Fresh root ginger & garlic paste', 2.00, '2 tbsp', 'tbsp', 'finely grated', 0, NULL),
(4, 5, 'Garam masala & ground cumin', 3.00, '3 tsp', 'tsp', 'freshly ground blend', 0, NULL),
(4, 6, 'Ground turmeric & Kashmiri chilli', 1.50, '1.5 tsp', 'tsp', 'mild aromatic heat', 0, NULL),
(4, 7, 'Natural yogurt', 100.00, '100g', 'g', 'for tenderising marinade', 1, 'Dairy'),
(4, 8, 'Hot lamb or vegetable stock', 550.00, '550ml', 'ml', 'infused with a pinch of saffron', 0, NULL),
(4, 9, 'Fresh mint and coriander leaves', 25.00, '25g', 'g', 'chopped, for garnishing', 0, NULL),
(4, 10, 'Toasted flaked almonds', 25.00, '25g', 'g', 'optional garnish', 1, 'Nuts'),

-- Recipe 5: Couscous Salad
(5, 1, 'Dry couscous grains', 225.00, '225g', 'g', 'medium grain', 1, 'Gluten'),
(5, 2, 'Boiling vegetable stock', 300.00, '300ml', 'ml', 'low sodium', 0, NULL),
(5, 3, 'Dried apricots & dried cranberries', 100.00, '100g', 'g', 'finely diced', 0, NULL),
(5, 4, 'Toasted almonds and pistachio nuts', 75.00, '75g', 'g', 'roughly chopped', 1, 'Nuts'),
(5, 5, 'Spring onions', 4.00, '4', 'trimmed', 'finely sliced on the diagonal', 0, NULL),
(5, 6, 'Extra virgin olive oil', 3.00, '3 tbsp', 'tbsp', 'cold pressed', 0, NULL),
(5, 7, 'Fresh lemon zest and juice', 1.00, '1 large', 'lemon', 'zested and juiced', 0, NULL),
(5, 8, 'Ground cinnamon and cumin', 1.00, '1 tsp', 'tsp', 'warm Moroccan spice', 0, NULL),
(5, 9, 'Fresh flat-leaf parsley and mint', 30.00, '30g', 'g', 'finely chopped', 0, NULL),

-- Recipe 6: Plum Clafoutis
(6, 1, 'Ripe red plums', 500.00, '500g', 'g', 'halved and stoned', 0, NULL),
(6, 2, 'Free-range eggs', 4.00, '4 large', 'eggs', 'room temperature', 1, 'Eggs'),
(6, 3, 'Caster sugar', 100.00, '100g', 'g', 'plus 2 tbsp for macerating plums', 0, NULL),
(6, 4, 'Plain flour', 65.00, '65g', 'g', 'sifted', 1, 'Gluten'),
(6, 5, 'Whole milk', 200.00, '200ml', 'ml', 'lightly warmed', 1, 'Dairy'),
(6, 6, 'Double cream', 150.00, '150ml', 'ml', 'for custard richness', 1, 'Dairy'),
(6, 7, 'Vanilla bean paste', 1.50, '1.5 tsp', 'tsp', 'or 1 split vanilla pod', 0, NULL),
(6, 8, 'Unsalted butter', 20.00, '20g', 'g', 'melted, plus extra for greasing dish', 1, 'Dairy'),
(6, 9, 'Icing sugar', 15.00, '1 tbsp', 'tbsp', 'for dusting before serving', 0, NULL),

-- Recipe 7: Mango Pie
(7, 1, 'Plain flour (for pastry)', 300.00, '300g', 'g', 'chilled', 1, 'Gluten'),
(7, 2, 'Cold unsalted butter', 150.00, '150g', 'g', 'cubed', 1, 'Dairy'),
(7, 3, 'Ripe sweet mangoes (Alfonso or Kent)', 3.00, '3 large', 'mangoes', 'peeled, stoned, and sliced', 0, NULL),
(7, 4, 'Light muscovado or golden caster sugar', 75.00, '75g', 'g', 'adjusted to fruit sweetness', 0, NULL),
(7, 5, 'Cornflour', 2.00, '2 tbsp', 'tbsp', 'to thicken mango juices', 0, NULL),
(7, 6, 'Ground green cardamom', 0.75, '0.75 tsp', 'tsp', 'freshly crushed seeds', 0, NULL),
(7, 7, 'Fresh lime zest and juice', 1.00, '1', 'lime', 'bright acidity', 0, NULL),
(7, 8, 'Milk and demerara sugar', 2.00, '2 tbsp', 'tbsp', 'for pastry glaze', 1, 'Dairy'),

-- Recipe 8: Mushroom Doner
(8, 1, 'Large portobello & oyster mushrooms', 500.00, '500g', 'g', 'wiped clean and thinly sliced', 0, NULL),
(8, 2, 'Smoked paprika, cumin, and ground coriander', 3.00, '3 tsp', 'tsp', 'shawarma spice mix', 0, NULL),
(8, 3, 'Dried oregano & garlic granules', 2.00, '2 tsp', 'tsp', 'aromatic rub', 0, NULL),
(8, 4, 'Olive oil & red wine vinegar', 3.00, '3 tbsp', 'tbsp', 'for marinade', 0, NULL),
(8, 5, 'Wholemeal pitta or flatbreads', 4.00, '4', 'flatbreads', 'warmed', 1, 'Gluten'),
(8, 6, 'Red cabbage and carrot', 200.00, '200g', 'g', 'finely shredded with lemon juice', 0, NULL),
(8, 7, 'Tahini and dairy-free yogurt sauce', 100.00, '100ml', 'ml', 'whisked with 1 crushed garlic clove', 1, 'Sesame'),
(8, 8, 'Pickled chillies and fresh tomato slices', 80.00, '80g', 'g', 'to assemble wraps', 0, NULL),

-- Recipe 9: Chicken Tikka Masala
(9, 1, 'Skinless chicken breasts', 600.00, '600g', 'g', 'cut into bite-sized chunks', 0, NULL),
(9, 2, 'Natural Greek yogurt', 150.00, '150g', 'g', 'for spiced tikka marinade', 1, 'Dairy'),
(9, 3, 'Garlic cloves & fresh ginger', 30.00, '30g', 'g', 'finely grated into a paste', 0, NULL),
(9, 4, 'Garam masala, cumin & ground coriander', 3.00, '3 tsp', 'tsp', 'toasted and ground', 0, NULL),
(9, 5, 'Kashmiri paprika & turmeric', 2.00, '2 tsp', 'tsp', 'for vibrant colour', 0, NULL),
(9, 6, 'Passata or crushed plum tomatoes', 400.00, '400g', 'g', 'rich tomato base', 0, NULL),
(9, 7, 'Double or single cream', 100.00, '100ml', 'ml', 'stirred in at the end', 1, 'Dairy'),
(9, 8, 'Fresh coriander leaves', 20.00, '20g', 'g', 'chopped to garnish', 0, NULL),

-- Recipe 10: Salmon en Croûte
(10, 1, 'Skinless salmon fillets', 600.00, '4 x 150g', 'g', 'pin-boned prime fillets', 1, 'Fish'),
(10, 2, 'All-butter puff pastry sheet', 375.00, '375g', 'g', 'ready-rolled, chilled', 1, 'Gluten'),
(10, 3, 'Baby spinach & watercress', 200.00, '200g', 'g', 'wilted and squeezed dry', 0, NULL),
(10, 4, 'Full-fat cream cheese', 120.00, '120g', 'g', 'softened with lemon zest', 1, 'Dairy'),
(10, 5, 'Fresh dill and tarragon', 15.00, '15g', 'g', 'finely chopped', 0, NULL),
(10, 6, 'Free-range egg', 1.00, '1', 'egg', 'beaten for golden pastry glaze', 1, 'Eggs'),

-- Recipe 11: Roasted Butternut Squash Risotto
(11, 1, 'Butternut squash', 600.00, '600g', 'g', 'peeled, deseeded and diced into 2cm cubes', 0, NULL),
(11, 2, 'Arborio risotto rice', 300.00, '300g', 'g', 'high-starch Italian grain', 0, NULL),
(11, 3, 'Hot vegetable stock', 1100.00, '1.1 litres', 'ml', 'kept simmering in a saucepan', 1, 'Celery'),
(11, 4, 'Shallots & garlic', 2.00, '2', 'shallots', 'finely diced', 0, NULL),
(11, 5, 'Fresh sage leaves', 15.00, '15', 'leaves', 'half finely chopped, half crisped in butter', 0, NULL),
(11, 6, 'Unsalted butter & Parmesan cheese', 75.00, '75g', 'g', 'to finish (mantecatura)', 1, 'Dairy'),

-- Recipe 12: Sticky Toffee Pudding
(12, 1, 'Pitted Medjool dates', 200.00, '200g', 'g', 'finely chopped', 0, NULL),
(12, 2, 'Boiling black tea or water', 250.00, '250ml', 'ml', 'with 1 tsp bicarbonate of soda', 0, NULL),
(12, 3, 'Unsalted butter', 175.00, '175g', 'g', '85g for sponge, 90g for toffee sauce', 1, 'Dairy'),
(12, 4, 'Dark muscovado sugar', 280.00, '280g', 'g', '140g for sponge, 140g for toffee sauce', 0, NULL),
(12, 5, 'Self-raising flour', 175.00, '175g', 'g', 'sifted with 1 tsp mixed spice', 1, 'Gluten'),
(12, 6, 'Free-range eggs', 2.00, '2 large', 'eggs', 'beaten', 1, 'Eggs'),
(12, 7, 'Double cream', 200.00, '200ml', 'ml', 'for the glossy butterscotch sauce', 1, 'Dairy');

-- 6. Seed Recipe Steps (Sequential Steps with Time per Step in Minutes)
INSERT INTO `recipe_steps` (`recipe_id`, `step_number`, `stage_title`, `instruction`, `duration_minutes`, `tip_or_temp`) VALUES
-- Recipe 1: Spaghetti Bolognese
(1, 1, 'Prepare the Soffritto Vegetables', 'Finely chop the onions, garlic, carrots, and celery into even 4mm dice so they melt evenly into the sauce base.', 15, 'Knife skill: uniform brunoise cut'),
(1, 2, 'Sauté Pancetta & Aromatics', 'Heat the olive oil in a large heavy-based casserole pan over medium heat. Fry the pancetta for 4 minutes until golden, then add the onions, garlic, carrots, and celery and cook gently until softened.', 12, 'Medium heat — do not burn the garlic'),
(1, 3, 'Brown the Minced Beef', 'Increase the heat to medium-high, add the lean minced beef, and cook while breaking up lumps with two wooden spoons until deeply browned all over.', 10, 'High heat for Maillard browning'),
(1, 4, 'Add Tomatoes, Stock & Herbs', 'Stir in the sun-dried tomato purée, tinned plum tomatoes (crushing them with your spoon), hot beef stock, dried oregano, and half the torn basil. Bring to a gentle bubble.', 8, 'Scrape the caramelized fond from pan base'),
(1, 5, 'Slow Simmer the Ragù', 'Reduce heat to low, cover with a slightly ajar lid, and simmer gently, stirring every 15 minutes until rich, glossy, and thickened.', 55, 'Low gentle simmer (85°C–90°C)'),
(1, 6, 'Cook Spaghetti & Toss Together', 'Boil the spaghetti in generously salted water until al dente, drain reserving 50ml pasta water, and toss with the Bolognese sauce, fresh basil, and grated Parmesan.', 15, 'Serve immediately in warm bowls'),

-- Recipe 2: Vegan Pancakes
(2, 1, 'Whisk Dry Ingredients', 'Sift the self-raising flour, baking powder, golden caster sugar, and a pinch of sea salt into a large mixing bowl and whisk to aerate.', 4, 'Sifting prevents lumps without over-mixing'),
(2, 2, 'Emulsify Wet Batter & Rest', 'Pour in the plant milk, vanilla extract, and 2 tablespoons of sunflower oil. Whisk briefly until just smooth, then let the batter rest on the counter.', 6, '5 minutes rest activates baking powder'),
(2, 3, 'Cook First Batch of Pancakes', 'Heat a non-stick frying pan over medium heat and brush lightly with oil. Ladle 2 tablespoons of batter per pancake and cook until bubbles rise to the surface, then flip until golden on both sides.', 8, 'Medium heat — flip once bubbles pop'),
(2, 4, 'Repeat & Stack with Berries', 'Cook the remaining batter in batches, keeping cooked pancakes warm under a clean cloth, then serve stacked high with fresh berries and warm maple syrup.', 7, 'Serve warm'),

-- Recipe 3: Healthy Pizza
(3, 1, 'Preheat Oven & Prepare Seeded Dough', 'Preheat the oven to 220°C (200°C fan) with a large baking sheet inside. Mix the wholemeal self-raising flour, toasted seeds, and natural yogurt in a bowl until it forms a soft dough.', 8, 'Oven at 220°C / 200°C Fan / Gas 7'),
(3, 2, 'Roll Base & Prepare Vegetable Ribbons', 'Knead the dough lightly on a floured surface for 1 minute, roll into a thin 28cm oval, and use a vegetable peeler to shave the courgette into thin ribbons.', 7, 'Roll to 4mm thickness for crispness'),
(3, 3, 'Assemble Passata, Veg & Mozzarella', 'Transfer the dough to baking parchment, spread evenly with herb passata leaving a 1cm border, and scatter over the sliced red pepper, courgette ribbons, and torn mozzarella.', 5, 'Slide parchment onto the hot baking sheet'),
(3, 4, 'Bake Until Crisp & Top with Rocket', 'Bake in the hot oven until the wholemeal crust is crisp and the cheese is bubbling and golden. Scatter with fresh peppery rocket leaves right before slicing.', 15, '15 minutes at 220°C'),

-- Recipe 4: Easy Lamb Biryani
(4, 1, 'Marinate the Lamb & Rinse Rice', 'Toss the cubed lamb shoulder with yogurt, grated ginger, garlic, garam masala, cumin, and turmeric. Rinse the basmati rice in cold water until clear and soak.', 15, 'Room temperature marinade absorbs faster'),
(4, 2, 'Caramelise the Sliced Onions', 'Heat oil in a heavy lidded casserole over medium heat and fry the thinly sliced onions until deep golden brown. Remove half the crispy onions for garnishing.', 15, 'Golden onions give signature biryani sweetness'),
(4, 3, 'Sear & Braise the Spiced Lamb', 'Add the marinated lamb to the remaining onions in the pot, sear for 5 minutes, then pour in 200ml water, cover, and simmer gently until the lamb is tender.', 25, 'Low-medium simmer'),
(4, 4, 'Layer Basmati Rice & Steam (Dum)', 'Drain the soaked basmati rice and scatter evenly over the lamb without stirring. Pour over the hot saffron stock, cover tightly with a foil-lined lid, and cook on very low heat until rice is fluffy.', 20, 'Keep lid sealed to trap aromatic steam'),

-- Recipe 5: Couscous Salad
(5, 1, 'Infuse & Steam the Couscous', 'Place the dry couscous, ground cinnamon, cumin, and diced dried apricots into a heatproof bowl. Pour over the boiling vegetable stock, cover tightly, and leave to absorb.', 10, 'Steam covered off the heat'),
(5, 2, 'Toast the Almonds & Pistachios', 'Meanwhile, place the roughly chopped almonds and pistachio nuts into a dry frying pan over medium heat and toast, tossing frequently until fragrant and lightly golden.', 5, 'Watch closely so nuts do not scorch'),
(5, 3, 'Whisk Citrus Dressing & Chop Herbs', 'Finely slice the spring onions, chop the fresh flat-leaf parsley and mint, and whisk together the extra virgin olive oil, lemon zest, and fresh lemon juice.', 5, 'Season dressing with black pepper'),
(5, 4, 'Fluff Grains & Toss Jewel Salad', 'Fluff the warm couscous thoroughly with a fork, then fold through the citrus dressing, toasted nuts, dried cranberries, spring onions, and fresh herbs.', 5, 'Serve warm or at room temperature'),

-- Recipe 6: Plum Clafoutis
(6, 1, 'Butter Dish & Macerate Plums', 'Preheat oven to 190°C (170°C fan). Generously butter a 24cm ceramic baking dish, arrange the halved plums cut-side up across the base, and sprinkle with 2 tablespoons of sugar.', 8, 'Oven at 190°C / 170°C Fan / Gas 5'),
(6, 2, 'Whisk Eggs, Sugar & Vanilla', 'In a mixing bowl, whisk the eggs, remaining caster sugar, and vanilla bean paste with an electric or hand whisk until pale, frothy, and slightly thickened.', 4, 'Whisk for 3–4 minutes for airy structure'),
(6, 3, 'Fold Flour, Milk, Cream & Butter', 'Sift in the plain flour and whisk smoothly, then gradually pour in the warm milk, double cream, and melted butter until a silky, lump-free custard batter forms.', 5, 'Batter should resemble single cream'),
(6, 4, 'Bake Until Golden & Puffed', 'Pour the batter gently over the plums and bake in the centre of the oven until puffed around the edges, golden brown on top, and just set in the middle.', 38, 'Cool for 10 mins and dust with icing sugar'),

-- Recipe 7: Mango Pie
(7, 1, 'Make & Chill Shortcrust Pastry', 'Rub the cold cubed butter into the plain flour with a pinch of salt until it resembles fine breadcrumbs. Stir in 3–4 tablespoons of ice-cold water to form a dough, wrap, and chill.', 20, 'Cold butter ensures flaky pastry'),
(7, 2, 'Prepare Spiced Mango Filling', 'Peel and slice the ripe mangoes into 1.5cm wedges. Toss in a bowl with muscovado sugar, cornflour, freshly crushed green cardamom, lime zest, and lime juice.', 10, 'Cornflour thickens the tropical juices'),
(7, 3, 'Line Pie Dish & Weave Lattice', 'Roll out two-thirds of the pastry to line a 22cm pie dish, spoon in the spiced mango filling, and use the remaining pastry strips to weave a decorative lattice top. Brush with milk and demerara sugar.', 10, 'Crimp edges firmly to seal'),
(7, 4, 'Bake Until Crisp & Bubbling', 'Bake at 200°C (180°C fan) until the pastry crust is crisp and golden brown and the mango filling is bubbling through the lattice.', 35, 'Rest 15 minutes before slicing'),

-- Recipe 8: Mushroom Doner
(8, 1, 'Slice & Marinate the Mushrooms', 'Thinly slice the portobello and oyster mushrooms into strips. Toss thoroughly in a large bowl with smoked paprika, cumin, coriander, oregano, garlic granules, olive oil, and red wine vinegar.', 10, 'Slice 5mm thin for crispy kebab edges'),
(8, 2, 'Roast at High Heat', 'Spread the marinated mushrooms in a single layer across two preheated baking trays and roast at 220°C (200°C fan), tossing halfway through until charred and crisp at the edges.', 18, 'High heat caramelises the spices'),
(8, 3, 'Prepare Pickled Slaw & Garlic Tahini', 'While the mushrooms roast, toss the finely shredded red cabbage and carrot with lemon juice and salt, and whisk the tahini, dairy-free yogurt, crushed garlic, and a splash of warm water.', 4, 'Massage cabbage for 1 minute to soften'),
(8, 4, 'Warm Flatbreads & Assemble Kebabs', 'Warm the wholemeal flatbreads in the oven for 2 minutes, slather with garlic tahini sauce, pile high with smoky mushroom doner shavings, crunchy slaw, and pickled chillies.', 3, 'Serve immediately while piping hot'),

-- Recipe 9: Chicken Tikka Masala
(9, 1, 'Marinate the Chicken Tikka', 'Combine the yogurt, half the grated garlic and ginger, 1 tsp garam masala, and lemon juice. Toss the chicken chunks to coat and marinate.', 15, 'Even 15 mins tenderises the chicken'),
(9, 2, 'Chargrill the Chicken Pieces', 'Sear the marinated chicken pieces in a smoking-hot griddle pan or under a hot grill until lightly charred at the edges.', 10, 'High heat locks in juices'),
(9, 3, 'Simmer Spiced Tomato Masala Sauce', 'Sauté onions, remaining ginger, garlic, cumin, coriander, and paprika in a deep pan, then pour in the passata and simmer until thick and fragrant.', 15, 'Low bubble so spices do not burn'),
(9, 4, 'Fold in Chicken, Cream & Coriander', 'Stir the charred chicken and cream into the masala sauce, simmer gently for 10 minutes until cooked through, and scatter with fresh coriander.', 10, 'Serve with warm basmati rice or naan'),

-- Recipe 10: Salmon en Croûte
(10, 1, 'Wilt Spinach & Mix Herb Cream Cheese', 'Wilt the spinach and watercress in a colander over boiling water, squeeze out all excess moisture, and fold into the cream cheese with lemon zest and chopped dill.', 10, 'Squeezing dry prevents soggy pastry'),
(10, 2, 'Assemble & Wrap Puff Pastry Parcels', 'Unroll the chilled puff pastry, place the seasoned salmon fillets in the centre, top with the creamy herb spinach, fold the pastry over to seal, and brush with beaten egg.', 10, 'Score diagonal lattice lines on top'),
(10, 3, 'Bake Until Crisp & Golden Brown', 'Bake at 200°C (180°C fan) until the puff pastry is risen and deep golden brown and the salmon inside is cooked and flaky.', 25, '200°C / 180°C Fan'),

-- Recipe 11: Roasted Butternut Squash Risotto
(11, 1, 'Roast the Butternut Squash Cubes', 'Toss the diced butternut squash with olive oil, salt, pepper, and chopped sage, then roast at 200°C until caramelised and tender.', 20, '200°C / 180°C Fan'),
(11, 2, 'Sweat Shallots & Toast Arborio Rice', 'Soften the diced shallots and garlic in olive oil over medium-low heat, stir in the Arborio rice, and toast for 2 minutes until the edges turn translucent.', 8, 'Toasting coats each grain'),
(11, 3, 'Ladle Simmering Stock Gradually', 'Add the hot vegetable stock one ladleful at a time, stirring continuously and allowing each addition to absorb before adding the next until the rice is creamy and al dente.', 18, 'Keep stock hot so cooking never stalls'),
(11, 4, 'Fold in Roasted Squash, Butter & Parmesan', 'Remove from the heat, gently fold through the roasted squash, cold butter, and grated Parmesan, and top with crispy fried sage leaves.', 4, 'Rest covered for 2 mins before serving'),

-- Recipe 12: Sticky Toffee Pudding
(12, 1, 'Soak Medjool Dates in Boiling Tea', 'Place the finely chopped Medjool dates and bicarbonate of soda into a bowl, pour over the boiling black tea, and leave to soften into a rich purée.', 10, 'Bicarbonate softens the date skins'),
(12, 2, 'Cream Butter, Muscovado Sugar & Eggs', 'Beat the softened butter and dark muscovado sugar until light and fluffy, beat in the eggs one at a time, then fold in the sifted flour and soaked date mixture.', 10, 'Do not over-beat after adding flour'),
(12, 3, 'Bake the Date Sponge Pudding', 'Pour into a buttered 20cm square baking tin and bake at 180°C (160°C fan) until risen, springy to the touch, and a skewer comes out clean.', 28, '180°C / 160°C Fan'),
(12, 4, 'Simmer Glossy Butterscotch Toffee Sauce', 'Melt the remaining butter, dark muscovado sugar, and double cream in a saucepan and bubble for 5 minutes until glossy, then pour warm over the pricked sponge.', 7, 'Serve warm with vanilla custard or ice cream');

-- 7. Seed Multi-Dimensional Recipe Ratings & Reviews (24 Reviews across all 12 recipes)
INSERT INTO `recipe_ratings` (`recipe_id`, `user_id`, `taste_rating`, `aesthetics_rating`, `difficulty_rating`, `overall_rating`, `review_title`, `review_comment`, `created_at`) VALUES
(1, 4, 5, 5, 5, 5.00, 'Authentic Italian comfort food!', 'Followed the step timers for browning the beef and simmering for 55 minutes—the sun-dried tomato paste makes the ragù unbelievably rich.', '2026-10-20 18:30:00'),
(1, 1, 5, 4, 5, 4.67, 'Family favourite every week', 'Used halal lean beef mince and scaled the servings to 8 using the portion calculator. Every step was super clear.', '2026-10-22 19:15:00'),
(2, 3, 5, 5, 5, 5.00, 'Fluffiest plant-based pancakes ever!', 'Resting the batter for 6 minutes worked like magic. You cannot tell these are dairy-free and egg-free!', '2026-10-21 09:20:00'),
(2, 2, 5, 5, 5, 5.00, 'Gorgeous golden stack for Sunday brunch', 'Topped with fresh strawberries and blueberries—looks like a café dish and takes only 25 minutes.', '2026-10-23 10:05:00'),
(3, 1, 5, 5, 5, 5.00, 'Crispy base in 35 minutes with no yeast!', 'Pre-heating the tray made the wholemeal yogurt dough so crispy. Love the courgette ribbons on top.', '2026-10-24 13:40:00'),
(3, 2, 4, 5, 5, 4.67, 'Light, colourful and nutritious', 'Under 450 calories and packed with fibre from the mixed seeds. Great mid-week vegetarian dinner.', '2026-10-25 20:10:00'),
(4, 1, 5, 5, 4, 4.67, 'Fragrant, restaurant-quality lamb biryani', 'The saffron steam method (dum) kept the lamb meltingly tender and every grain of basmati separate.', '2026-10-26 19:50:00'),
(4, 4, 5, 5, 4, 4.67, 'Incredible depth of spice', 'Caramelising the onions properly took 15 minutes well spent. Easily the best homemade curry I have cooked.', '2026-10-27 21:00:00'),
(5, 2, 5, 5, 5, 5.00, 'Like a jewel box on a platter!', 'The dried apricots, cranberries, and green pistachios look stunning together and the lemon dressing is so fresh.', '2026-10-27 12:30:00'),
(5, 3, 4, 5, 5, 4.67, 'Perfect make-ahead work lunch', 'Ready in 25 minutes and tastes even better the next day once the Moroccan spices infuse.', '2026-10-28 13:15:00'),
(6, 2, 5, 5, 4, 4.67, 'Silky French patisserie perfection', 'Baked until just set in the middle—the ruby plum juices mingling with the vanilla custard is heavenly.', '2026-10-28 17:45:00'),
(6, 5, 5, 5, 4, 4.67, 'A timeless Limousin classic', 'Serving it lukewarm with a snowfall of icing sugar showcases seasonal plums at their best.', '2026-10-29 16:00:00'),
(7, 2, 5, 4, 4, 4.33, 'Cardamom and mango is a dream pairing', 'The lattice crust took a little patience, but the spiced mango filling was worth every minute.', '2026-10-29 18:20:00'),
(7, 4, 4, 5, 3, 4.00, 'Showstopping weekend bake', 'Crisp buttery pastry and fragrant tropical filling. Great served with a scoop of vanilla ice cream.', '2026-10-30 15:10:00'),
(8, 3, 5, 5, 5, 5.00, 'Mind-blowing vegan street food!', 'Roasting the thinly shaved oyster mushrooms on two trays made them genuinely crispy like shawarma.', '2026-10-30 20:45:00'),
(8, 1, 5, 4, 5, 4.67, 'Healthy fakeaway winner', 'So much flavour from the smoked paprika and garlic tahini sauce, and ready in 35 minutes.', '2026-10-31 19:00:00'),
(9, 1, 5, 5, 5, 5.00, 'Better than our local curry house!', 'Toasting the cumin and coriander before simmering the passata gave such a velvety, smoky depth.', '2026-10-31 20:10:00'),
(9, 4, 5, 5, 4, 4.67, 'Rich, smoky and super tender chicken', 'The yogurt marinade kept the chicken breast pieces super juicy under the grill.', '2026-10-31 20:40:00'),
(10, 5, 5, 5, 4, 4.67, 'Dinner party showstopper', 'Squeezing the wilted spinach completely dry kept the puff pastry base crisp from edge to edge.', '2026-10-31 21:00:00'),
(10, 1, 5, 5, 4, 4.67, 'Looks and tastes like a 5-star restaurant dish', 'The lemon dill cream cheese pairs beautifully with the flaky pastry and Scottish salmon.', '2026-11-01 10:15:00'),
(11, 2, 5, 5, 4, 4.67, 'Velvety risotto with sweet caramelised squash', 'Crispy sage leaves on top made the presentation look gorgeous. Step timers were spot on.', '2026-11-01 11:00:00'),
(11, 3, 5, 4, 4, 4.33, 'So comforting for autumn evenings', 'Used dairy-free butter and nutritional yeast for a plant-based version and it turned out super creamy.', '2026-11-01 11:30:00'),
(12, 4, 5, 5, 5, 5.00, 'The ultimate British pudding!', 'Soaking the Medjool dates in hot tea made the sponge unbelievably light and sticky.', '2026-11-01 12:00:00'),
(12, 2, 5, 5, 5, 5.00, 'Glossy dark muscovado butterscotch heaven', 'Pricking the warm sponge so the toffee sauce soaks right in is the best tip ever.', '2026-11-01 12:20:00');

-- 8. Seed User Favourites
INSERT INTO `user_favourites` (`user_id`, `recipe_id`, `personal_note`, `saved_at`) VALUES
(1, 4, 'Serve with cucumber raita for Friday family dinner', '2026-10-26 20:00:00'),
(1, 3, 'Quick post-study healthy dinner', '2026-10-24 14:00:00'),
(1, 8, 'Kids loved the crispy spiced mushrooms!', '2026-10-31 19:30:00'),
(1, 9, 'Make with homemade garlic naan', '2026-10-31 20:30:00'),
(1, 10, 'Special occasion seafood centrepiece', '2026-11-01 10:30:00'),
(2, 6, 'Use Victoria plums when in season', '2026-10-28 18:00:00'),
(2, 5, 'Bring to summer picnic', '2026-10-27 13:00:00'),
(2, 11, 'Extra crispy sage leaves on top', '2026-11-01 11:15:00'),
(2, 12, 'Bake for Sunday family lunch', '2026-11-01 12:30:00'),
(3, 8, 'Extra pickled chillies and tahini', '2026-10-30 21:00:00'),
(3, 2, 'Add chia seeds to the batter', '2026-10-21 10:00:00'),
(3, 5, 'Great high-energy lunch', '2026-10-28 14:00:00'),
(4, 1, 'Add a splash of red wine to the ragù', '2026-10-20 19:00:00'),
(4, 4, 'Toast extra almonds for crunch', '2026-10-27 21:15:00'),
(4, 12, 'Serve with clotted cream ice cream', '2026-11-01 12:15:00');

SET FOREIGN_KEY_CHECKS = 1;
