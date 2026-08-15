# pantry_from_photo — plan

Photo of a shelf → list of pantry items, each assigned to a space. Reviewed by the user before
anything lands in the pantry. Voice version comes later and is the same thing with a different
part type and prompt.

## Scope

Output per item: `name`, optional `space_id`, optional `notes`.

**No `quantity`, `unit` or `expiry_date`** — those are exactly what a photo is worst at (occlusion
makes counting a guess; expiry dates are tiny and face away). All three are already nullable, so
no migration. Quantity gets added later through the normal pantry edit flow, not here.

Spaces are a closed set, so they go in the **schema as an enum**, not the prompt — same trick as
`RecipeSchema::get($names)`. The model cannot return a space that isn't the user's. Nullable, so an
unclear item lands unassigned rather than forced somewhere wrong. `space_storages.description` is
load-bearing: "Fridge — dairy, leftovers" assigns far better than "Fridge".

## Steps

1. **Generic job.** `GenerateAiRecipe::handle()` typehints `RecipeFromIngredients`, so it can only
   run one operation. Shared interface (`buildRequest` / `persist` / `promptVersion`) + a registry
   keyed on `$log->action`. Change `persist()` to return **columns to merge into the log** instead
   of an int: recipe returns `['recipe_id' => 12]`, pantry returns `['result_json' => [...]]`.
2. **Migration.** Nullable `result_json` on `user_ai_recipe_logs` — where items sit between the job
   finishing and the user confirming. Plus a new `AiOperation` case with its own `creditCost()`.
3. **Upload.** `POST /pantry/ai/from-photo`, multipart. Validate mime + size, store to disk, then
   copy `RecipeController.php:431` exactly: create log with `request_meta = ['photo_path' => ...]`,
   spend credit, dispatch, return 202. **Path in the payload, never the bytes** — the queue is
   `database`.
4. **`PantryFromPhoto` operation.** `buildRequest()`: `AiPart::inline()` for the image +
   `AiPart::text()` listing the user's spaces as `id, name, description`; schema enum = those ids.
   `persist()`: does **not** touch the pantry, just returns the items for `result_json`.
5. **Prompt + schema.** `resources/prompts/pantry_from_photo.md`, short, appended after `system.md`.
   No JSON or field names in it. `PantryItemsSchema::get($spaceIds)`.
6. **Review + confirm.** `GET /pantry/ai/generations/{id}` returns `result_json` + photo URL.
   `POST .../confirm` takes the **edited** list (not `result_json` — that was only a suggestion),
   re-checks each `space_id` belongs to the user, writes via `PantryIntakeService::add()`,
   acknowledges the log.
7. **Cleanup.** Delete the file when the log goes terminal — success path *and* `failed()`. Sweep
   for orphaned uploads alongside `ai:sweep-stalled`.

## Review screen

- **Group by space**, not a flat list — the assignment is the thing being checked, and a wrong one
  jumps out of a grouped list and hides in a flat one.
- One-line rows (no quantity/unit means no reason for more): tap name to edit, tap chip to move,
  X to drop. No modal.
- Included by default, easy to remove — the user is confirming, not re-entering.
- Reuse `SelectionDock` for the "Add N items" bar and `ActionSheet` for the space picker.
- Photo thumbnail at the top — they're verifying a list against a source.
- Nothing persists until confirm; state lives on the server, so backgrounding the app is safe.

## Notes

- Downscale to ~1200px on the frontend before upload. A 4MB phone photo buys nothing but tokens.
- Verify the returned `space_id` belongs to the user server-side anyway. The enum should make it
  impossible; one query means a provider quirk can't cross a user boundary.

## API contract (frontend is built, backend is not)

The frontend for all of this exists and calls these three endpoints. None of them are implemented
yet — build them to this shape and the UI works unchanged.

**`POST /api/pantry/ai/from-photo`** — multipart: `photo` (jpeg blob, already ≤1200px), plus
`idempotency_key`. Same 202 body as the recipe endpoint:
`{ generation_id, action: "pantry_from_photo", credits_remaining }`.

**`GET /api/pantry/ai/generations/{id}`**

```json
{
  "generation_id": 12,
  "status": "completed",
  "error": null,
  "photo_url": "/storage/scans/....jpg",
  "confirmed_at": null,
  "spaces": [{"id": 3, "name": "Fridge"}],
  "items": [{"name": "Milk", "space_id": 3, "notes": null}]
}
```

`spaces` rides along because the review screen needs the picker anyway, and it must be the same
list the schema enum was built from. `confirmed_at` is what stops a second tap re-adding
everything.

**`POST /api/pantry/ai/generations/{id}/confirm`** — `{ items: [{name, space_id, notes}] }`,
returns `{ added: 8 }`. The body is the **edited** list; ignore `result_json` here.

The existing `GET /api/recipe/ai/active-generations` must also return pantry runs, with `action`
set — that is how the pill picks up the job and routes to the review page.

## First slice

Skip the upload endpoint. Do steps 1, 2, 4, 5 against a local test image from a command or test.
That answers the only real unknown — whether space assignment is any good on real shelves — before
building the upload plumbing and review UI around it.
