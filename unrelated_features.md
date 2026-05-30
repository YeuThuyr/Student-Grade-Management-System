# Auxiliary & Unrelated Features Inventory

This document lists all application pages, features, and scripts that exist in the codebase but were not explicitly described or required in the core system specifications.

These features have been **fully preserved** in the application and updated to remain compatible with standard database and authentication libraries.

---

## 1. Class Management Module (Lớp Chuyên ngành)

This module provides complete administrative CRUD functionality for academic classes and departments, which is crucial for assigning students to specific courses of study.

| File Path | Role / Functionality | Details |
|:---|:---|:---|
| `classes/list.php` | Class Listing | Displays a searchable directory of academic classes with their unique codes, titles, and descriptions. |
| `classes/add.php` | Create Class | Form to create a new class. Validates unique class codes (e.g. `CNTT-01`). |
| `classes/edit.php` | Edit Class | Form to modify class details such as department names or descriptions. |
| `classes/delete.php` | Delete Class | Deletes a class record. Handled by relational foreign key rules (sets matching student class references to `NULL` to avoid orphans). |

---

## 2. Subject Management Module (Môn học / Học phần)

Provides CRUD controls for academic courses, managing their credits (e.g. 2, 3, 4 credits) which directly power the new weighted GPA engine.

| File Path | Role / Functionality | Details |
|:---|:---|:---|
| `subjects/list.php` | Subject Directory | Lists all subjects currently active in the database. |
| `subjects/add.php` | Create Subject | Allows administrators to create subjects specifying their subject code, credits, and descriptions. |
| `subjects/edit.php` | Edit Subject | Modifies credit hours, descriptions, or names of existing courses. |
| `subjects/delete.php` | Delete Subject | Cascades subject deletion to related student grades to maintain database normalization. |

---

## 3. General Pages & Utility Helpers

Additional user interface elements and helper scripts.

| File Path | Role / Functionality | Details |
|:---|:---|:---|
| `contact.php` | Contact Support | A beautifully styled informational support request page with HUST branding. Includes mock email submission logic. |
| `get_filter_values.php` | Filter API | Auxiliary JSON API endpoint to fetch list of classes and semesters for search suggestions. |
| `tmp_verify.php` | Local Verifier | Stored locally (now ignored in git) to test password crypt hashing verifications during development. |
