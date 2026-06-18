# Current Plan — What to Develop Next

Based on `PLAN.md` vs. what's actually in the repo right now.

## PLAN.md sections that are stale (already done, just not marked)

- **"Go shopping" multi-list session** — fully built (`Shopping.vue`, `ShoppingItemCard.vue`, `ShoppingProgressBar.vue`, `ShoppingItemEditModal.vue`, `ShoppingListFilter.vue`, backend `ShoppingSessionController`/`ShoppingItemController`).
- **Cooking mode + timers** — also built (`Cooking.vue`, `Cooking/Timers.vue`, `Cooking/FinishedTimer.vue`, `CookingModal.vue`, `RecipeCookingModal.vue`, backend `CookingSessionController`/`CookingSessionTimerController`, multiple migrations for timer percent/duration support).
- **Register page** — exists (`Register.vue`).
- **Conversion service + frontend wiring** — done (see `CONVERSION_SERVICE_PLAN.md`). UI subsequently refined: all selection-mode chrome (toggle button, selected item cards, checkboxes, "Move" button, modal target cards, Next/Confirm button) uses violet instead of blue; ConvertItemsModal step 2 replaced native `<select>` with a custom searchable list picker (with a clear ✕ button); grocery lists in the picker are tinted green with a checkmark when completed.

So a good chunk of "Phase 3" and the timer checklist is likely already complete. Worth a pass to update `PLAN.md` itself so it reflects reality — otherwise it'll keep suggesting work that's done.

## Confirmed NOT implemented yet

- **Recipe Usage Tracking & Rating System** — grepped for `times_cooked`, `rating`, `mark-cooked` etc. in `Recipe.php` and `routes/api.php`: nothing found. This is a real gap. It's a self-contained feature (2 migration columns + 2 small endpoints + star-rating UI + "Most Cooked"/"Top Rated" sections) and pairs naturally with the cooking-mode flow that already exists — when a cooking session finishes, that'd be the natural trigger to increment `times_cooked`/`last_cooked_at`.

## Small, concrete ❌ TODOs explicitly listed in PLAN.md

1. **Recipe edit/delete buttons overlap/opacity bug** — "edit and delete buttons going under the components and having opacity not set to full." Quick visual bug fix.
2. **RecipePage.vue responsive/small-screen alignment** — listed twice in PLAN.md, so likely a recurring annoyance.
3. **Grocery list seeder** — "make a seeder to add grocery_list_items to grocery lists" — pure dev-convenience, low priority but trivial.

## Priority recommendation

- **Quick wins first**: the recipe card button overlap bug and RecipePage responsive fix — both are small, visible polish issues that affect daily use of an already-built page.
- **Biggest "new feature" value**: Recipe Usage Tracking & Rating — it's scoped clearly in the plan, touches a part of the app (recipes + cooking) that's otherwise feature-complete, and gives you actually useful data (top-rated/most-cooked recipes) for relatively little surface area.
- **Housekeeping**: update `PLAN.md` to strike the sections that are done (shopping session, cooking/timers, conversion service) so it stops misrepresenting the project's current state.
- **Lowest priority**: the grocery list seeder — only matters for local dev/demo data, doesn't affect the real app.
