# 🛡️ WPForms Fingerprint Protection

**Contributors:** [rohitdev](https://github.com/rohitdevwp)  
**Donate link:** [https://github.com/rohitdevwp/wpforms-fingerprint-protection](https://github.com/rohitdevwp/wpforms-fingerprint-protection)  
**Tags:** wpforms, spam, anti-spam, fingerprint, security, forms, fraud-prevention  
**Requires at least:** 5.0  
**Tested up to:** 6.7  
**Requires PHP:** 7.2  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

> Prevent spam and fake form submissions using FingerprintJS device fingerprinting. Stop bots, rate limit abusers, and protect your WPForms.

---

## 📖 Description

**WPForms Fingerprint Protection** uses advanced device fingerprinting technology to protect your WordPress forms from spam, bots, and fake submissions.  
Unlike traditional CAPTCHA solutions, this plugin works invisibly in the background without annoying your legitimate users.

---

### 🧩 Key Features

- **Device Fingerprinting:** Creates unique identifiers for each visitor based on browser and device characteristics  
- **Rate Limiting:** Automatically blocks users who submit forms too frequently  
- **Spam Detection:** Learns and blocks known spammers across sessions  
- **Admin Dashboard:** View detailed logs of all submissions with filtering options  
- **Zero User Friction:** No CAPTCHA, no extra steps — completely invisible to users  
- **99.5% Accuracy:** Powered by FingerprintJS Pro for reliable device identification  

---

### 🎯 Perfect For

- Contact forms receiving spam  
- Lead generation forms  
- Registration forms  
- Survey and feedback forms  
- Any WPForms experiencing abuse  

---

### 🚀 How It Works

1. Install the plugin and activate it  
2. Get a free API key from [fingerprint.com](https://fingerprint.com) (20,000 requests/month free)  
3. Enter your API key in the settings  
4. That’s it! Your forms are now protected ✅  

The plugin automatically:
- Tracks each visitor’s unique device fingerprint  
- Limits submissions per device (configurable)  
- Blocks known spammers automatically  
- Logs all activity for your review  

---

### 📊 What You Get

#### **Settings Page**
- Easy API key configuration  
- Customizable rate limits  
- Adjustable spam thresholds  
- Real-time protection statistics  

#### **Logs Dashboard**
- View all submissions  
- Filter by status (allowed, blocked, spam, suspicious)  
- Mark/unmark spammers  
- Monitor IP addresses and confidence scores  

---

### 🔒 Privacy & GDPR Compliance

- Device fingerprints are anonymous identifiers  
- No personal data is collected  
- Fingerprinting for fraud prevention is allowed under GDPR  
- All data stored securely in your WordPress database  

---

### 💡 Requirements

- WordPress 5.0 or higher  
- WPForms plugin (free or pro version)  
- PHP 7.2 or higher  
- FingerprintJS API key (free tier available)  

---

### 🆓 Free Tier Limits

FingerprintJS offers a generous free tier:
- 20,000 API calls per month  
- ~650 form page views per day  
- Perfect for small to medium-sized websites  

---

### 🔗 Useful Links

- [📘 Documentation](https://github.com/rohitdevwp/wpforms-fingerprint-protection/wiki)  
- [🐞 Report Issues](https://github.com/rohitdevwp/wpforms-fingerprint-protection/issues)  
- [💻 GitHub Repository](https://github.com/rohitdevwp/wpforms-fingerprint-protection)  

---

## ⚙️ Installation

### **Automatic Installation**

1. Log in to your WordPress admin panel  
2. Go to **Plugins → Add New**  
3. Search for “WPForms Fingerprint Protection”  
4. Click **Install Now** and then **Activate**

### **Manual Installation**

1. Download the plugin ZIP file  
2. Go to **Plugins → Add New → Upload Plugin**  
3. Choose the ZIP file and click **Install Now**  
4. Activate the plugin  

### **Configuration**

1. Go to **WPForms → Fingerprint Protection**  
2. Sign up at [fingerprint.com](https://fingerprint.com) for a free API key  
3. Copy your **Public API Key**  
4. Paste it in the “API Key” field  
5. Adjust settings as needed (defaults work great for most sites)  
6. Click **Save Settings**

Your forms are now protected! 🎉


## Issue: Not found menu?
## just copy paste this code to your functions.php of your wordpress theme (appearance->theme file editor-> functions.php)
```php
// Temporary - Add direct admin page
add_action('admin_menu', function() {
    add_menu_page(
        'Fingerprint Settings',
        'Fingerprint Protection', 
        'manage_options',
        'wpfp-settings',
        'wpfp_settings_page_callback',
        'dashicons-shield'
    );
});

function wpfp_settings_page_callback() {
    if (class_exists('WPForms_Fingerprint_Protection')) {
        $plugin = WPForms_Fingerprint_Protection::get_instance();
        $plugin->render_settings_page();
    }
}

```

### 🧪 Testing

1. Visit a page with a WPForms form  
2. Submit the form multiple times quickly  
3. After hitting the rate limit, you’ll see:  
   > “Too many submissions detected”  
4. Check **WPForms → Fingerprint Logs** to see blocked attempts  

---

## ❓ Frequently Asked Questions (FAQ)

**Q: Does this work with the free version of WPForms?**  
✅ Yes! Works with both WPForms Lite and WPForms Pro.

**Q: Do I need a paid FingerprintJS account?**  
No, the free tier includes 20,000 API calls per month.

**Q: Will this slow down my website?**  
No. The script is lightweight (<30KB) and loads asynchronously from a global CDN.

**Q: Does it work with AJAX form submissions?**  
Yes, fully compatible with WPForms’ AJAX submission feature.

**Q: What happens if a legitimate user is blocked?**  
You can unmark them from the Logs dashboard — instant access restored.

**Q: Can I customize the rate limits?**  
Yes — control submission limits, time windows, and spam sensitivity.

**Q: Is this GDPR compliant?**  
Yes. Only anonymous device identifiers are used — no personal data.

**Q: What if FingerprintJS fails to load?**  
It fails gracefully — submissions are allowed but marked as “suspicious”.

**Q: Can I see which submissions were blocked?**  
Yes — view all allowed, blocked, and suspicious entries in the dashboard.

**Q: Does this replace CAPTCHA?**  
It can! Provides better protection without user annoyance.

**Q: How accurate is the fingerprinting?**  
FingerprintJS Pro offers **99.5% accuracy** across sessions and browsers.

**Q: Will this work with caching plugins?**  
Yes — compatible with WP Rocket, W3 Total Cache, WP Super Cache, and more.

---

## 🖼️ Screenshots

1. Settings page with API key configuration  
2. Protection statistics dashboard  
3. Detailed submission logs with filtering  
4. Rate limiting in action  
5. Mark/unmark spam interface  

---

## 🧾 Changelog

### **1.0.0 - 2024-01-15**
- Initial release  
- Device fingerprinting integration  
- Rate limiting system  
- Spam detection  
- Admin dashboard with logs  
- Settings page  
- Mark/unmark spam functionality  
- Multi-language support ready  

---

## 🔔 Upgrade Notice

**1.0.0** — Initial release of WPForms Fingerprint Protection.

---

## 🧩 Third-Party Services

This plugin uses **FingerprintJS**, a third-party service for device fingerprinting.

**Service:** [FingerprintJS](https://fingerprint.com)  
**Privacy Policy:** [https://fingerprint.com/privacy-policy/](https://fingerprint.com/privacy-policy/)  
**Terms of Service:** [https://fingerprint.com/terms-of-service/](https://fingerprint.com/terms-of-service/)

When a user views a form, the plugin loads a script from `fpjscdn.net` to generate a fingerprint.

- Used only for spam prevention  
- Not shared with third parties  
- Stored securely in your database  
- Anonymous and GDPR-safe  

By using this plugin, you agree to comply with FingerprintJS’s terms.

---

## 🆘 Support

- [WordPress Support Forum](https://wordpress.org/support/plugin/wpforms-fingerprint-protection/)  
- [GitHub Issues](https://github.com/rohitdevwp/wpforms-fingerprint-protection/issues)  
- [Documentation](https://github.com/rohitdevwp/wpforms-fingerprint-protection/wiki)

---

## 🤝 Contributing

Contributions are welcome!  
Visit the [GitHub repository](https://github.com/rohitdevwp/wpforms-fingerprint-protection) to open issues or submit pull requests.

---

## 👨‍💻 Credits

- **Developed by:** Rohit Dev  
- **Powered by:** [FingerprintJS](https://fingerprint.com)  
- **Built for:** The WordPress Community ❤️
