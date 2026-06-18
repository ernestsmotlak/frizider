# Future Ideas

## 1. Recipe Usage Tracking & Ratings
When a cooking session finishes, increment `times_cooked` + `last_cooked_at` on the recipe and let users leave a star rating. Unlocks "Most Cooked" and "Top Rated" recipe sections. Scoped clearly — 2 migration columns, 2 small endpoints, star-rating UI, hook into existing cooking session finish flow.

## 2. Expiry / Low-Stock Alerts
Surface fridge items that are expiring soon or running low as a notification badge or a dedicated "Expiring Soon" list. Core to the fridge-tracker use case. Low effort on the data model if items already carry quantity/expiry fields.

## 3. Meal Planning / Weekly View
A simple weekly calendar where you assign recipes to days. Could auto-generate a shopping list from planned meals. Bridges the recipe and shopping features together — the biggest leap but ties everything in the app together.

## 4. Nutrition / Macros Summary
Aggregate calories/protein/carbs per recipe or per day, derived from recipe ingredients and quantities. Higher effort, high value if the user tracks diet.

## 5. UI Polish (quick wins)
- Recipe card edit/delete buttons overlap/opacity bug
- RecipePage responsive alignment on small screens
