OXFORD HOUSE CENTRAL LOGIN SYSTEM
================================

- full login screen
- house users
- central admins
- permission-based house access
- cross-house reporting panels
- central audit log
- user and access manager

MAIN NEW FILES
--------------
- login.php
- logout.php
- access_denied.php
- users_admin.php
- central_admin.php
- extras/master_config.php
- extras/sql/oxford_central_install.sql

DEFAULT LOGIN ACCOUNTS
----------------------

House Access
- Email: [housename]@oxfordhouse.us
- Password: Recovery[HOUSENUMBER]! 

HOW ACCESS WORKS
----------------
- Every person logs in through login.php
- House users only see houses assigned to them
- Central admins can switch between all houses
- The sidebar house dropdown only shows permitted houses
- Central dashboard and user manager are restricted to central admins

AUDIT LOGGED EVENTS
-------------------
- login success
- login failure
- logout
- page opened
- house switched
- POST form requests across the folder
- user creation and access changes

NOTES
-----
- Each house still uses its own database for separation
- The master database is oxford_central
- Cross-house panels in central_admin.php summarize records from known form tables across all house databases
- Existing form pages keep using their current individual tables and layouts


Added in this build:
- Central admin can create a new house from the dashboard
- Creating a house now creates its dedicated MySQL database
- The system clones the current template table structure into the new house database
- The create-house form also creates the initial house manager login
- Central admin links and panels stay hidden from non-central users
- House creation is written into the audit log
