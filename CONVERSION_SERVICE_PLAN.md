# Item Conversion Service — Implementation Prompt

## Context

This Laravel 12 + Vue 3 app (`frizider-api`) has three independent "item" entities that the user wants to be able to convert between, via **one-time copy operations** (snapshots), not persistent foreign-key links. There is intentionally **no shared "ingredient" catalog / no `ingredient_id` linkage** — each conversion just copies a subset of fields into a new row in the target table, and the user fills in any target-specific fields the source doesn't have.

The three entities:

- **`RecipeIngredient`** (`app/Models/RecipeIngredient.php`, table `recipe_ingredients`) — belongs to `Recipe` via `recipe_id`. Fields: `name`, `quantity`, `unit`, `notes`, `sort_order`, `completed`.
- **`PantryItem`** (`app/Models/PantryItem.php`, table `pantry_items`) — belongs to `User` (`user_id`) and optionally `SpaceStorage` (`space_id`). Fields: `name`, `quantity`, `unit`, `expiry_date`, `purchase_date`, `notes`.
- **`GroceryListItem`** (`app/Models/GroceryListItem.php`, table `grocery_list_items`) — belongs to `GroceryList` via `grocery_list_id`. Fields: `name`, `quantity`, `unit`, `notes`, `sort_order`, `is_purchased`, `completed`, and a currently-**unused** nullable `pantry_item_id` FK → `pantry_items` (see cleanup note below).

`ShoppingItem` (table `shopping_items`) is the temporary "go shopping" session working-copy of a `GroceryListItem` (linked via `grocery_list_item_id`, used only inside the shopping-session flow — `saveShoppingSession`/`getShoppingSession`/`finishShoppingSession` in `app/Http/Controllers/GroceryListController.php`). For conversion purposes, treat `ShoppingItem` the same as `GroceryListItem` (same common fields: name, quantity, unit, notes).

## Goal

Build a small **conversion service** with **3 backend endpoints** (one per target type), each accepting one or more source items (of either of the other two/three source types) plus any target-specific required fields, and creating new row(s) in the target table. This is a **copy**, not a link — no FK is created between source and target rows.

## Field-mapping spec (already agreed)

Common fields across all entities: `name`, `quantity`, `unit`, `notes`.

| # | Source(s) | Target | Copied (common fields) | User must supply | Defaulted / not copied |
|---|---|---|---|---|---|
| 1 | `ShoppingItem` / `GroceryListItem` | `PantryItem` | name, quantity, unit, notes | `space_id` (required — which storage unit), `expiry_date` (optional) | `purchase_date` = today; `is_purchased`/`completed`/`sort_order` dropped |
| 2 | `RecipeIngredient` | `PantryItem` | name, quantity, unit, notes | `space_id` (required), `expiry_date` (optional) | `purchase_date` = today; `sort_order`/`completed` dropped |
| 3 | `PantryItem` / `RecipeIngredient` | `GroceryListItem` | name, quantity, unit, notes | `grocery_list_id` (required — target list; pick existing or create) | `is_purchased` = false, `completed` = false, `sort_order` = next available; `expiry_date`/`space_id` dropped |
| 4 | `PantryItem` / `ShoppingItem` / `GroceryListItem` | `RecipeIngredient` | name, quantity, unit, notes | `recipe_id` (required — must already exist) | `completed` = false, `sort_order` = next available; `expiry_date`/`space_id`/`is_purchased` dropped |

Rows 1+2 are one endpoint ("convert to pantry item"), row 3 is one endpoint ("convert to grocery list item"), row 4 is one endpoint ("convert to recipe ingredient"). So: **3 endpoints total**, each parameterized by `source_type` (an enum: `shopping_item` | `grocery_list_item` | `recipe_ingredient` | `pantry_item`) + `source_ids: number[]` (support multi-select/bulk) + target-specific extra fields.

## Important design constraints / things to preserve

- Ownership checks: every source/target row must belong to `auth()->id()` (directly or via parent — e.g. `RecipeIngredient` via `recipe.user_id`, `GroceryListItem` via `groceryList.user_id`, `ShoppingItem` via its session's `user_id`). Reuse the ownership-check patterns already used in `RecipeController`, `PantryItemController`, `GroceryListItemController`.
- Wrap multi-item conversions in `DB::transaction()`.
- `sort_order` at the target is always **recalculated** (append after current max), never copied from source.
- Soft-deleted source rows (`RecipeIngredient`, `PantryItem`, `GroceryListItem` all use `SoftDeletes`) should not be convertible — exclude via normal Eloquent scoping (default behavior already excludes trashed).
- `quantity` is `decimal:2` on all three models — keep as-is when copying.
- `unit` max length differs slightly historically (see `2026_01_23_210000_update_unit_columns_to_max_10_chars.php`) — validate against the **target** table's constraints, not the source's.

## Cleanup item (do alongside, small)

`grocery_list_items.pantry_item_id` is a nullable FK to `pantry_items` that is **currently unused anywhere in the codebase** (verified via grep — only `groceryListItems` plural relation, unrelated, is used). Since this conversion service makes that kind of persistent link unnecessary (we're doing copies, not links), add a migration to drop this column and remove the `pantryItem()` relation from `app/Models/GroceryListItem.php`.

## What to deliver

1. A new service class (e.g. `app/Services/ItemConversionService.php`) with one method per target type, implementing the field-mapping table above.
2. 3 new routes in `routes/api.php` inside the existing `auth:api` group, e.g.:
   - `POST /api/convert/to-pantry-item`
   - `POST /api/convert/to-grocery-list-item`
   - `POST /api/convert/to-recipe-ingredient`
3. A small controller (e.g. `ItemConversionController`) wiring routes → service, with request validation per the table (including ownership checks on `source_ids`, `space_id`, `grocery_list_id`, `recipe_id`).
4. Migration to drop `grocery_list_items.pantry_item_id` + remove the unused relation from the model.
5. Return shape: mirror existing patterns — `{ "message": "...", "data": <created row(s)> }`.

Frontend wiring (buttons/UI on Shopping/Pantry/Recipe pages) is a **follow-up**, not part of this task — focus on the backend service + endpoints + cleanup migration first. Confirm the field-mapping table against the actual current schema (re-check migrations in `database/migrations/`) before implementing, since the schema has evolved across many migrations.

## Frontend wiring plan (follow-up, not part of this task)

Once the 3 endpoints above exist, wire them into the Pantry/Grocery List/Recipe item cards (`PantryItemsCard.vue`, `GroceryListItemsCard.vue`, the recipe ingredients equivalent, and the shopping session page) like this:

1. **Selection mode, reused from `GroceryListCard.vue`/`GroceryListsPage.vue`.** Each items-card gets a toggle button (next to the existing "+" add button) that flips its list into select mode — same checkbox-overlay pattern already used for "save shopping session". Add a "select all" toggle in the same header area.

2. **Convert action.** Once ≥1 item is selected, show a "Move to…" / "Add to…" button. Per the mapping table, every source type has **exactly 2** valid targets (pantry → grocery list item / recipe ingredient; grocery/shopping item → pantry item / recipe ingredient; recipe ingredient → pantry item / grocery list item), so this opens a small modal.

3. **Wizard modal (single `Modal.vue`, multi-step via a local `step` ref):**
   - **Step 1 — destination type:** a 2-card chooser, same visual pattern as the existing "Recipe Actions" modal (icon + title + subtitle per card), showing the 2 valid targets for the current source type.
   - **Step 2 — destination instance:** a dropdown/list to pick *which* grocery list / recipe / storage space to copy into (the `grocery_list_id` / `recipe_id` / `space_id` the endpoint requires).
   - **Step 3 — optional extra fields:** e.g. `expiry_date` when the target is a pantry item.
   - Footer has Back / Next / Confirm buttons; body content swapped via `v-if="step === N"` — no routing or separate page needed.

4. **Submit & feedback.** On confirm, call the matching `/api/convert/to-*` endpoint with `source_type`, `source_ids`, and the step 2/3 field(s). Show a success/error toast (existing `toastStore` pattern). Since conversions are copies, the source list doesn't need refreshing; the target list only needs refreshing if it happens to be the currently-open page (out of scope to wire cross-page refresh for v1 — a toast confirming what was created is enough).
