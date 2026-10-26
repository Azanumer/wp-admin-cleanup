# Admin Cleanup

A small WordPress plugin for handing a site over to a client (or an editor team) without the noisy default admin getting in the way.

## What it declutters

- WordPress logo in the admin bar
- Comments menu item (commenting itself keeps working — only the menu is hidden)
- Dashboard welcome panel, Quick Draft widget, and (optionally) the Activity widget
- Front-end admin bar for non-admin roles (optional)
- Admin footer credit — replace it with your agency/client text, or leave it empty for the WordPress default

Everything is a checkbox under **Settings → Admin Cleanup**, so nothing is permanently removed: uncheck and it comes back. No settings are stored per-user and no core files are touched.

## Install

Upload the plugin folder to `wp-content/plugins/` and activate it, then configure it under **Settings → Admin Cleanup**.

## Uninstall

Removes the single `wac_settings` option. All hidden items return to their WordPress defaults.

## License

MIT
