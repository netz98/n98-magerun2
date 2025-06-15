# Functional Tests for View Handling in db:drop and db:dump

This document outlines the manual functional tests for verifying the new view-handling capabilities in `db:drop` and `db:dump` commands.

**Prerequisites:**
*   A working Magento 2 instance.
*   `n98-magerun2` installed and configured for the Magento instance.
*   MySQL client tools available for database inspection and dump import.
*   Ability to create and drop databases, tables, and views in your MySQL environment.

## Common Setup Steps:

For many tests, you'll need a database with specific tables and views.

**Database Schema Creation SQL:**
```sql
-- Create dummy tables
CREATE TABLE IF NOT EXISTS test_table1 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS test_table2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT
);

-- Populate with some data (optional, but good for verifying dumps)
INSERT INTO test_table1 (data) VALUES ('Sample data 1'), ('Sample data 2');
INSERT INTO test_table2 (description) VALUES ('Description for T2-1'), ('Description for T2-2');

-- Create dummy views
-- Note: Adjust view definitions if 'core_config_data' or other tables don't exist or are empty in your test env.
-- A simple view on a known table is sufficient.
CREATE OR REPLACE VIEW test_view1 AS SELECT id, data FROM test_table1 WHERE id = 1;
CREATE OR REPLACE VIEW test_view2 AS SELECT description FROM test_table2 LIMIT 1;

-- Example view for dump tests that might be part of a @stripped group
CREATE OR REPLACE VIEW test_view_stripped AS SELECT * FROM test_table1 LIMIT 1;

-- Verify creation (optional)
-- SHOW FULL TABLES;
-- SELECT * FROM test_view1;
-- SELECT * FROM test_view2;
```

**Cleanup SQL (after each test case if needed):**
```sql
DROP VIEW IF EXISTS test_view1;
DROP VIEW IF EXISTS test_view2;
DROP VIEW IF EXISTS test_view_stripped;
DROP TABLE IF EXISTS test_table1;
DROP TABLE IF EXISTS test_table2;
-- To drop the entire database (if the test case requires it):
-- DROP DATABASE your_magento_db_name;
-- CREATE DATABASE your_magento_db_name;
-- USE your_magento_db_name;
```

Remember to replace `your_magento_db_name` with the actual name of your Magento test database.

---

## I. `db:drop` Command Tests

### Test Case D1: Drop Views Only

1.  **Setup:**
    *   Execute the "Database Schema Creation SQL" above in your Magento database.
    *   Verify that `test_table1`, `test_table2`, `test_view1`, and `test_view2` exist.
2.  **Command:**
    ```bash
    n98-magerun2 db:drop --drop-views --force
    ```
3.  **Expected Outcome:**
    *   `test_view1` and `test_view2` are dropped.
    *   `test_table1` and `test_table2` still exist.
4.  **Verification:**
    *   Connect to the database and check:
        *   `SHOW TABLES LIKE 'test_view1';` (should return empty set)
        *   `SHOW TABLES LIKE 'test_view2';` (should return empty set)
        *   `SELECT COUNT(*) FROM test_table1;` (should return original count)
        *   `SELECT COUNT(*) FROM test_table2;` (should return original count)

### Test Case D2: Drop Tables and Views

1.  **Setup:**
    *   Execute the "Database Schema Creation SQL" above.
    *   Verify tables and views exist.
2.  **Command:**
    ```bash
    n98-magerun2 db:drop --tables --drop-views --force
    ```
3.  **Expected Outcome:**
    *   `test_table1`, `test_table2`, `test_view1`, and `test_view2` are all dropped.
4.  **Verification:**
    *   Connect to the database and check:
        *   `SHOW TABLES LIKE 'test_view1';` (empty)
        *   `SHOW TABLES LIKE 'test_view2';` (empty)
        *   `SHOW TABLES LIKE 'test_table1';` (empty)
        *   `SHOW TABLES LIKE 'test_table2';` (empty)

### Test Case D3: Drop Entire Database (includes views)

1.  **Setup:**
    *   Execute the "Database Schema Creation SQL" above.
    *   Verify tables and views exist.
2.  **Command:**
    ```bash
    n98-magerun2 db:drop --force
    ```
3.  **Expected Outcome:**
    *   The entire database is dropped.
4.  **Verification:**
    *   Attempting to connect to the database or listing tables should fail or show an empty database (depending on your MySQL client and if `n98-magerun2` recreates it).
    *   If `n98-magerun2 db:info` is run, it should indicate the database doesn't exist or has no tables.

---

## II. `db:dump` Command Tests (using `mysqldump` path)

**Note:** For these tests, ensure `mydumper` is not installed or not prioritized, so `mysqldump` is used. You might need to temporarily uninstall `mydumper` or use a specific n98-magerun2 configuration if it defaults to `mydumper`.

**Setup for Dump Tests:**
*   Execute the "Database Schema Creation SQL" in your Magento database.
*   Create a separate, empty database (e.g., `test_import_db`) for importing and verifying the dumps.

### Test Case P1: Default Dump (should include views)

1.  **Command:**
    ```bash
    n98-magerun2 db:dump dump_default.sql.gz --compression="gz"
    ```
2.  **Verification:**
    *   Unzip `dump_default.sql.gz` to `dump_default.sql`.
    *   Import `dump_default.sql` into `test_import_db`.
    *   In `test_import_db`:
        *   Check `test_table1` exists and has data.
        *   Check `test_view1` exists and can be queried (e.g., `SELECT * FROM test_view1;`).
        *   Inspect `dump_default.sql` content:
            *   It should contain `CREATE TABLE test_table1 ...;`
            *   It should contain `INSERT INTO test_table1 ...;`
            *   It should contain `CREATE ALGORITHM=UNDEFINED DEFINER=... SQL SECURITY DEFINER VIEW \`test_view1\` AS SELECT ... FROM \`test_table1\` ...;` (or similar view creation syntax).

### Test Case P2: Dump with `--no-views`

1.  **Command:**
    ```bash
    n98-magerun2 db:dump --no-views dump_no_views.sql.gz --compression="gz"
    ```
2.  **Verification:**
    *   Unzip and import `dump_no_views.sql` into `test_import_db` (after clearing it).
    *   In `test_import_db`:
        *   `test_table1` should exist and have data.
        *   `test_view1` should NOT exist. Trying `SELECT * FROM test_view1;` should result in an error (table/view not found).
    *   Inspect `dump_no_views.sql` content:
        *   It should contain `CREATE TABLE test_table1 ...;`
        *   It should NOT contain any `CREATE VIEW test_view1 ...;` statement.

### Test Case P3: Dump with `--views` (explicit include)

1.  **Command:**
    ```bash
    n98-magerun2 db:dump --views dump_with_views.sql.gz --compression="gz"
    ```
2.  **Verification:** (This should behave like the default dump)
    *   Unzip and import `dump_with_views.sql` into `test_import_db`.
    *   In `test_import_db`:
        *   `test_table1` exists and has data.
        *   `test_view1` exists and can be queried.
    *   Inspect `dump_with_views.sql`: It should contain the `CREATE VIEW test_view1 ...;` statement.

### Test Case P4: `--no-views` with `--strip="@stripped"` (view in stripped group)

1.  **Setup:**
    *   Ensure `test_view_stripped` is created from the common setup.
    *   Configure a table group `@stripped` in your n98-magerun2 config (e.g., `config/commands/db.yaml` or global config) to include `test_view_stripped`. Example:
        ```yaml
        commands:
          N98\Magento\Command\Database\DumpCommand:
            table-groups:
              - id: "stripped"
                description: "Tables to be stripped for development"
                tables:
                  - "test_view_stripped" # This is a view
                  - "another_table_to_strip"
        ```
2.  **Command:**
    ```bash
    n98-magerun2 db:dump --strip="@stripped" --no-views dump_strip_no_views.sql.gz --compression="gz"
    ```
3.  **Verification:**
    *   Unzip and import `dump_strip_no_views.sql` into `test_import_db`.
    *   In `test_import_db`:
        *   `test_view_stripped` should NOT exist.
    *   Inspect `dump_strip_no_views.sql`:
        *   It should NOT contain `CREATE VIEW test_view_stripped ...;` (neither structure nor data, as views don't have data in the same way tables do, and `--no-views` should prevent its definition from being dumped).
        *   If `another_table_to_strip` was an actual table, its structure (`CREATE TABLE`) should be present, but no `INSERT INTO`.

### Test Case P5: `--no-views` with explicit table list that includes a view name

1.  **Setup:** (No special group needed)
2.  **Command:** (This scenario tests if `--no-views` overrides an explicit mention of a view name in the general tables list for mysqldump if that were possible. However, mysqldump typically takes table names, and views are implicitly included unless ignored. The `--no-views` should add all views to the ignore list.)
    ```bash
    n98-magerun2 db:dump --no-views test_table1 test_view1 dump_explicit_list_no_views.sql.gz --compression="gz"
    ```
    *(Note: `mysqldump` usually takes table names after options. If `test_view1` is passed as a "table to dump" and `--no-views` is also passed, the view should still be excluded due to `--no-views` adding it to the ignore list.)*
3.  **Verification:**
    *   Unzip and import `dump_explicit_list_no_views.sql` into `test_import_db`.
    *   In `test_import_db`:
        *   `test_table1` should exist.
        *   `test_view1` should NOT exist.
    *   Inspect `dump_explicit_list_no_views.sql`: It should not contain `CREATE VIEW test_view1 ...;`.

### Test Case P6: Default dump with `--strip="test_view1"`

1.  **Command:**
    ```bash
    n98-magerun2 db:dump --strip="test_view1" dump_strip_view.sql.gz --compression="gz"
    ```
2.  **Verification:**
    *   Unzip and import `dump_strip_view.sql` into `test_import_db`.
    *   In `test_import_db`:
        *   `test_view1` should exist (as its definition should be dumped).
        *   Querying `test_view1` should work.
    *   Inspect `dump_strip_view.sql`:
        *   It SHOULD contain the `CREATE VIEW test_view1 ...;` statement (structure). Since views don't store data themselves, stripping a view means its definition is dumped, which is the default behavior for views anyway when not excluded. The key is that it's not *excluded*.

---
*(Similar test cases should be designed for the `mydumper` path if it's a critical component, focusing on its specific options like `--ignore-table` for views when `--no-views` is used, and how it handles stripped views (mydumper might not have a direct equivalent of stripping a view's "data" as it's just a definition).)*

```
