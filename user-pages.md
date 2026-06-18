# User Pages Plan

## Navigation — Blob Morph from Logo Tap

Tapping the `<LogoComponent/>` in the top-right corner of `DashboardLayout.vue` triggers
a blob morph animation. The logo button itself transforms into the panel — a green circle
expands outward from the logo's position, grows to cover the right ~62% of the screen,
and the panel content fades in over it. Tapping the scrim on the left reverses everything:
the panel content fades out, the green surface contracts back into a circle, and the circle
shrinks back into the logo button.

This ties directly into the app's existing visual language: the mesh background already
has animated green blobs drifting around. The logo "becoming" one of those blobs and
then resolving into a panel feels native to the aesthetic rather than bolted on.

---

### Phase 1 — The blob grows (opening, ~350ms)

A green circle element, initially sized and positioned exactly over the logo button
(`width: 32px`, `height: 32px`, `border-radius: 50%`, anchored to `top-right`), begins
expanding via `transform: scale(N)` with `transform-origin: top right`. The scale factor
N is calculated so the circle's diameter covers the full panel area (~62% screen width,
full height). At the same time:

- `border-radius` transitions from `50%` toward `16px` — the circle morphs into a
  rounded rectangle as it grows, so the final shape matches the panel corners.
- The main shell (`dashboard-shell`) receives a subtle `scale(0.97)` and a
  `filter: blur(3px)` — same scale-down trick as the Actions hub, but with blur added
  to push depth.
- A dark scrim (`rgba(6, 44, 33, 0.22)`) fades in over the left portion of the screen
  (everything outside the panel area) simultaneously.

Easing: `cubic-bezier(0.16, 1, 0.3, 1)` — fast start, spring-like settle. Duration ~350ms.

---

### Phase 2 — Content fades in (staggered, starts ~200ms into Phase 1)

While the blob is still expanding (not after), the panel content begins fading in with a
short delay so it arrives just as the blob finishes settling. Items stagger in from top
to bottom with ~40ms between each:

1. Avatar circle + name + email (~200ms into blob expand)
2. Navigation rows, one by one
3. Sign out row last

Each item enters with `opacity: 0 → 1` and a tiny `translateY(6px) → translateY(0)`.
Duration per item: ~180ms. This cascade makes the panel feel alive rather than a static
surface that just appears.

---

### Phase 3 — Closing (reverse, ~250ms)

Tap outside the panel:

1. Panel content fades out all at once (no reverse stagger, ~120ms) — fast so it doesn't
   feel sluggish.
2. Scrim fades out simultaneously.
3. The blob contracts: `scale(N) → scale(1)`, `border-radius: 16px → 50%`. Duration ~250ms,
   eased with `cubic-bezier(0.4, 0, 1, 1)` (accelerating out — snappier than the open).
4. Main shell scale and blur reverse as the blob shrinks.

The logo button is visually indistinguishable from the blob's final contracted state, so
the animation reads as the panel "returning into" the logo.

---

### Logo state while open

While the panel is open the logo button itself is hidden (opacity 0, covered by the blob
element). When the blob contracts back it crossfades into the logo button restoring to
full opacity. This avoids any visual double — there's only ever one green circle on screen.

---

### Implementation sketch

```
DashboardLayout state:
  isUserPanelOpen: boolean

Elements:
  <LogoComponent/>        — the trigger button, fades out when panel opens
  <div class="blob"/>     — the green expanding circle, absolutely positioned,
                            transform-origin: top right, z-index: 30
  <Transition>            — wraps panel content, fades + staggers in
    <UserPanel/>          — the actual nav drawer content, z-index: 31
  </Transition>
  <div class="scrim"/>    — covers left portion, z-index: 29, click to close

Blob sizing:
  closed:  width/height = logo button size, border-radius: 50%, scale(1)
  open:    scale(N) where N = panelWidth / blobDiameter, border-radius: 16px
```

The blob element lives outside `<UserPanel/>` so it can animate independently of the
content fade. The panel content sits in a fixed-position container over the blob at a
higher z-index.

---

### Drawer structure

At the top: avatar initials circle + user name + email (non-interactive, just identity).
Below: a vertical list of navigation rows, one per destination. Each row has a small icon
tile on the left, a label, and optionally a badge (e.g. unread count on Notifications).
Bottom of the drawer: "Sign out" row, visually separated by a thin divider.

### Drawer width

~62% of screen width, capped at `22rem` on wider phones. The visible blurred portion on
the left communicates "the app is still behind here."

---

## Pages

### Profile

The user's personal info. Shows and allows editing of:
- Display name
- Email address (read-only, since it's the auth identifier)
- Profile avatar / initials color picker
- Language / locale preference (if relevant later)

Single-column form layout, same frosted-glass card style used throughout the app.
Save button at the bottom, sticky.

---

### Settings

App-wide preferences. Grouped into sections with dividers:

**Appearance**
- Theme toggle (light / system / dark — for whenever dark mode lands)

**Data**
- Default storage space (which pantry to pre-select when adding items)
- Expiry warning threshold (how many days before expiry to show a warning badge)

**Pantry**
- Low-stock alert threshold per category (optional, future-safe section header for now)

Each setting uses the appropriate control — toggle switches, segmented pickers, or
steppers — matching the green accent palette.

---

### Notifications

Two parts:

**In-app notification feed**
A chronological list of past notifications (item expiring soon, grocery list shared with
you, recipe suggestion, etc.). Each item has an icon, a short message, a relative
timestamp, and a read/unread state. Unread items have a subtle left-border accent.
A "Mark all as read" button sits at the top when there are unread items.

**Notification preferences**
Below the feed (or as a sub-section via a tab / toggle): per-category toggles for which
events generate a notification. E.g.:
- Expiry warnings — on/off + how many days in advance
- Shared list activity — on/off
- Recipe suggestions — on/off

---

## What is NOT in scope (for now)

- Subscription / billing page (deferred, no payment provider integrated yet)
- Push notification permissions (can be added to the Notifications preferences page later)
- Social / sharing profile (future)
