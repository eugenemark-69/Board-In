# Config notes

This folder contains configuration files used by the application.

Important: `config/session.php` starts the session and also requires `includes/functions.php`, which provides common helper functions such as `esc()`, `esc_attr()`, `old()`, `redirect()`, and flash helpers.

Make sure to include `config/session.php` at the top of any page that needs session state or helper functions.