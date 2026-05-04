# CourseTransit

Sell Moodle™ courses using WordPress + WooCommerce — without custom integrations.

CourseTransit is a bridge plugin that connects Moodle LMS with WordPress (WooCommerce), enabling automated course sales, user creation, and enrollments.

---

## Features

- WooCommerce → Moodle enrollment sync  
- Automatic user creation in Moodle  
- Secure API-based communication  
- Instant enrollment after purchase  
- Lightweight and developer-friendly  

---

## How It Works

1. User purchases a course via WooCommerce  
2. Plugin captures the order  
3. User is created or matched in Moodle  
4. Enrollment is triggered via Moodle API  
5. User gets access instantly with email notifications 

---

## Requirements

### WordPress

- WordPress 6.x+
- WooCommerce 7.x+

### Moodle

- Moodle 3.9+ (recommended 4.x)
- Web Services enabled
- API token access

---

## Installation

### Moodle (Auth Plugin)

#### Method 1: Install via UI

1. Go to:
   Site administration → Plugins → Install plugins

2. Upload the plugin ZIP file

3. Click "Install plugin from the ZIP file"

4. Follow on-screen steps

5. After installation, go to:
   Site administration → Plugins → Authentication → Manage authentication

6. Enable **CourseTransit**

---

#### Method 2: Manual Installation

1. Extract plugin and copy to:
   /auth/coursetransit

2. Set correct permissions

3. Visit:
   Site administration → Notifications

4. Complete installation

5. Enable plugin:
   Site administration → Plugins → Authentication → Manage authentication

### WordPress Plugin

1. Upload plugin via:
   wp-admin → Plugins → Add New → Upload

2. Activate plugin

3. Configure:
   - Moodle API URL  
   - Web service token  
   - Course mappings  

---

## Use Cases

- Sell Moodle courses via WooCommerce  
- Paid LMS platforms  
- Course marketplaces  

---


## Contributing

Pull requests are welcome. Open an issue first for major changes.

---

## Disclaimer

This plugin is not affiliated with or endorsed by Moodle Pty Ltd or the WordPress Foundation.

---

## License

This project is licensed under the GNU General Public License v3.0.

See: https://www.gnu.org/licenses/gpl-3.0.html