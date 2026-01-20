# Frontend Development Plan

## Current State Analysis

### Backend Status ✅ (Complete)
- ✅ Auth (login/register with JWT cookies)
- ✅ User profile (GET/PATCH/DELETE `/me`)
- ✅ SpaceStorage (CRUD)
- ✅ PantryItem (CRUD)
- ✅ Recipe (CRUD)
- ✅ RecipeIngredient (CRUD)
- ✅ GroceryList (CRUD)
- ✅ GroceryListItem (CRUD)

### Frontend Status ⚠️ (In Progress)
- ✅ Basic auth store
- ✅ Login page (missing success handling)
- ⚠️ Empty Dashboard
- ❌ Missing: Register page, resource stores, resource pages, navigation

---

## Recommended Development Roadmap

### Phase 1: Complete Authentication Flow (START HERE)

#### 1. Fix Login Flow
- Update auth store after successful login
- Redirect to dashboard on success
- Handle error messages properly

#### 2. Add Register Page
- Create `Register.vue` similar to Login.vue
- Add route in router
- Update auth store with register method

#### 3. Enhance Auth Store
- Add `login()` and `register()` methods
- Handle user state updates after login/register
- Add logout that properly clears cookies

#### 4. Add NotFound Catch-All Route
- Add catch-all route `/:pathMatch(.*)*` to router (must be last route)
- Redirect to Error page with 404 code and message
- Handles any non-existent routes (e.g., `/safsdaas` → `/error?code=404&message=The page you're looking for doesn't exist.`)

---

### Phase 2: Dashboard & Navigation

#### 5. Build Dashboard
- Add overview cards (storage spaces count, pantry items, recipes, etc.)
- Add navigation sidebar/menu
- Quick action buttons

#### 6. Add Navigation Component
- Create sidebar or top nav in DashboardLayout
- Links to main sections
- User profile dropdown/logout

---

### Phase 3: Core Features (Priority Order)

#### 7. SpaceStorage Management (Simplest - Start Here)
- Create store: `stores/spaceStorage.ts`
- Pages: List, Create/Edit, Delete
- Routes: `/storage-spaces`, `/storage-spaces/new`, `/storage-spaces/:id/edit`

#### 8. PantryItem Management
- Create store: `stores/pantryItem.ts`
- Pages: List (with filters), Create/Edit form, Delete
- Show expiry dates, group by space
- Filter by space, expiry date, etc.

#### 9. Recipe Management
- Create store: `stores/recipe.ts`
- Pages: List, Create/Edit (with ingredients), View detail
- ✅ Handle RecipeIngredient nested CRUD
- ✅ Form to add/remove ingredients (add from card and from edit modal)
- ✅ Delete ingredients with confirmation
- Save recipe description as WYSIWYG (rich text editor)
- Add validation to all recipe subcomponents
- Add validation to the ingredients modal
- Make ingredients in Recipes.vue expandable: show symbol if they have a note, make all ingredients expandable, when extended show the notes part of RecipeIngredient
- Add emoji/image picker using emoji-mart-vue: https://serebrov.github.io/emoji-mart-vue/
- - ❌ TODO: Fix the edit and delete buttons going under the components and having opacity not set to full

#### 10. GroceryList Management
- Create store: `stores/groceryList.ts`
- Pages: List, Create/Edit, View with items
- Add a status filter select (All / Completed / Not completed) next to the search bar on `frontend/src/pages/GroceryList/GroceryListsPage.vue`
- Mark items as purchased
- Convert to pantry items functionality

##### Go shopping (Plan B: multi-list shopping session)

**Goal**
- Let the user pick **N shopping lists**, then shop them in one combined, in-store friendly view.
- Keep it always clear **which list each item belongs to**, and ensure toggles/adds update the correct list.

**User flow**
- Entry points:
  - From `Shopping Lists` page: add a primary action like "Start shopping" (new page).
  - From inside a single list: "Add more lists" to start a session pre-filled with current list.
- Step 1: Select lists
  - Show a list picker with search (reuse existing list pagination endpoint).
  - User selects 1..N lists, taps "Start".
- Step 2: Shopping session view
  - Combined view optimized for fast checking off items.
  - Option to switch between different grouping modes (see UI ideas below).
- Step 3: Finish / exit session
  - Simple: leave session at any time (state persisted).
  - Optional: provide "Finish shopping" (mark selected lists as completed) and/or "Checkout to pantry".

**Screens**
- `StartShoppingSessionPage.vue`
  - Search + multi-select list cards
  - Shows selected list chips at top
  - "Start shopping" button enabled when at least one list selected
- `ShoppingSessionPage.vue`
  - Top bar:
    - Selected list chips (removable)
    - Session title like "Shopping session"
    - Progress summary (overall + per list)
  - Item list area:
    - Big checkboxes, large tap targets, quick add
    - Purchased items optionally collapsed into a "Purchased" section

**UI idea 1 (recommended to start): grouped by list (matches your original idea)**
- Render sections per list:
  - Section header: list emoji/name + per-list progress like "3/12"
  - Items underneath: checkbox, formatted quantity/unit/name/notes
- Add a subtle list identity cue:
  - Color-tint the section header or left border per list
  - Also show a small list badge on each row for clarity when scrolling
- Pros: simplest mental model, zero dedupe edge cases.

**UI idea 2 (optional upgrade): grouped by item name (dedupe), with list provenance**
- Merge items across selected lists by normalized name (and optionally unit):
  - Single "Milk" row may represent items from multiple lists
  - Under the row, show list badges like "Weekly", "Party"
- Toggle behavior options:
  - Tap checkbox toggles all underlying items
  - Expand row to toggle per-list if needed
- Pros: less scrolling, faster in-store; Cons: requires careful merge rules and UI for conflicts.

**Adding items during a session**
- Quick add input (always visible):
  - Default target list: last used in session
  - Allow changing target list via a dropdown next to the input
- Submit creates a normal grocery list item via the existing API.

**API usage (no backend changes required)**
- List picker:
  - `POST /api/get-grocery-lists` with `{ searchTerm, per_page }`
- Load selected lists (session bootstrap):
  - For each selected list id, call `GET /api/grocery-lists/{id}` to obtain `grocery_list_items`
- Toggle purchased:
  - `PATCH /api/grocery-list-items/{id}` with `{ is_purchased: boolean }`
- Add item:
  - `POST /api/grocery-list-items` with `{ grocery_list_id, name, quantity, unit, notes, sort_order, is_purchased }`

**Frontend state strategy (important for multi-list)**
- Maintain a local session state shaped like:
  - `selected_list_ids: number[]`
  - `lists_by_id: Record<number, GroceryList>`
  - `items_by_list_id: Record<number, GroceryListItem[]>`
- Updates:
  - Prefer optimistic UI for `is_purchased` toggles (flip immediately, revert on error).
  - When the API returns an updated list payload (current behavior), update only that list’s items in `lists_by_id[list_id]`.
- Persistence:
  - Save `selected_list_ids` (and optionally grouping mode) in `localStorage` so the session resumes.

**Edge cases**
- A selected list is deleted/forbidden mid-session:
  - Remove it from the session and show a toast.
- Conflicts in dedupe mode (UI idea 2):
  - Different quantities/units/notes across lists: show per-list details on expand.
- Performance:
  - Limit max selected lists in the UI (e.g. 10) to avoid many parallel requests.

**Optional backend improvements (later, not required for v1)**
- Single endpoint to fetch multiple lists with items in one request (reduces N requests):
  - e.g. `POST /api/shopping-session` with `{ grocery_list_ids: number[] }`
- Bulk toggle endpoint for faster completion:
  - e.g. `PATCH /api/grocery-list-items/bulk` with item ids + new state
- "Checkout" endpoint to convert purchased grocery list items into pantry items in one transaction.

---

## Suggested File Structure

```
frontend/src/
├── stores/
│   ├── auth.ts (enhance existing)
│   ├── spaceStorage.ts (new)
│   ├── pantryItem.ts (new)
│   ├── recipe.ts (new)
│   └── groceryList.ts (new)
├── pages/
│   ├── Login.vue (fix redirect)
│   ├── Register.vue (new)
│   ├── Dashboard.vue (build out)
│   ├── StorageSpaces/
│   │   ├── Index.vue
│   │   └── Form.vue
│   ├── PantryItems/
│   │   ├── Index.vue
│   │   └── Form.vue
│   ├── Recipes/
│   │   ├── Index.vue
│   │   ├── Form.vue
│   │   └── Show.vue
│   └── GroceryLists/
│       ├── Index.vue
│       ├── Form.vue
│       └── Show.vue
└── components/
    └── Navigation.vue (new - sidebar/navbar)
```

---

## Immediate Next Steps

### Priority 1: Fix Login & Add Register
1. **Fix Login redirect** - Update auth store, redirect to dashboard
2. **Add Register page** - Copy Login.vue structure, add registration
3. **Enhance auth store** - Add login/register methods
4. **Add NotFound route** - Catch-all route for non-existent paths, redirect to Error page with 404

### Priority 2: Build Basic Dashboard
1. **Dashboard overview** - Welcome message, basic layout
2. **Navigation menu** - Links to main sections
3. **User info display** - Show logged in user

### Priority 3: First Feature (SpaceStorage)
1. **Create store** - API integration
2. **List page** - Display all storage spaces
3. **Form pages** - Create and edit storage spaces

---

## Notes

- Follow existing patterns from Login.vue for consistency
- Use Tailwind CSS for styling (already configured)
- HTTP-only cookies are handled automatically by the browser
- All API calls should use the axios instance from `api/http.ts` (already configured with `withCredentials: true`)
- Authentication middleware is handled by Laravel (`jwt.cookie` + `auth:api`)

---

## Progress Log

### 2025-01-XX - Recipe Ingredients Management Enhancements

#### Frontend Improvements

**RecipeIngredientCard Component:**
- ✅ Added "+" button to add new ingredients directly from the ingredient card
- ✅ Added ability to add new ingredients from within the edit ingredients modal
- ✅ Added trash/delete button to each ingredient in the edit modal
- ✅ Implemented delete functionality with confirmation dialog
- ✅ Added smooth scroll to bottom when adding new ingredient in edit modal
- ✅ Improved UI/UX with consistent button styling matching modal close button

**ConfirmModal Component (New):**
- ✅ Created globally registered `ConfirmModal` component
- ✅ Implemented `$confirm` global method accessible via `useConfirmStore()`
- ✅ Returns Promise<boolean> - true on confirm, false on cancel/close
- ✅ Reusable confirmation dialog with customizable message
- ✅ Styled to match existing modal design patterns

**Delete Functionality:**
- ✅ Handles deletion of both new (unsaved) and existing ingredients
- ✅ Shows confirmation dialog before deletion
- ✅ Updates parent component data via emit after successful deletion
- ✅ Proper error handling with toast notifications

#### Backend Improvements

**RecipeController:**
- ✅ Implemented `deleteIngredientFromRecipe()` method
- ✅ Route: `DELETE /api/recipe/{recipe}/ingredient/{ingredient}`
- ✅ Verifies recipe ownership and ingredient relationship
- ✅ Uses soft delete (RecipeIngredient model uses SoftDeletes)
- ✅ Returns updated recipe with ingredients loaded

**Route Updates:**
- ✅ Updated route from POST to DELETE method
- ✅ Changed route pattern to match frontend: `recipe/{recipe}/ingredient/{ingredient}` (singular)

#### Technical Details

- Used Pinia store (`useConfirmStore`) for confirm modal state management
- Implemented Vue 3 composition API with TypeScript
- Maintained consistency with existing code patterns
- Proper error handling and loading states
- Reactive data updates between parent and child components

---

### 2025-01-XX - Recipe Card Button UI Enhancements

#### Frontend Improvements

**Enhanced Button Styling Across Recipe Components:**
- ✅ Updated `RecipeTitleCard.vue` edit button with enhanced hover and click effects
- ✅ Applied consistent button styling to `RecipeIngredientCard.vue` (add and edit buttons)
- ✅ Applied consistent button styling to `RecipeInstructionsCard.vue` edit button

**Button Styling Features:**
- ✅ Added border styling (`border-2 border-gray-200`) for better visual definition
- ✅ Enhanced hover effects: darker border (`hover:border-gray-300`), stronger shadow (`hover:shadow-xl`), scale up to 110% (`hover:scale-110`)
- ✅ Active/click feedback: scale down to 95% (`active:scale-95`) with reduced shadow (`active:shadow-md`) for tactile feedback
- ✅ Smooth transitions (`transition-all duration-200`) for polished interactions

**Components Updated:**
- `frontend/src/components/Recipes/RecipeTitleCard.vue` - Edit recipe button
- `frontend/src/components/Recipes/RecipeIngredientCard.vue` - Add ingredient and edit ingredients buttons
- `frontend/src/components/Recipes/RecipeInstructionsCard.vue` - Edit instructions button

#### Technical Details

- Consistent button styling across all recipe card components
- Improved user experience with clear visual feedback on hover and click
- Maintained existing backdrop blur and transparency effects
- All buttons now provide consistent, polished interaction patterns

---

### 2025-01-XX - Recipe Card Layout & UI Improvements

#### Frontend Improvements

**RecipeCard Component (`frontend/src/components/Recipes/RecipeCard.vue`):**
- ✅ Fixed layout for larger screens (sm and above) to properly display title and description
- ✅ Changed placeholder SVG icon from book icon to recipe-related document icon
- ✅ Improved content alignment on larger screens with `sm:justify-start` for better text positioning
- ✅ Added `line-clamp-3` support for description on larger screens (shows up to 3 lines instead of 1)

**RecipeTitleCard Component (`frontend/src/components/Recipes/RecipeTitleCard.vue`):**
- ✅ Fixed title centering issue when using `max-w-[220px]` constraint
- ✅ Added `mx-auto` to center the h1 element horizontally, matching the description alignment
- ✅ Added `break-words` to ensure long recipe titles wrap properly instead of overlapping the menu button
- ✅ Title now properly wraps to new lines when exceeding 220px width, preventing overlap with the three-dot menu button

#### Technical Details
- Used Tailwind CSS responsive utilities (`sm:`) for larger screen layouts
- Implemented proper text wrapping with `break-words` and `line-clamp` utilities
- Fixed flexbox alignment issues with `mx-auto` for centered elements with max-width constraints
- Maintained small screen layout (mobile-first approach) while improving larger screen experience

---

### 2025-01-XX - Responsive Design Fixes

#### Frontend Improvements

**RecipePage Component:**
- ❌ TODO: Fix smaller screen alignment for `frontend/src/pages/Recipe/RecipePage.vue`

---

### 2025-01-XX - Database Seeding

#### Backend Tasks

**GroceryList Seeder:**
- ❌ TODO: Make a seeder to add grocery_list_items to grocery lists

#### Frontend Improvements

**RecipePage Component:**
- ❌ TODO: Fix smaller screen alignment for `frontend/src/pages/Recipe/RecipePage.vue`

