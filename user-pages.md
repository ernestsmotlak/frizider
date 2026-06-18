# User Pages Plan

## Navigation — Right Drawer from Logo Tap

Tapping the `<LogoComponent/>` in the top-right corner of `DashboardLayout.vue` opens a
drawer that slides in from the right edge and stops roughly in the middle of the screen
(covering ~60–65% of the viewport width). The rest of the app behind it blurs and dims.
Tapping anywhere on the blurred area closes the drawer and reverses the animation.

### How the animation works

- The main shell (`dashboard-shell`) scales down slightly and blurs — same idea as the
  `scale-[0.985]` trick already used for the Actions hub, but this time combined with a
  `blur` filter to push focus fully onto the drawer.
- The drawer itself transitions in with `transform: translateX(100%) → translateX(0)`,
  spring-eased (`cubic-bezier(0.16, 1, 0.3, 1)`), matching the existing panel animations.
- A semi-transparent scrim with `backdrop-filter: blur(4px)` sits between the shell and
  the drawer (z-index between them) to produce the frosted-blur effect on the content behind.
- The logo gets a subtle green ring while the drawer is open, the same treatment the
  Actions button already gets.

### Drawer structure

At the top: avatar initials circle + user name + email.
Below that: a vertical list of navigation rows, one per destination.
Each row has a small icon tile on the left, a label, and optionally a badge (e.g. unread
notification count on Notifications).
The bottom of the drawer has a "Sign out" row, visually separated.

### Drawer width

Targets ~62% of the screen width, capped at `22rem` on wider phones. This leaves enough
of the blurred content visible on the left to communicate "the app is still there."

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
