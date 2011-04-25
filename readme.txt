=== Plugin Name ===
Contributors: dolby_uk
Donate link: http://www.dansmart.co.uk/
Tags: iphone, mobile, theme switcher, mobile theme, mobile device, series 60, ipad, blackberry, android
Requires at least: 2.7
Tested up to: 3.1
Stable tag: trunk

The Mobile Smart plugin allows your Wordpress site to switch your theme if a user visits it using a mobile device,
plus adds template tags to help you customise your theme based on the device viewing it. Also contains a sample mobile theme
for developers to start with, based on Mobile Boilerplate and HTML 5 Reset.

== Description ==

Mobile Smart, using detection from the MobileESP project (http://www.mobileesp.com) allows the following:

* Switch your theme to a mobile-ready theme if a mobile device is detected
* Manual Switcher - to allow your user to manually switch between desktop and mobile versions. Available in 3 versions: widget, option to automatically insert into footer, or template tag.
* Template functions to help determine which tier of mobile device (touch/smartphone/other) is viewing your site, to allow conditional content inclusion.
* Adds device and tier specific CSS selectors to the body_class, to allow conditional CSS (e.g. so in the same way you have ".single" that you can target ".iphone" or ".mobile-tier-touch".)

See the Frequently Asked Questions for guidance on how to use the plugin.

A number of devices have now been added to this plugin. Recently added devices, via MobileESP project include:

* IPad
* Blackberry Tablet
* Android Tablet
* Windows Phone 7
* Other Blackberry devices (e.g. Blackberry Touch)
* Gaming devices with browsers (Sony Playstation, XBox, Nintendo devices)

There are also two new tiers:

* Tablet (MOBILE_DEVICE_TIER_TABLET) - for tablets (currently iPad, Android, Blackberry)
* Rich CSS (MOBILE_DEVICE_TIER_RICH_CSS) - for devices that are capable for viewing content with CSS optimised for the iPhone, but may not necessarily support Javascript

= Mobile Theme =
Mobile Smart comes with a basic barebones ('boilerplate') theme that can be used by theme developers to develop for mobile devices.

The Mobile Smart boilerplate theme is based on two projects: http://html5boilerplate.com/mobile/ and http://html5reset.org/#wordpress
with some additional Mobile Smart modifications.

Note: The theme is not designed to be used without developer modification.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `mobile-smart` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Settings->Mobile Smart and choose your theme to display when a mobile device encounters your page.

See the Frequently Asked Questions for guidance on how to use the plugin.

If you wish to use the built in sample boilerplate (i.e. barebones) theme as a starting place:
1. Copy the `plugins/mobile-smart/mobile-smart-theme/` folder to the `wp-content/themes/` directory
1. Select the theme in Settings->Mobile Smart
1. Make appropriate modifications to the theme

== Frequently Asked Questions ==

= Does this work with other mobile plugins =

It would be advisable not to use other mobile theme switching functionality with this unless theme switching is turned off (go to Settings->Mobile Smart to disable).

This has been tested with the Wordpress Mobile Pack transcoder and is noted to be compatible, though the list of mobile devices are different between the two.

= How do I use the Manual Switcher? =

You have the option of the following:
* Mobile Smart Manual Switcher Widget - go to Appearance->Widgets and drop the widget in an appropriate sidebar. If you're
  a theme developer, you can create a new 'sidebar' in the appropriate location, e.g. the footer bar, if you don't want
  this option in the standard sidebar.
* Enable Manual Switcher in footer - if this option is enabled (via the Options->Mobile Smart page), this adds
  the Manual Switcher link into the wp_footer() call, which means it will be displayed at the bottom of your page.
* Template tag, see below:

`<?php
  // get global instance of Mobile Smart class
  global $mobile_smart;

  // display manual switcher link - requires Manual Switching to be enabled
  $mobile_smart->addSwitcherLink();
?>`

The Manual Switcher displays the switcher link in a div with an id of *mobilesmart_switcher*

= Do you do domain switching =

Not currently, though that is on the roadmap.

= How do I enable unique handset body classes =

To enable the CSS body classes, ensure that in your mobile theme you have the body_class() function included:

 `<?php body_class(); ?>`


= How do I change stylesheets dependent on device tier =

How do I use the body classes?

If you have a style that you only want a specific tier of device (e.g. touch handsets like the iPhone) to use, then use the body class CSS selector in your CSS file as follows:

(Example: 

/* for all links */
a {
  color: black;

  }

/* increase padding on anchors on touch handsets to allow for big fingers
.mobile-tier-touch li a {
  padding: 20px;
}


= How do I change stylesheets dependent on device tier =

You would do this if you prefer to split out each device tier CSS into separate files. Be aware that this creates an extra function call though.

Use the following PHP code:

`<?php
/* add additional stylesheet for certain mobile types */
global $mobile_smart;
// add stylesheets dependent on header
if ($mobile_smart->isTierTouch() == MOBILE_DEVICE_TIER_TOUCH)
{
  wp_enqueue_style('mobile-touch', get_bloginfo('stylesheet_directory')."/css/touch.css");
}
else if ($mobile_smart->isTierSmartphone())
{
  wp_enqueue_style('mobile-smartphone', get_bloginfo('stylesheet_directory')."/css/smartphone.css");
}
?>`

Note: these functions do not test for the Manual Switcher. To test for the manual switcher (in case you are using
these template tag functions in a desktop theme), you should call:

`<?php
/* add additional stylesheet for certain mobile types */
global $mobile_smart;
// find out manual switching state
$is_manual_switched_to_mobile = $mobile_smart->switcher_isMobile();
?>`

= Can you add xxxx-device? =

Please email me with details of the device that is not yet supported by Mobile Smart, and I will add it in, and endeavour to release an updated version within the week (if timescales allow).

= Where can I get a mobile theme from? =

Try the Wordpress Mobile Pack for a good example of a theme that is compatible with XHTML-MP.

I will be developing a sample mobile theme next, that will help get going quickly without installing two plugins.

= What's on the roadmap? =

- Admin interface to allow adding of user agents
- Domain switching of themes
- PHP and Javascript helper functions

== Changelog ==

= 0.1 =
Initial release, containing mobile device detection, body classes, and mobile tier template tags.

= 0.2 =
Added Manual Mobile Switcher - widget, link, and template tag.

= 1.0 =
Based detector on Mobile ESP project, meaning device detection will stay up to date with latest mobile devices

= 1.1 =
Includes sample mobile theme - based on Mobile Boilerplate and HTML 5 Reset projects

== Upgrade Notice ==

= 0.1 =
Initial release.

= 0.2 =

= 1.0 =

= 1.1 =
