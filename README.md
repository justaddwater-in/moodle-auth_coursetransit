# CourseTransit for Moodle™

The Moodle™ companion plugin for [CourseTransit](https://justaddwater.in/products/coursetransit-wordpress-moodle-integration/) — the WordPress + WooCommerce integration that lets you sell Moodle™ courses online and enroll students automatically after purchase.

This authentication plugin (`auth_coursetransit`) runs on your Moodle™ site and establishes a secure, token-based connection with the CourseTransit WordPress plugin. Once connected, WordPress can sync your courses, create or match users, and enroll students in Moodle™ the moment a WooCommerce order is completed.

> This is the Moodle™ side of the integration. It works together with the [CourseTransit WordPress plugin](https://justaddwater.in/products/coursetransit-wordpress-moodle-integration/), which you install on your WordPress site.

---

## What This Plugin Does

- Establishes a secure, token-based connection between Moodle™ and WordPress
- Registers one or more WordPress sites against your Moodle™ instance
- Supports course synchronization requested from WordPress
- Enables automatic user creation and matching in Moodle™
- Enables automatic enrollment triggered by WooCommerce orders
- Communicates over Moodle™'s native web services API with authenticated tokens

---

## How It Works

1. A learner purchases a course on your WordPress site via WooCommerce
2. The CourseTransit WordPress plugin captures the completed order
3. WordPress calls your Moodle™ site securely using its assigned token
4. The user is created or matched in Moodle™
5. The learner is enrolled and gets instant access, with an enrollment email

---

## Requirements

### Moodle™

- Moodle™ 4.1+ (4.x recommended)
- Web services enabled
- API token access

### WordPress (companion)

- WordPress 6.0+
- WooCommerce (installed and active)
- The [CourseTransit WordPress plugin](https://justaddwater.in/products/coursetransit-wordpress-moodle-integration/)

---

## Installation

### Method 1: Install via the Moodle™ UI

1. Go to **Site administration → Plugins → Install plugins**
2. Upload the plugin ZIP file
3. Click **Install plugin from the ZIP file**
4. Follow the on-screen steps to complete installation

### Method 2: Manual Installation

1. Extract the plugin and copy it to `/auth/coursetransit`
2. Set the correct file permissions
3. Visit **Site administration → Notifications**
4. Complete the installation

### After Installing

1. Open the CourseTransit settings in Moodle™ and launch the setup wizard
2. Select the API user the integration will act as (a dedicated administrator account is recommended)
3. Register your WordPress site by entering its name and URL
4. Generate a secure site token
5. Enter your Moodle™ URL and the generated token in the CourseTransit WordPress plugin
6. Start syncing courses from WordPress

---

## Connecting Multiple WordPress Sites

CourseTransit supports connecting multiple WordPress sites to a single Moodle™ instance. Each registered site is bound to its own dedicated web-service token, so one site's token cannot be used to act as another. Retrieve a site's token any time from **Sites → Show token** in the CourseTransit dashboard.

---

## Use Cases

- Selling Moodle™ courses through WooCommerce
- Paid LMS platforms and online academies
- Course marketplaces and multi-brand storefronts

---

## Changelog

### 2.0

- Aligned versioning and compatibility with CourseTransit 2.0 and the Pro extension add-on
- Stability, security, and compatibility improvements

### 0.2.1

- Each registered WordPress site is now bound to a dedicated Moodle™ web-service token, while the existing `wstoken`, `wsfunction`, and payload request contract stays unchanged. Once the token identifies the registered site, `payload.siteurl` is checked only for an exact host consistency match and is never used to select or grant another site's permissions — so a token issued for Site A cannot be configured with Site B's URL and used as Site B.
- On upgrade, installations with more than one registered site assign the existing token to the oldest site and generate unique tokens for the remaining sites. Those sites must update their WordPress connection token once (via **Sites → Show token**). Single-site installations continue using their existing token.
- The telemetry client no longer disables TLS certificate verification.

---

## Contributing

Pull requests are welcome. Please open an issue first for major changes.

---

## Disclaimer

This plugin is not affiliated with or endorsed by Moodle Pty Ltd or the WordPress Foundation.

---

## License

GPL v3 or later

This project is licensed under the GNU General Public License v3.0. See: https://www.gnu.org/licenses/gpl-3.0.html

---

## Author

Built by [Justaddwater](https://justaddwater.in/) — an enterprise eLearning development company.