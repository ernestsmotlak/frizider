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

##### Recipe Usage Tracking & Rating System
**Goal:**
- Track how many times a recipe has been cooked
- Allow users to rate recipes (e.g., 1-5 stars)
- Display usage count and rating on recipe cards and detail pages

**Features:**
- **Usage Counter:**
  - Increment counter when user marks recipe as "cooked" or "completed"
  - Display count on recipe cards (e.g., "Cooked 5 times")
  - Show in recipe detail view
  - Optional: Track last cooked date

- **Rating System:**
  - Star rating (1-5 stars) that users can set
  - Display average rating on recipe cards
  - Show rating in recipe detail view
  - Allow users to update their rating
  - Optional: Show rating distribution or reviews

**Database Changes:**
- Add `times_cooked` (integer, default 0) to `recipes` table
- Add `rating` (decimal/float, nullable) to `recipes` table (or create separate `recipe_ratings` table if multi-user ratings needed)
- Add `last_cooked_at` (timestamp, nullable) to `recipes` table (optional)

**Backend API:**
- `PATCH /api/recipes/{id}/mark-cooked` - Increment times_cooked, update last_cooked_at
- `PATCH /api/recipes/{id}/rating` - Update recipe rating
- Include `times_cooked`, `rating`, `last_cooked_at` in recipe responses

**Frontend Implementation:**
- Add "Mark as Cooked" button/action in recipe detail view
- Add star rating component/selector
- Display usage stats and rating on recipe cards
- Filter/sort recipes by rating or times cooked
- Show "Most Cooked" or "Top Rated" sections on recipes list page

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

### Cooking mode

- **Wizard-style cards per recipe part:** Present the recipe as a step-by-step wizard: one card per “part” of the recipe (e.g. title/overview, ingredients, then each instruction or grouped instructions). User advances through cards (Next/Back) so it feels like a guided cooking flow.
- **Cards in a wizard by recipe:** Structure the cooking view as a wizard where each step is a card; cards follow the recipe structure (intro → ingredients → instruction 1 → instruction 2 → …).
- **Darker theme (optional):** Use a darker theme for the cooking view so it’s visually distinct from the normal recipe page and easier in the kitchen (e.g. reduced glare, “cooking time” feel).
- **Edit ingredients/instructions:** When the user wants to edit instructions or ingredients from the cooking view, switch to a view that is “almost normal recipe page” (same edit/add behavior as RecipePage) so they can make changes, then return to the wizard/cooking flow. Keeps full edit capability without mixing it into the main wizard cards.

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


#### Timer feature (FAB + sheet, draggable)

- **Pattern:** Floating Action Button (FAB) that opens a timer bottom sheet. When at least one timer is active, show a circle icon (FAB) on screen.
- **Placement (default):** Bottom-right, floating **above** the sticky bottom nav bar (e.g. `bottom: calc(navbar-height + 12px)`). Add bottom padding to the main scroll area so content is not hidden behind the FAB.
- **Draggable FAB:** User can **long-press** the FAB then **drag** it anywhere on the screen.
  - **Short tap** → open timer sheet.
  - **Long-press** (~300–400 ms) → enter "move" mode, then drag; release to drop. Optional: light haptic on long-press.
  - **Persistence:** Store FAB position (e.g. `{ x, y }` as % of viewport or px) in the same place as timer state (composable or Pinia) so it survives navigation within the session.
  - **Bounds:** Clamp position so the FAB stays fully visible and does not sit under the sticky nav or in system safe areas (e.g. allow dragging only in the content rectangle from below header to above nav bar).
  - **Default:** First time (or no saved position): bottom-right above nav; after first drag, use saved position.
- **Discoverability / a11y:** Expose a "Move timer button" hint (e.g. long-press hint in label or short instructions) so users know they can reposition the FAB.

---

## Cooking Session Timers – Extended Implementation

### Architecture Overview

**Pattern:** Centralize all timer API calls in a composable (`useCookingSessionTimers`). `Timers.vue` emits intent with minimal payloads; `Cooking.vue` listens, calls composable methods, and owns session/timers state. No POST logic or session data in Timers.vue.

**Flow:** Timers.vue → emit (e.g. `start-timer` with `timer_id`) → Cooking.vue handler → composable method → axios → update `cookingSession` / `timers` refs from `response.data.data`.

---

### Backend API (CookingSessionTimerController)

| Method | Payload | Returns |
|--------|---------|---------|
| `createTimer` | `{ note, duration_seconds }` | `data` (session + timers) |
| `startTimer` | `{ timer_id }` | `data` |
| `pauseOrContinueTimer` | `{ timer_id, action: "pause" \| "continue" }` | `data` |
| `completeTimer` | `{ timer_id }` | `data` |
| `updateTimer` | `{ timer_id }` + optional `note`, `sort_order`, `duration_seconds`, `status` | `data` |
| `deleteTimer` | `{ timer_id }` | `data` |
| `reorderTimers` | `{ orders: [{ timer_id, sort_order }, ...] }` | `data` |
| `resetTimer` | `{ timer_id }` | `data` |

**Middleware:** Accepts optional `location: { timer_fab_x_percent, timer_fab_y_percent }` to persist FAB position.

**Timer statuses:** `idle`, `running`, `paused`, `completed`.

---

### Composable: `useCookingSessionTimers`

**File:** `frontend/src/composables/useCookingSessionTimers.ts`

**Options:**
- `cookingSession: Ref<CookingSessionData | null>`
- `timers: Ref<Timers[]>`
- `onSuccess?: (data: CookingSessionData) => void` (optional)

**Returned methods:**
- `createTimer(payload: { note: string; duration_seconds: number })`
- `startTimer(timerId: number)`
- `pauseOrContinueTimer(timerId: number, action: "pause" | "continue")`
- `completeTimer(timerId: number)`
- `resetTimer(timerId: number)`
- `deleteTimer(timerId: number)`
- `reorderTimers(orders: Array<{ timer_id: number; sort_order: number }>)`

**Behavior:** Each method POSTs to the timer API, then updates `cookingSession` and `timers` from `response.data.data`. Uses `useToastStore()` for errors.

**Usage in Cooking.vue:**
```ts
const { createTimer, startTimer, pauseOrContinueTimer, completeTimer, resetTimer, deleteTimer, reorderTimers } =
    useCookingSessionTimers({ cookingSession, timers });
```

---

### Timers.vue – UI Additions

#### 1. Extended `Timers` interface

Add `id` and `original_duration_seconds`. Remove debug `<pre>{{ t }}</pre>`.

#### 2. Per-timer action buttons (status-based)

| Status | Button(s) | Emit |
|--------|-----------|------|
| `idle` | Start | `start-timer` |
| `running` | Pause, Complete | `pause-timer`, `complete-timer` |
| `paused` | Continue, Complete, Reset | `continue-timer`, `complete-timer`, `reset-timer` |
| `completed` | Reset | `reset-timer` |
| All | Delete | `delete-timer` |

Use icons: Play ▶, Pause ⏸, Check ✓, Reset ↺, Trash/X.

#### 3. Add-timer form (create)

- Text input for **note**
- Duration input (minutes and/or seconds)
- "Add timer" button → emit `create-timer` with `{ note, duration_seconds }`

Place at top of list or in collapsible section.

#### 4. Empty state

When `timers.length === 0`: message like "No timers yet" and primary "Add timer" button to reveal or focus the form.

#### 5. Status-based visual styling

- **idle**: neutral/gray
- **running**: accent color, optional ring animation
- **paused**: muted or orange
- **completed**: checkmark, muted, strikethrough on note

#### 6. Timer card layout

```
┌─────────────────────────────────────────────────┐
│ [ring]  Note / label                             │
│         MM:SS                                    │
│         [Start/Pause/Continue] [Complete] [Reset] [Delete] │
└─────────────────────────────────────────────────┘
```

Show only buttons valid for current status.

#### 7. Optional: reorder

- Drag handle (⋮⋮) on each card
- VueDraggable on list, emit `reorder` on drag end

#### 8. Other tweaks

- Use `t.id` as `:key` instead of index
- `aria-label` on icon-only buttons
- Live countdown for `running` timers (compute remaining from `started_at` + `duration_seconds`)

---

### Implementation Checklist

| Item | Where |
|------|-------|
| Add `id`, `original_duration_seconds` to `Timers` interface | Timers.vue |
| Remove debug `<pre>` | Timers.vue |
| Live countdown for `running` timers | Timers.vue |
| Start / Pause / Continue buttons | Timers.vue |
| Complete button | Timers.vue |
| Reset button | Timers.vue |
| Delete button | Timers.vue |
| Add-timer form | Timers.vue |
| Emit handlers (no axios in Timers) | Timers.vue |
| Create composable `useCookingSessionTimers` | composables/ |
| Wire composable in Cooking.vue | Cooking.vue |
| Listen to emits, call composable methods | Cooking.vue |
| Fix progress ring denominator logic | Timers.vue |
| Timer reorder (optional) | Timers.vue |
| Persist FAB position with `location` (optional) | Cooking.vue |
| Wire timer routes in `api.php` | routes/api.php |