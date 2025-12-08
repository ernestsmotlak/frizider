# Database Schema Documentation

## 1. Overview

The Food & Recipe Tracker database is designed to help users manage their pantry inventory, track recipes, plan grocery shopping, and monitor food waste. The application is currently designed as a single-user system but is architected to support multi-user functionality in the future, with all user-owned resources properly scoped through foreign key relationships.

The schema supports:
- **Storage Management**: Organize pantry items across multiple storage spaces (e.g., fridge, freezer, pantry)
- **Recipe Management**: Store recipes with ingredients and tag-based organization
- **Grocery Planning**: Create and manage shopping lists that can be converted to pantry items
- **Waste Tracking**: Log food waste to identify patterns and reduce waste
- **Tagging System**: Flexible tagging across recipes, pantry items, and grocery lists

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

### Tags (Optional but Included)

#### `tags`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `user_id` | BIGINT UNSIGNED | NO | - | Foreign key to `users.id` |
| `name` | VARCHAR(255) | NO | - | Tag name (e.g., "vegetarian", "quick-meal") |
| `color` | VARCHAR(7) | YES | NULL | Hex color code for UI display |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:**
- Primary key: `id`
- Foreign key: `user_id` → `users.id` (ON DELETE CASCADE)
- Unique: `(user_id, name)` (tags are unique per user)

#### `recipe_tag`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `recipe_id` | BIGINT UNSIGNED | NO | - | Foreign key to `recipes.id` |
| `tag_id` | BIGINT UNSIGNED | NO | - | Foreign key to `tags.id` |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |

**Indexes:**
- Primary key: `(recipe_id, tag_id)` (composite)
- Foreign key: `recipe_id` → `recipes.id` (ON DELETE CASCADE)
- Foreign key: `tag_id` → `tags.id` (ON DELETE CASCADE)

#### `pantry_item_tag`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `pantry_item_id` | BIGINT UNSIGNED | NO | - | Foreign key to `pantry_items.id` |
| `tag_id` | BIGINT UNSIGNED | NO | - | Foreign key to `tags.id` |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |

**Indexes:**
- Primary key: `(pantry_item_id, tag_id)` (composite)
- Foreign key: `pantry_item_id` → `pantry_items.id` (ON DELETE CASCADE)
- Foreign key: `tag_id` → `tags.id` (ON DELETE CASCADE)

#### `grocery_list_tag`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `grocery_list_id` | BIGINT UNSIGNED | NO | - | Foreign key to `grocery_lists.id` |
| `tag_id` | BIGINT UNSIGNED | NO | - | Foreign key to `tags.id` |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |

**Indexes:**
- Primary key: `(grocery_list_id, tag_id)` (composite)
- Foreign key: `grocery_list_id` → `grocery_lists.id` (ON DELETE CASCADE)
- Foreign key: `tag_id` → `tags.id` (ON DELETE CASCADE)

---

### Waste Tracking

#### `waste_logs`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `pantry_item_id` | BIGINT UNSIGNED | NO | - | Foreign key to `pantry_items.id` |
| `quantity` | DECIMAL(10,2) | YES | NULL | Quantity wasted |
| `unit` | VARCHAR(50) | YES | NULL | Unit of measurement |
| `reason` | VARCHAR(255) | YES | NULL | Reason for waste (e.g., "expired", "spoiled") |
| `notes` | TEXT | YES | NULL | Additional notes |
| `wasted_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | When the waste occurred |
| `created_at` | TIMESTAMP | YES | NULL | Record creation timestamp |
| `updated_at` | TIMESTAMP | YES | NULL | Record update timestamp |

**Indexes:**
- Primary key: `id`
- Foreign key: `pantry_item_id` → `pantry_items.id` (ON DELETE CASCADE)
- Index: `wasted_at` (for waste tracking queries)

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

- **User → Tags (1:N)**
  - One user can have multiple tags
  - Foreign key: `tags.user_id` → `users.id`

- **Space → PantryItems (1:N)**
  - One space can contain multiple pantry items
  - Foreign key: `pantry_items.space_id` → `spaces.id` (nullable)

- **Recipe → RecipeIngredients (1:N)**
  - One recipe can have multiple ingredients
  - Foreign key: `recipe_ingredients.recipe_id` → `recipes.id`

- **GroceryList → GroceryListItems (1:N)**
  - One grocery list can have multiple items
  - Foreign key: `grocery_list_items.grocery_list_id` → `grocery_lists.id`

- **PantryItems → WasteLogs (1:N)**
  - One pantry item can have multiple waste log entries
  - Foreign key: `waste_logs.pantry_item_id` → `pantry_items.id`

### Many-to-Many Relationships

- **Recipe ↔ Tags (N:M)**
  - Recipes can have multiple tags, tags can be applied to multiple recipes
  - Junction table: `recipe_tag`

- **PantryItems ↔ Tags (N:M)**
  - Pantry items can have multiple tags, tags can be applied to multiple items
  - Junction table: `pantry_item_tag`

- **GroceryLists ↔ Tags (N:M)**
  - Grocery lists can have multiple tags, tags can be applied to multiple lists
  - Junction table: `grocery_list_tag`

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

- **`tags.user_id` → `users.id`**
  - **Reason**: User-owned tags should be removed when the user is deleted.

- **`recipe_ingredients.recipe_id` → `recipes.id`**
  - **Reason**: Ingredients are meaningless without their parent recipe. If a recipe is deleted, its ingredients should be removed.

- **`grocery_list_items.grocery_list_id` → `grocery_lists.id`**
  - **Reason**: List items are meaningless without their parent list. If a grocery list is deleted, its items should be removed.

- **`waste_logs.pantry_item_id` → `pantry_items.id`**
  - **Reason**: Waste logs are tied to specific pantry items. If a pantry item is deleted, its waste logs should be removed.

- **`recipe_tag.recipe_id` → `recipes.id`** and **`recipe_tag.tag_id` → `tags.id`**
  - **Reason**: Junction table records should be removed when either the recipe or tag is deleted.

- **`pantry_item_tag.pantry_item_id` → `pantry_items.id`** and **`pantry_item_tag.tag_id` → `tags.id`**
  - **Reason**: Junction table records should be removed when either the pantry item or tag is deleted.

- **`grocery_list_tag.grocery_list_id` → `grocery_lists.id`** and **`grocery_list_tag.tag_id` → `tags.id`**
  - **Reason**: Junction table records should be removed when either the grocery list or tag is deleted.

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
 │           └── WasteLogs (1:N)
 │
 ├── PantryItems (1:N)
 │     ├── Spaces (N:1, optional)
 │     └── Tags (N:M via pantry_item_tag)
 │
 ├── Recipes (1:N)
 │     ├── RecipeIngredients (1:N)
 │     └── Tags (N:M via recipe_tag)
 │
 ├── GroceryLists (1:N)
 │     ├── GroceryListItems (1:N)
 │     │     └── PantryItems (1:1, optional, after purchase)
 │     └── Tags (N:M via grocery_list_tag)
 │
 └── Tags (1:N)
       ├── Recipes (N:M via recipe_tag)
       ├── PantryItems (N:M via pantry_item_tag)
       └── GroceryLists (N:M via grocery_list_tag)
```

### Relationship Summary

- **Direct ownership**: User owns Spaces, PantryItems, Recipes, GroceryLists, and Tags
- **Hierarchical organization**: Spaces contain PantryItems
- **Composition**: Recipes contain RecipeIngredients; GroceryLists contain GroceryListItems
- **Tagging system**: Tags can be applied to Recipes, PantryItems, and GroceryLists via junction tables
- **Workflow connection**: GroceryListItems can link to PantryItems when purchased
- **Tracking**: PantryItems can have multiple WasteLogs for waste tracking

---

## 6. Future Expansions

The following features are planned for future implementation:

### Multi-User Collaboration
- **Household/Group Management**: Add `households` or `groups` table to support shared spaces and collaborative meal planning
- **User Roles**: Implement role-based access control (owner, member, viewer) for shared resources
- **Sharing Permissions**: Granular permissions for sharing recipes, grocery lists, and pantry items

### Shared Spaces per Household
- **Household Spaces**: Extend the `spaces` table to support household-level storage locations
- **Multi-User Pantry Access**: Allow multiple users to view and manage shared pantry inventory
- **Activity Logging**: Track who added, modified, or consumed pantry items

### AI Receipt Scanning
- **Receipts Table**: Store scanned receipt images and extracted data
- **OCR Integration**: Automatic extraction of items, prices, and dates from receipts
- **Auto-Populate Pantry**: Automatically create pantry items from scanned receipts
- **Price Tracking**: Historical price data for budget analysis

### Grocery Store Integrations
- **Store Profiles**: Store information about different grocery stores
- **Store-Specific Lists**: Organize grocery lists by store location
- **Price Comparison**: Compare prices across different stores
- **Store APIs**: Integration with store APIs for real-time inventory and pricing

### Additional Features
- **Meal Planning**: Weekly/monthly meal planning with recipe scheduling
- **Nutrition Tracking**: Nutritional information per recipe and meal
- **Shopping History**: Historical shopping patterns and frequency analysis
- **Expiry Alerts**: Automated notifications for items approaching expiration
- **Recipe Scaling**: Automatically adjust ingredient quantities based on serving size
- **Inventory Analytics**: Reports on consumption patterns, waste reduction, and spending trends
- **Barcode Scanning**: Quick item lookup and addition via barcode scanning
- **Unit Conversion**: Automatic unit conversion for recipes and shopping lists

---

## Notes

- All tables include `created_at` and `updated_at` timestamps for audit trails
- User-owned tables include `deleted_at` for soft deletes, allowing data recovery
- Junction tables (`recipe_tag`, `pantry_item_tag`, `grocery_list_tag`) use composite primary keys for efficiency
- Quantity and unit fields are nullable to support flexible data entry
- The schema is designed to be extensible for future multi-user features while maintaining data integrity



