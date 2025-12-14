# Database Schema Documentation

## 1. Overview

The Food & Recipe Tracker database is designed to help users manage their pantry inventory, track recipes, plan grocery shopping, and monitor food waste. The application is currently designed as a single-user system but is architected to support multi-user functionality in the future, with all user-owned resources properly scoped through foreign key relationships.

The schema supports:
- **Storage Management**: Organize pantry items across multiple storage spaces (e.g., fridge, freezer, pantry)
- **Recipe Management**: Store recipes with ingredients
- **Grocery Planning**: Create and manage shopping lists that can be converted to pantry items

---

## 2. Tables + Columns

### Core User Tables

#### `users`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR(255) | NO | - | User's full name |
| `email` | VARCHAR(255) | NO | - | Unique email address |
| `email_verified_at` | TIMESTAMP | YES | NULL | Email verification timestamp |
| `password` | VARCHAR(255) | NO | - | Hashed password |
| `remember_token` | VARCHAR(100) | YES | NULL | Remember me token |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:**
- Primary key: `id`
- Unique: `email`

---

### Storage & Pantry

#### `spaces`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `user_id` | BIGINT UNSIGNED | NO | - | Foreign key to `users.id` |
| `name` | VARCHAR(255) | NO | - | Space name (e.g., "Fridge", "Freezer") |
| `description` | TEXT | YES | NULL | Optional space description |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:**
- Primary key: `id`
- Foreign key: `user_id` → `users.id` (ON DELETE CASCADE)

#### `pantry_items`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `user_id` | BIGINT UNSIGNED | NO | - | Foreign key to `users.id` |
| `space_id` | BIGINT UNSIGNED | YES | NULL | Foreign key to `spaces.id` |
| `name` | VARCHAR(255) | NO | - | Item name |
| `quantity` | DECIMAL(10,2) | YES | NULL | Item quantity |
| `unit` | VARCHAR(50) | YES | NULL | Unit of measurement (e.g., "kg", "pieces") |
| `expiry_date` | DATE | YES | NULL | Expiration date |
| `purchase_date` | DATE | YES | NULL | Date item was purchased |
| `notes` | TEXT | YES | NULL | Additional notes about the item |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:**
- Primary key: `id`
- Foreign key: `user_id` → `users.id` (ON DELETE CASCADE)
- Foreign key: `space_id` → `spaces.id` (ON DELETE SET NULL)
- Index: `expiry_date` (for expiry tracking queries)

---

### Recipes

#### `recipes`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `user_id` | BIGINT UNSIGNED | NO | - | Foreign key to `users.id` |
| `name` | VARCHAR(255) | NO | - | Recipe name |
| `description` | TEXT | YES | NULL | Recipe description |
| `instructions` | TEXT | YES | NULL | Cooking instructions |
| `servings` | INT UNSIGNED | YES | NULL | Number of servings |
| `prep_time` | INT UNSIGNED | YES | NULL | Preparation time in minutes |
| `cook_time` | INT UNSIGNED | YES | NULL | Cooking time in minutes |
| `image_url` | VARCHAR(500) | YES | NULL | URL to recipe image |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:**
- Primary key: `id`
- Foreign key: `user_id` → `users.id` (ON DELETE CASCADE)

#### `recipe_ingredients`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `recipe_id` | BIGINT UNSIGNED | NO | - | Foreign key to `recipes.id` |
| `name` | VARCHAR(255) | NO | - | Ingredient name |
| `quantity` | DECIMAL(10,2) | YES | NULL | Ingredient quantity |
| `unit` | VARCHAR(50) | YES | NULL | Unit of measurement |
| `notes` | VARCHAR(500) | YES | NULL | Additional notes (e.g., "chopped", "optional") |
| `sort_order` | INT UNSIGNED | YES | 0 | Display order in recipe |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |

**Indexes:**
- Primary key: `id`
- Foreign key: `recipe_id` → `recipes.id` (ON DELETE CASCADE)
- Index: `sort_order` (for ordered display)

---

### Grocery Shopping

#### `grocery_lists`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `user_id` | BIGINT UNSIGNED | NO | - | Foreign key to `users.id` |
| `name` | VARCHAR(255) | NO | - | List name |
| `notes` | TEXT | YES | NULL | Additional notes |
| `completed_at` | TIMESTAMP | YES | NULL | When the shopping trip was completed |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:**
- Primary key: `id`
- Foreign key: `user_id` → `users.id` (ON DELETE CASCADE)

#### `grocery_list_items`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `grocery_list_id` | BIGINT UNSIGNED | NO | - | Foreign key to `grocery_lists.id` |
| `pantry_item_id` | BIGINT UNSIGNED | YES | NULL | Foreign key to `pantry_items.id` (set when purchased) |
| `name` | VARCHAR(255) | NO | - | Item name |
| `quantity` | DECIMAL(10,2) | YES | NULL | Desired quantity |
| `unit` | VARCHAR(50) | YES | NULL | Unit of measurement |
| `is_purchased` | BOOLEAN | NO | FALSE | Whether item has been purchased |
| `notes` | VARCHAR(500) | YES | NULL | Additional notes |
| `sort_order` | INT UNSIGNED | YES | 0 | Display order in list |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |

**Indexes:**
- Primary key: `id`
- Foreign key: `grocery_list_id` → `grocery_lists.id` (ON DELETE CASCADE)
- Foreign key: `pantry_item_id` → `pantry_items.id` (ON DELETE SET NULL)
- Index: `sort_order` (for ordered display)

---

## 3. Relationships

### One-to-Many Relationships

- **User → Spaces (1:N)**
  - One user can have multiple storage spaces
  - Foreign key: `spaces.user_id` → `users.id`

- **User → PantryItems (1:N)**
  - One user can have multiple pantry items
  - Foreign key: `pantry_items.user_id` → `users.id`

- **User → Recipes (1:N)**
  - One user can have multiple recipes
  - Foreign key: `recipes.user_id` → `users.id`

- **User → GroceryLists (1:N)**
  - One user can have multiple grocery lists
  - Foreign key: `grocery_lists.user_id` → `users.id`

- **Space → PantryItems (1:N)**
  - One space can contain multiple pantry items
  - Foreign key: `pantry_items.space_id` → `spaces.id` (nullable)

- **Recipe → RecipeIngredients (1:N)**
  - One recipe can have multiple ingredients
  - Foreign key: `recipe_ingredients.recipe_id` → `recipes.id`

- **GroceryList → GroceryListItems (1:N)**
  - One grocery list can have multiple items
  - Foreign key: `grocery_list_items.grocery_list_id` → `grocery_lists.id`

### Special Relationships

- **GroceryListItems → PantryItems (1:1 after purchase)**
  - When a grocery list item is purchased, it can be linked to a pantry item
  - Foreign key: `grocery_list_items.pantry_item_id` → `pantry_items.id` (nullable)
  - This creates a connection between shopping lists and inventory

---

## 4. Cascade Strategy

### ON DELETE CASCADE

The following foreign keys use `ON DELETE CASCADE` to automatically remove dependent records when the parent is deleted:

- **`spaces.user_id` → `users.id`**
  - **Reason**: If a user is deleted, all their storage spaces should be removed. This prevents orphaned spaces.

- **`pantry_items.user_id` → `users.id`**
  - **Reason**: User-owned pantry items should be removed when the user is deleted.

- **`recipes.user_id` → `users.id`**
  - **Reason**: User-owned recipes should be removed when the user is deleted.

- **`grocery_lists.user_id` → `users.id`**
  - **Reason**: User-owned grocery lists should be removed when the user is deleted.

- **`recipe_ingredients.recipe_id` → `recipes.id`**
  - **Reason**: Ingredients are meaningless without their parent recipe. If a recipe is deleted, its ingredients should be removed.

- **`grocery_list_items.grocery_list_id` → `grocery_lists.id`**
  - **Reason**: List items are meaningless without their parent list. If a grocery list is deleted, its items should be removed.

### ON DELETE SET NULL

The following foreign keys use `ON DELETE SET NULL` to preserve records while breaking the relationship:

- **`pantry_items.space_id` → `spaces.id`**
  - **Reason**: If a storage space is deleted, pantry items should remain but become "unassigned" rather than being deleted. This preserves inventory data even if organizational structure changes.

- **`grocery_list_items.pantry_item_id` → `pantry_items.id`**
  - **Reason**: If a pantry item is deleted, the grocery list item should remain (as it may have historical value) but the link should be broken. This preserves shopping history even if inventory items are removed.

---

## 5. ERD (Entity Relationship Diagram)

```
User
 ├── Spaces (1:N)
 │     └── PantryItems (1:N)
 │
 ├── PantryItems (1:N)
 │     └── Spaces (N:1, optional)
 │
 ├── Recipes (1:N)
 │     └── RecipeIngredients (1:N)
 │
 └── GroceryLists (1:N)
       └── GroceryListItems (1:N)
             └── PantryItems (1:1, optional, after purchase)
```

### Relationship Summary

- **Direct ownership**: User owns Spaces, PantryItems, Recipes, and GroceryLists
- **Hierarchical organization**: Spaces contain PantryItems
- **Composition**: Recipes contain RecipeIngredients; GroceryLists contain GroceryListItems
- **Workflow connection**: GroceryListItems can link to PantryItems when purchased

---

## Notes

- All tables include `created_at` and `updated_at` timestamps for audit trails
- User-owned tables include `deleted_at` for soft deletes, allowing data recovery
- Quantity and unit fields are nullable to support flexible data entry
- The schema is designed to be extensible for future multi-user features while maintaining data integrity



