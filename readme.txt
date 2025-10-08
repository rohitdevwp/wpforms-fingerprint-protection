=== WPForms Fingerprint Protection ===
Contributors: rohitdev
Donate link: https://github.com/rohitdevwp/wpforms-fingerprint-protection
Tags: wpforms, spam, anti-spam, fingerprint, security, forms, fraud-prevention
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prevent spam and fake form submissions using FingerprintJS device fingerprinting. Stop bots, rate limit abusers, and protect your WPForms.

== Description ==

**WPForms Fingerprint Protection** uses advanced device fingerprinting technology to protect your WordPress forms from spam, bots, and fake submissions. Unlike traditional CAPTCHA solutions, this plugin works invisibly in the background without annoying your legitimate users.

### 🛡️ Key Features

* **Device Fingerprinting**: Creates unique identifiers for each visitor based on browser and device characteristics
* **Rate Limiting**: Automatically blocks users who submit forms too frequently
* **Spam Detection**: Learns and blocks known spammers across sessions
* **Admin Dashboard**: View detailed logs of all submissions with filtering options
* **Zero User Friction**: No CAPTCHA, no extra steps - completely invisible to users
* **99.5% Accuracy**: Powered by FingerprintJS Pro for reliable device identification

### 🎯 Perfect For

* Contact forms receiving spam
* Lead generation forms
* Registration forms
* Survey and feedback forms
* Any WPForms experiencing abuse

### 🚀 How It Works

1. Install the plugin and activate it
2. Get a free API key from [fingerprint.com](https://fingerprint.com) (20,000 requests/month free)
3. Enter your API key in the settings
4. That's it! Your forms are now protected

The plugin automatically:
- Tracks each visitor's unique device fingerprint
- Limits submissions per device (configurable)
- Blocks known spammers automatically
- Logs all activity for your review

### 📊 What You Get

**Settings Page:**
- Easy API key configuration
- Customizable rate limits
- Adjustable spam thresholds
- Real-time protection statistics

**Logs Dashboard:**
- View all submissions
- Filter by status (allowed, blocked, spam, suspicious)
- Mark/unmark spammers
- Monitor IP addresses and confidence scores

### 🔒 Privacy & GDPR Compliant

- Device fingerprints are anonymous identifiers
- No personal data is collected
- Fingerprinting for fraud prevention is allowed under GDPR
- All data stored securely in your WordPress database

### 💡 Requirements

- WordPress 5.0 or higher
- WPForms plugin (free or pro version)
- PHP 7.2 or higher
- FingerprintJS API key (free tier available)

### 🆓 Free Tier Limits

FingerprintJS offers a generous free tier:
- 20,000 API calls per month
- Approximately 650 form page views per day
- Perfect for small to medium-sized websites

### 🔗 Links

* [Documentation](https://github.com/rohitdevwp/wpforms-fingerprint-protection/wiki)
* [Report Issues](https://github.com/rohitdevwp/wpforms-fingerprint-protection/issues)
* [GitHub Repository](https://github.com/rohitdevwp/wpforms-fingerprint-protection)

== Installation ==

### Automatic Installation

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for "WPForms Fingerprint Protection"
4. Click "Install Now" and then "Activate"

### Manual Installation

1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Activate the plugin

### Configuration

1. Go to **WPForms → Fingerprint Protection**
2. Sign up at [fingerprint.com](https://fingerprint.com) for a free API key
3. Copy your **Public API Key**
4. Paste it in the "API Key" field
5. Adjust settings as needed (defaults work great for most sites)
6. Click "Save Settings"

Your forms are now protected!

### Testing

1. Visit a page with a WPForms form
2. Submit the form multiple times quickly
3. After hitting the rate limit, you should see a message: "Too many submissions detected"
4. Check **WPForms → Fingerprint Logs** to see the blocked attempts

== Frequently Asked Questions ==

= Does this work with the free version of WPForms? =

Yes! This plugin works with both WPForms Lite (free) and WPForms Pro.

= Do I need a paid FingerprintJS account? =

No, the free tier includes 20,000 API calls per month, which is plenty for most websites. You only need to upgrade if you exceed this limit.

= Will this slow down my website? =

No. The fingerprinting script is loaded asynchronously and doesn't block page rendering. The script is lightweight (less than 30KB) and loads from a global CDN.

= Does it work with AJAX form submissions? =

Yes, the plugin is fully compatible with WPForms' AJAX submission feature.

= What happens if a legitimate user is blocked? =

You can easily unmark them as spam from the Logs dashboard. They will be able to submit forms immediately.

= Can I customize the rate limits? =

Yes! You can adjust:
- Maximum submissions per device
- Time window for rate limiting
- Confidence score threshold
- Spam detection sensitivity

= Is this GDPR compliant? =

Yes. Device fingerprinting for fraud prevention is allowed under GDPR. The plugin doesn't collect or store personal data - only anonymous device identifiers.

= What if FingerprintJS is blocked or fails to load? =

The plugin is designed to fail gracefully. If fingerprinting fails, submissions are allowed but logged as "suspicious" for your review.

= Can I see which submissions were blocked? =

Yes! The Logs dashboard shows all submissions with their status: allowed, blocked, spam, or suspicious.

= Does this replace CAPTCHA? =

It can! This plugin provides better protection than traditional CAPTCHAs without annoying your users. However, you can use both together for maximum security.

= How accurate is the fingerprinting? =

FingerprintJS Pro has 99.5% accuracy in identifying unique devices, even across sessions and browser restarts.

= Will this work with caching plugins? =

Yes, the plugin is compatible with popular caching plugins like WP Rocket, W3 Total Cache, and WP Super Cache.

== Screenshots ==

1. Settings page with API key configuration
2. Protection statistics dashboard
3. Detailed submission logs with filtering
4. Rate limiting in action
5. Mark/unmark spam interface

== Changelog ==

= 1.0.0 - 2024-01-15 =
* Initial release
* Device fingerprinting integration
* Rate limiting system
* Spam detection
* Admin dashboard with logs
* Settings page
* Mark/unmark spam functionality
* Multi-language support ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of WPForms Fingerprint Protection.

== Third-Party Services ==

This plugin uses FingerprintJS, a third-party service for device fingerprinting.

**Service:** FingerprintJS  
**Website:** https://fingerprint.com  
**Privacy Policy:** https://fingerprint.com/privacy-policy/  
**Terms of Service:** https://fingerprint.com/terms-of-service/

When a user views a form, the plugin loads a script from `fpjscdn.net` to generate a device fingerprint. This data is:
- Used only for spam prevention
- Not shared with third parties
- Stored securely in your WordPress database
- Anonymous (no personal data)

By using this plugin, you agree to comply with FingerprintJS's terms of service.

== Support ==

For support, please visit:
- [Plugin Support Forum](https://wordpress.org/support/plugin/wpforms-fingerprint-protection/)
- [GitHub Issues](https://github.com/rohitdevwp/wpforms-fingerprint-protection/issues)
- [Documentation](https://github.com/rohitdevwp/wpforms-fingerprint-protection/wiki)

== Contributing ==

Contributions are welcome! Please visit the [GitHub repository](https://github.com/rohitdevwp/wpforms-fingerprint-protection) to contribute.

== Credits ==

* Developed by Rohit Dev
* Powered by FingerprintJS
* Built for the WordPress community