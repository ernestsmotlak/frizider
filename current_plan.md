# Current Plan

_Last updated: 2026-08-08_

## Done today

Five commits, all on the AI recipe feature. Working tree clean.

- **Fail fast on bad requests** (`3000cf5`) — `PermanentAiException`; a 4xx from Gemini (except 429)
  skips the remaining retries and refunds immediately instead of spinning through the backoff.
  The provider's raw error stays server-side; the user sees one plain sentence.
- **Server-side "seen" state** (`3c3c9d8`) — `acknowledged_at` on `user_ai_recipe_logs`, replacing
  the `localStorage` flag that broke whenever two generations finished out of order.
- **Multi-job pill + stalled sweep** (`47aa9c6`) — the pill now handles several concurrent
  generations (row tap acknowledges one, X acknowledges all visible). New `ai:sweep-stalled`
  command, scheduled every 5 min, fails and refunds runs nothing is going to finish.
  Job-side lock guards the race it creates, so a revived worker cannot keep a refunded recipe.
- **Profile page + AI history** (`f8a9d7b`) — `GET /recipe/ai/generations`, read-only and
  paginated. New `/profile` route with Account / AI history / Credits tabs, plus a fifth bottom-nav
  item. History shows every run regardless of acknowledgement — it is the "elsewhere" the pill
  deliberately isn't.
- **Split the profile tabs into components** (`21f8937`) — each panel owns its own fetching.

Tests: 24 passing across fail-fast, acknowledgement, sweep and history.

## Next: the remaining AI operations

Prerequisite first — `GenerateAiRecipe` still typehints `RecipeFromIngredients` directly, so it
can only ever run one operation. Needs the handler interface + registry from
`AI_PROMPT_LAYER_PLAN.md` before any of the below can exist.

Then, cheapest first:

1. **`turn_vegetarian`** — recipe in, recipe out. `source_recipe_id` already exists.
2. **`turn_vegan`** — same shape.
3. **`recipe_from_description`** — free text in, recipe out.
4. **`pantry_from_receipt`** — receipt photo → pantry items.
5. **`pantry_from_photo`** — fridge/shelf photo → pantry items.
6. **`pantry_from_voice`** — spoken list → pantry items.

1–3 reuse everything that already works. 4–6 are a separate project: file uploads (path in the job
payload, never the bytes), a cleanup story, `PantryItemsSchema`, a review screen before anything
lands in the pantry, and real per-operation credit costs.

## Also still open

- Credits tab is a placeholder — needs a balance + ledger `GET`. There is currently **no** endpoint
  that tells a user their balance; they find out by hitting a 402.
- Panel header CSS is duplicated across the three profile panels; promote to `main.css` if it grows.
- Recipe card edit/delete buttons overlap with wrong opacity.
- `RecipePage.vue` alignment on small screens.
- Recipe usage tracking + ratings (`times_cooked`, `last_cooked_at`, stars) — still unbuilt.
- Grocery list items seeder.
