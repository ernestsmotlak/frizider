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
- Handle RecipeIngredient nested CRUD
- Form to add/remove ingredients
- Save recipe description as WYSIWYG (rich text editor)

#### 10. GroceryList Management
- Create store: `stores/groceryList.ts`
- Pages: List, Create/Edit, View with items
- Mark items as purchased
- Convert to pantry items functionality

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

