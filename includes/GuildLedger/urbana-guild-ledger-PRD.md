```
### Product Requirements Document: Urbana Guild Ledger

**1. Overview**

The Urbana Guild Ledger is a two-stage WordPress plugin designed to be a modern, low-effort solution for logging and managing interactions with Project Partners.

**2. Goals**

* Provide a secure, private, and searchable record of all outreach and feedback conversations.
* Offer a sustainable logging solution for both the founder and the future co-founder.
* Maintain brand consistency by using a medieval Guild theme.

**3. User Stories**

* **As a founder**, I need a secure, admin-only interface to log detailed conversations and feedback.
* **As a future co-founder**, I need a simple, low-effort form to quickly log new interactions, either from the admin area or the front end.
* **As a team member**, I need to easily search and review past interactions to inform my work.

**4. Functionality (Stage 1 - MVP: WordPress Admin Only)**

This stage focuses on core, back-end functionality to be developed first.

* **Custom Post Type:**
    * Create a new Custom Post Type with the slug `urbana_ledger`.
    * The CPT should have a title field, and additional custom fields as described below.
* **WordPress Admin Menu:**
    * Add a top-level menu item to the WordPress admin sidebar called "Urbana."
    * Add a sub-menu item under "Urbana" called "Guild Ledger."
* **Data Entry Fields:** The following fields must be available on the add/edit screen for a new ledger entry:
    * **Contact Name:** Text field.
    * **Company/Council:** Text field.
    * **Interaction Type:** Dropdown select with options: "Video Call," "Phone Call," "Email."
    * **Date:** Date picker field.
    * **Notes:** A rich text editor field to record conversation details and feedback.
* **Display:**
    * The main `urbana_ledger` post list page must display all entries.
    * The display should use the modern **DataViews** style instead of the older List Tables.
    * The list should be searchable and sortable by Contact Name, Company, and Date.

**5. Functionality (Stage 2 - Future: Front-End Addition)**

This stage will be developed after the successful implementation of Stage 1.

* **Front-End Form:**
    * Create a simple form on a front-end page of the website for adding new ledger entries.
    * The form will have the same data entry fields as the WordPress admin screen.
    * The form will submit data that creates a new `urbana_ledger` CPT entry.
* **Authentication & Access Control:**
    * The front-end form must be restricted to logged-in users with a specific role (e.g., Administrator or Editor).
    * The form should not be publicly accessible to prevent security risks.

**6. Technical Specifications**

* **Plugin Name:** Urbana Guild Ledger
* **Custom Post Type Slug:** `urbana_ledger`
* **Display:** All lists and tables should use the **DataViews** framework.
```