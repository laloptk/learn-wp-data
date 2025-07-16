# 🗂️ LearnWPData

A lightweight **WordPress plugin** to master **Databases & Data Management** the right way:  
- Custom tables from the start  
- Clean, reusable **OOP architecture**  
- REST API endpoints  
- Dynamic Gutenberg blocks  
- Built as the first building block of a **small reusable framework** for future plugins  

---

## 🚀 High-Level Goals

✅ **Master custom database design** in WordPress  
✅ **Practice data relationships** (notes → users, optional categories)  
✅ Build a **clean CRUD layer** using OOP + PSR-4 autoloading  
✅ Expose data through a **REST API**  
✅ Render it dynamically with a **Gutenberg block**  
✅ Lay the groundwork for a **mini framework for DB & data manipulation**  

---

## 🏗️ Project Scope

LearnWPData will let users create, view, and manage **notes** stored in a custom DB table.  

Core features:  
1. **Custom DB tables**
   - `wp_lwpd_notes` → stores notes (id, user_id, title, content, status, created_at, updated_at)
   - (Optional) `wp_lwpd_notes_meta` → future extensibility
   - (Optional) `wp_lwpd_notes_categories` → simple relations

2. **CRUD Layer**
   - A dedicated `NotesRepository` class to handle all DB operations
   - Future-proof: designed to be reusable for other plugins

3. **REST API**
   - `/wp-json/learnwpdata/v1/notes`
   - GET, POST, DELETE basic endpoints

4. **Gutenberg Block**
   - Dynamic block showing the current user’s notes
   - Uses REST API under the hood

---

## 🧱 Mini Framework Approach

This plugin is **Phase 1 of a reusable DB & Data Manipulation framework**.  
It will establish:

- **Base classes** for creating and managing custom tables  
  - `BaseTable` → handles table creation, schema versioning, and upgrades  
  - `BaseRepository` → generic CRUD operations  
- **Interfaces/Traits** for reusable patterns  
- Consistent **naming conventions** for future tables  

Future plugins will reuse these **base classes** for faster development.

---

## 📂 Folder Structure

learn-wp-data/
│
├── includes/ # PHP backend (PSR-4 autoload)
│ ├── Framework/ # Base classes (Table, Repository)
│ ├── Notes/ # Plugin-specific Notes logic
│ └── Plugin.php # Main bootstrap
│
├── src/ # JS (Gutenberg blocks, REST integration)
│ ├── blocks/
│ └── api/
│
├── build/ # Compiled JS (using @wordpress/scripts)
│
├── learn-wp-data.php # Main plugin file
└── README.md # This file


---

## 🛠️ Tech & Standards

- **PHP 7.4+** with PSR-4 autoloading (Composer)
- **@wordpress/scripts** for JS bundling
- **OOP & design principles**
  - Single Responsibility Principle for classes
  - Clear separation of DB logic vs business logic vs presentation
- **YAGNI-friendly** → keep it simple, scalable later

---

## 🗺️ Roadmap

### ✅ Phase 1: DB Foundations
- Define DB schema (`wp_lwpd_notes`)
- Create `BaseTable` + `NotesTable` class for table creation
- Create `BaseRepository` + `NotesRepository` for CRUD
- Add simple activation hook for table install

### 🔄 Phase 2: REST API
- Register `/learnwpdata/v1/notes` routes
- Connect endpoints to `NotesRepository`

### 🔄 Phase 3: Gutenberg Block
- Create a dynamic block listing user notes
- Fetch data via REST API

### 🔄 Phase 4: Relations & Status
- Add optional categories table
- Add `status` (active/archived) column
- Implement filtering in API & block

### 🔄 Phase 5: Framework Extraction
- Refactor base classes into a reusable `Framework` namespace
- Document how to extend for future plugins

---

## 📌 Future Ideas

- Async operations (queue-ready hooks)
- Caching layers
- More complex data relations
- Reusable abstract `BaseController` for REST endpoints

---

## 🧩 Why This Matters

LearnWPData is not just for managing notes—it’s a **learning and framework-building project** to:

- Master **WordPress DB best practices**
- Understand **scaling patterns**
- Build a **clean, reusable architecture** for future plugins

---

## 💡 Quick Start

1. Clone the repo  
2. `composer dump-autoload` for PSR-4  
3. `npm install && npm start` for JS  
4. Activate the plugin → tables auto-create on activation  

---

**This is the foundation for your future WordPress DB framework.**
