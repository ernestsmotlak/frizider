# Ideas & Future Enhancements

## UI/UX Ideas

### Fridge Icon Instead of Hamburger Menu
Instead of using a traditional hamburger menu icon, use a fridge icon that can toggle between:
- **Closed fridge icon** - When the navigation menu is closed/collapsed
- **Open fridge icon** - When the navigation menu is open/expanded

This would be thematically appropriate for a refrigerator inventory management app and adds a fun, on-brand touch to the UI.

**Implementation notes:**
- The icon state changes based on menu open/closed state
- Could use SVG icons for better scalability
- Animation between states could be smooth (door opening/closing effect)

## Mobile Navigation & Shopping Lists UX Decision

### Bottom Navigation Bar
Primary navigation structure:
- **Pantry** | **Shopping** | **Recipes** | (Optional) **Profile/Stats**

### Shopping List Page Structure
**Top bar:**
- Shopping ▾ (current list selector) + Add / Search / Filter

**Inside the page:**
- Chips/tabs for: **All** / **Needed** / **In Cart** / **Bought** (these scale nicely)

**Note:** Final layout and flows depend on whether users will primarily have:
- One list most of the time, OR
- Multiple lists (weekly + stores + events)
