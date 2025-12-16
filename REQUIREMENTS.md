# Food & Recipe Tracker - Requirements

## Overview
A sleek, intuitive app for managing food at home, recipes, and grocery shopping to prevent waste and simplify cooking.

**Tech Stack:** Laravel (Backend) + Nuxt (Frontend)

## Core Features

### 1. Recipes Management
- **CRUD Operations:** Create, read, update, delete recipes
- **Recipe Structure:**
  - Name, category (optional), preparation steps, image (optional)
  - Ingredients list with free-form units (e.g., "1 cup rice", "½ onion")
  - Optional tags (vegetarian, gluten-free, etc.)
  - Favorites marking
- **Advanced Features:**
  - Filter by date, alphabetical, favorites, urgency, tags
  - Recipe suggestions based on selected pantry ingredients
  - Automatic pantry deduction when recipe is marked as cooked

### 2. Pantry Management
- **Inventory Tracking:**
  - Name, quantity, free-form units, expiration date
  - Optional tags (vegetable, dairy, protein)
  - Storage space organization
- **Operations:**
  - Manual add/edit/remove items
  - Update quantities and expiration dates
  - Expiry highlighting and notifications
  - Search/filter by tag, expiration, name
- **Automation:**
  - Push notifications for expiring items
  - Auto-updates from completed grocery lists

### 3. Grocery Lists (MVP Focus)
- **List Management:**
  - Create, view, edit, delete grocery lists
  - List completion tracking
- **Item Management:**
  - Manual item addition
  - Import ingredients from recipes
  - Pantry availability checking during creation
  - Purchase status tracking
- **Completion Workflow:**
  - Mark list as completed
  - Confirm purchased items (checklist style)
  - Set quantity and expiration for bought items
  - Automatic pantry inventory updates

## Additional Features

### Smart Suggestions
- Recipe suggestions from pantry ingredients
- Urgent cooking filter (expiring ingredients)
- Similar recipe recommendations

### Future Features
- Food waste tracking and analytics
- Receipt scanning with AI parsing
- Grocery store integration and price comparison
- Perpetual pot concept (leftover ingredient reuse)

## Technical Requirements

### Backend & Database
- Laravel backend with scalable database design
- Single-user MVP, multi-user ready
- Offline support for core functionality
- System-level push notifications

### Architecture
- RESTful API design
- Data synchronization for offline/online
- Extensible for future multi-user authentication

## UI/UX Guidelines

### Design Principles
- Sleek, modern, minimalistic interface
- Fast navigation between Recipes, Pantry, Grocery Lists
- Optimized for repetitive tasks (quick item addition)
- Highlight urgent tasks (expiring items)

### User Experience
- Free-form input support (flexible units, optional fields)
- Intuitive workflows (recipe → shopping → pantry)
- Accessibility considerations

## MVP Scope (Shopping Lists First)

### Phase 1: Shopping Lists
1. Grocery list CRUD operations
2. Shopping item management
3. Recipe ingredient import
4. Purchase tracking and pantry updates
5. Basic pantry integration

### Phase 2: Recipes & Pantry
1. Recipe management system
2. Pantry inventory tracking
3. Smart suggestions and automation

### Phase 3: Advanced Features
1. Push notifications
2. Offline support
3. Waste tracking and analytics

## Database Schema Overview

### Core Tables (Current)
- `users` - User accounts
- `spaces` - Storage locations (fridge, pantry, etc.)
- `pantry_items` - Inventory items
- `recipes` - Recipe storage
- `recipe_ingredients` - Recipe components
- `grocery_lists` - Shopping lists
- `grocery_list_items` - Shopping list items

### Key Relationships
- User owns all resources
- Spaces contain pantry items
- Recipes have ingredients
- Grocery lists have items
- Shopping items can link to pantry items after purchase

### No Pivot Tables Required
- Direct foreign key relationships only
- Clean, normalized schema for core functionality




