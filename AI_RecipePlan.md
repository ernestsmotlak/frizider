# AI Recipe Generation – Implementation Plan

## Overview

- **Permission & quota:** Stored in `ai_user_data` (permission + number of AI calls left). Never sent to frontend or writable by frontend.
- **Frontend:** Buttons on RecipesPage and NewRecipe (e.g. “AI generate recipe”, “Turn vegetarian”, “Turn vegan”). Optional minimal endpoint to know if user can use AI and how many calls remain.
- **Backend:** One authenticated route that checks `ai_user_data` (permission + calls remaining), calls OpenAI (key only in env/config), validates response, saves into existing Recipe/RecipeIngredient/RecipeInstruction with `is_ai_generated = true`, decrements calls remaining, and logs to `user_ai_recipe_logs`.
- **Security:** API key only server-side; rate limit; input limits; recipe ownership for “turn vegetarian/vegan”.
- **Logging:** Table `user_ai_recipe_logs` linked to user and to the created recipe (and optionally source recipe); optional debug table for raw request/response.
- **Recipe storage:** Same `recipes` / `recipe_ingredients` / `recipe_instructions`; add `is_ai_generated` on `recipes`; expose in list/detail for frontend “AI” badge.

---

## Where to store “AI calls left”

**Recommendation: store in `ai_user_data`.**

Keep permission and quota in one table:

- `can_generate_ai_recipes` (boolean) – may use the feature at all.
- `ai_recipe_calls_remaining` (unsigned integer, default 0) – number of AI calls left for this user.

Logic: user can call the AI endpoint only if `can_generate_ai_recipes` is true **and** `ai_recipe_calls_remaining > 0`. After each successful AI generation, decrement `ai_recipe_calls_remaining` in the same transaction as creating the recipe and log. Optionally cap at 0 (no negative values). You can grant more calls by updating this column (seed, admin, or internal tool); frontend never sends or receives it except via an optional minimal endpoint that returns only e.g. `can_generate_ai_recipes` and `ai_recipe_calls_remaining` for UX (hide/disable button when 0).

---

## Checklist

### 1. Database & migrations

- [ ] **Migration: `ai_user_data` (or `ai_user_permissions`)**
  - Columns: `id`, `user_id` (FK to users, unique), `can_generate_ai_recipes` (boolean, default false), **`ai_recipe_calls_remaining`** (unsigned integer, default 0), `timestamps`.
  - Create rows only when you grant access (or when you grant first batch of calls); “no row” = no permission.

- [ ] **Migration: `user_ai_recipe_logs`**
  - Columns: `id`, `user_id` (FK users), `recipe_id` (nullable FK recipes – the created recipe), `action` (string: generate, generate_from_ingredients, turn_vegetarian, turn_vegan), `source_recipe_id` (nullable FK recipes), `request_meta` (nullable JSON), `success` (boolean), `error_message` (nullable), `tokens_used` (nullable unsigned), `idempotency_key` (nullable string, unique per user if used), `created_at` (and `updated_at` if needed).
  - Log links to **user** and to the **possibly created recipe** (and optionally source recipe for turn_*).

- [ ] **Migration: add `is_ai_generated` to `recipes`**
  - Boolean, default false.

- [ ] **(Optional) Migration: `ai_recipe_request_logs`**
  - For debug only: `id`, `user_ai_recipe_log_id` (FK), `request_payload`, `response_payload`, `created_at`. Consider PII and retention.

---

### 2. Models & relationships

- [ ] **Model: `AiUserData`**
  - Fillable/guarded as needed; `belongsTo(User::class)`. Include `can_generate_ai_recipes` and `ai_recipe_calls_remaining`.

- [ ] **Model: `UserAiRecipeLog`**
  - `belongsTo(User::class)`, `belongsTo(Recipe::class, 'recipe_id')`, `belongsTo(Recipe::class, 'source_recipe_id')`.

- [ ] **User model**
  - Add `hasOne(AiUserData::class)` (e.g. `aiUserData()`).

- [ ] **Recipe model**
  - Add `is_ai_generated` to `$fillable` and `$casts` (boolean). Optionally `hasMany(UserAiRecipeLog::class, 'recipe_id')`.

- [ ] **(Optional) Model: `AiRecipeRequestLog`**
  - Only if you added the optional debug migration.

---

### 3. Config & env

- [ ] **`.env`**
  - Add `OPENAI_API_KEY=...`. Do not commit the key.

- [ ] **`config/services.php`**
  - Add e.g. `openai.api_key` and `openai.timeout` (e.g. 60). Use in backend only.

---

### 4. Backend: OpenAI integration

- [ ] **HTTP client to OpenAI**
  - Timeout from config; key from `config('services.openai.api_key')`.

- [ ] **Service/class for AI recipe**
  - Inputs: variant + optional ingredients/recipe text. System prompt: return JSON (name, description, servings, prep_time, cook_time, ingredients[], instructions[]). Call OpenAI; return parsed array or throw.

- [ ] **Response validation**
  - Same rules as `saveRecipeWithData`. All-or-nothing; on failure return 422 and do not save.

---

### 5. Backend: API

- [ ] **Route**
  - e.g. `POST /api/recipes/ai-generate` inside `auth:api`, with throttle.

- [ ] **Controller method**
  - Load `AiUserData` for current user. If no row or `can_generate_ai_recipes` is false → 403.
  - **If `ai_recipe_calls_remaining` is 0 → 403 (or 429) and do not call OpenAI.**
  - Validate request (variant, optional ingredients, optional recipe_id). Enforce input limits.
  - For turn_vegetarian/vegan: load recipe with `where('user_id', auth()->id())`.
  - (Optional) Idempotency key: if already used for this user, return existing recipe or 409.
  - Call AI service; parse and validate response.
  - **In one transaction:** create Recipe (`is_ai_generated => true`), RecipeIngredient, RecipeInstruction; create `UserAiRecipeLog` with user_id, recipe_id, action, success true; **decrement `AiUserData.ai_recipe_calls_remaining`** (never below 0); commit.
  - On failure: log with success false, do not decrement calls remaining. Return 422/502/503 as appropriate.
  - Return same JSON shape as your existing create-recipe endpoint.

- [ ] **(Optional) Route: `GET /api/ai/can-generate-recipe`**
  - Auth required. Return only `{ "can_generate_ai_recipes": true|false, "ai_recipe_calls_remaining": number }`. No other user data. Frontend uses this to show/hide or disable AI buttons and display “X calls left”.

---

### 6. Expose `is_ai_generated` to frontend

- [ ] **`paginateRecipes` (get-recipes)**
  - Include `is_ai_generated` in selected attributes and response.

- [ ] **`show` (single recipe)**
  - Include `is_ai_generated` in recipe payload.

---

### 7. Frontend

- [ ] **RecipesPage.vue**
  - Add buttons: “Generate recipe with AI”, optionally “Turn vegetarian” / “Turn vegan” (pass recipe id when applicable). Optionally call `GET /api/ai/can-generate-recipe` to show/hide and show “X calls left”. On 403 or 0 calls, hide or disable.

- [ ] **NewRecipe.vue**
  - Same AI actions. For “generate from ingredients”, send current ingredients (and optional name/description). On success, fill form or redirect to created recipe.

- [ ] **Recipe list/detail/card**
  - Use `is_ai_generated` to show “AI” badge or different styling.

---

### 8. Optional

- [ ] **Idempotency**
  - Request body `idempotency_key`; store in `user_ai_recipe_logs`; on duplicate for same user return existing recipe or 409; do not decrement calls again for same key.

- [ ] **`ai_recipe_request_logs`**
  - Only if you need debug; mind PII and retention.

---

### 9. Security & robustness

- [ ] API key only in env/config; never in frontend or API responses.
- [ ] Permission and quota only in `ai_user_data`; not writable by frontend.
- [ ] Rate limit on AI route.
- [ ] Input limits (ingredient count, lengths).
- [ ] Recipe ownership for `recipe_id` (turn_vegetarian/vegan).
- [ ] Timeout on OpenAI HTTP call.
- [ ] All-or-nothing save; decrement `ai_recipe_calls_remaining` only on successful generation.

---

## Summary table

| Item | Recommendation |
|------|----------------|
| Permission + quota | **`ai_user_data`**: `can_generate_ai_recipes`, **`ai_recipe_calls_remaining`**. Not sent/writable from frontend; set only server-side or admin. |
| Optional frontend hint | `GET /api/ai/can-generate-recipe` → `{ can_generate_ai_recipes, ai_recipe_calls_remaining }`. |
| Frontend | Buttons on RecipesPage + NewRecipe; variants: generate, generate_from_ingredients, turn_vegetarian, turn_vegan; show/disable when 0 calls. |
| Backend | One POST route; auth + check permission and calls remaining; validate → call OpenAI → validate response → save recipe + ingredients + instructions in transaction; decrement `ai_recipe_calls_remaining`; create UserAiRecipeLog; return same shape as create. |
| Logging | `user_ai_recipe_logs` with user_id, recipe_id (created), source_recipe_id (turn_*); optional `ai_recipe_request_logs`. |
| Recipe storage | Same Recipe / RecipeIngredient / RecipeInstruction; `is_ai_generated` on recipes; expose in list/detail. |
