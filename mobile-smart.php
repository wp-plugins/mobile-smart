<?php
/*
Plugin Name: Mobile Smart
Plugin URI: http://www.dansmart.co.uk
Version: v0.2.1
Author: <a href="http://www.dansmart.co.uk/">Dan Smart</a>
Description: Mobile Smart contains helper tools for mobile devices, including allowing
             determination of mobile device type or tier in CSS and PHP code
 */

/*  Copyright 2009 Dan Smart  (email : dan@dansmart.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Attributation:
//  - Parts of the detection code are based upon the mobile detect script by
//    Anthony Hand
//     - The code, "Detecting Smartphones Using PHP"
//       by Anthony Hand, is licensed under a Creative Commons
//       Attribution 3.0 United States License.
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// -------------------------------------------------------------------------
// Defines
// -------------------------------------------------------------------------
define('MOBILESMART_DOMAIN', 'mobilesmart');

define ('MOBILE_DEVICE_OPERA_MINI', 'operamini');
define ('MOBILE_DEVICE_IPHONE', 'iphone');
define ('MOBILE_DEVICE_IPOD', 'ipod');
define ('MOBILE_DEVICE_ANDROID_WEBKIT', 'android webkit');
define ('MOBILE_DEVICE_SERIES60', 'series_60');
define ('MOBILE_DEVICE_SYMBIAN_OS', 'symbian_os');
define ('MOBILE_DEVICE_WINDOWS_MOBILE', 'windows_mobile');
define ('MOBILE_DEVICE_BLACKBERRY', 'blackberry');
define ('MOBILE_DEVICE_PALM_OS', 'palm_os');
define ('MOBILE_DEVICE_OTHER', 'other_mobile');

define ('MOBILE_DEVICE_TIER_TOUCH', 'mobile-tier-touch');
define ('MOBILE_DEVICE_TIER_SMARTPHONE', 'mobile-tier-smartphone');
define ('MOBILE_DEVICE_TIER_OTHER', 'mobile-tier-other-mobile');

define ('MOBILESMART_SWITCHER_GET_PARAM', 'mobile_switch');
define ('MOBILESMART_SWITCHER_MOBILE_STR', 'mobile');
define ('MOBILESMART_SWITCHER_DESKTOP_STR', 'desktop');
define ('MOBILESMART_SWITCHER_COOKIE', 'mobile-smart-switcher');
define ('MOBILESMART_SWITCHER_COOKIE_EXPIRE', 3600); // 3600

// -------------------------------------------------------------------------
// Includes
// -------------------------------------------------------------------------
require_once('mobile-smart-switcher-widget.php');

// -------------------------------------------------------------------------
// Plugin Class
// -------------------------------------------------------------------------
if (!class_exists("MobileSmart"))
{
  class MobileSmart
  {
    // -------------------------------------------------------------------------
    // Attributes
    // -------------------------------------------------------------------------
    var $admin_optionsName = "MobileSmartOptions";
    var $admin_options = array('mobile_theme'=>'default',
                               'enable_theme_switching'=>true);

    var $device = ''; // current device
    var $deviceTier = ''; // current device tier

    var $switcher_cookie = null;

    // Initialise UA strings
    var $device_ua_array = array(
       'engineWebKit' => 'webkit',
       'deviceAndroid' => 'android',
       'deviceIphone' => 'iphone',
       'deviceIpod' => 'ipod',
       'deviceSymbian' => 'symbian',
       'deviceS60' => 'series60',
       'deviceS70' => 'series70',
       'deviceS80' => 'series80',
       'deviceS90' => 'series90',
       'deviceWinMob' => 'windows ce',
       'deviceWindows' => 'windows',
       'deviceIeMob' => 'iemobile',
       'enginePie' => "wm5 pie", //An old Windows Mobile
       'deviceBB' => 'blackberry',
       'vndRIM' => 'vnd.rim', //Detectable when BB devices emulate IE or Firefox
       'devicePalm' => 'palm',

       'engineBlazer' => 'blazer', //Old Palm
       'engineXiino' => 'xiino', //Another old Palm

       //Initialize variables for mobile-specific content.
       'vndwap' => 'vnd.wap',
       'wml' => 'wml',

       //Initialize variables for other random devices and mobile browsers.
       'deviceBrew' => 'brew',
       'deviceDanger' => 'danger',
       'deviceHiptop' => 'hiptop',
       'devicePlaystation' => 'playstation',
       'deviceNintendoDs' => 'nitro',
       'deviceNintendo' => 'nintendo',
       'deviceWii' => 'wii',
       'deviceXbox' => 'xbox',
       'deviceArchos' => 'archos',

       'engineOpera' => "opera", //Popular browser
       'engineNetfront' => "netfront", //Common embedded OS browser
       'engineUpBrowser' => 'up.browser', //common on some phones
       'engineOpenWeb' => 'openweb', //Transcoding by OpenWave server
       'deviceMidp' => "midp", //a mobile Java technology
       'uplink' => "up.link",

       'devicePda' => 'pda', //some devices report themselves as PDAs
       'mini' => 'mini',  //Some mobile browsers put 'mini' in their names.
       'mobile' => 'mobile', //Some mobile browsers put 'mobile' in their user agent strings.
       'mobi' => 'mobi', //Some mobile browsers put 'mobi' in their user agent strings.

       //Use Maemo, Tablet, and Linux to test for Nokia's Internet Tablets.
       'maemo' => 'maemo',
       'maemoTablet' => 'tablet',
       'linux' => 'linux',
       'qtembedded' => "qt embedded", //for Sony Mylo
       'mylocom2' => 'com2', //for Sony Mylo also

       //In some UserAgents, the only clue is the manufacturer.
       'manuSonyEricsson' => "sonyericsson",
       'manuericsson' => "ericsson",
       'manuSamsung1' => "sec-sgh",
       'manuSony' => "sony",

       //In some UserAgents, the only clue is the operator.
       'svcDocomo' => "docomo",
       'svcKddi' => "kddi",
       'svcVodafone' => "vodafone"
      ); // end user agent string array

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // Method: Constructor
    // -------------------------------------------------------------------------
    function MobileSmart()
    {
      // translations
      load_plugin_textdomain(MOBILESMART_DOMAIN,'/wp-content/plugins/mobile-smart/languages');

      if (isset($_COOKIE[MOBILESMART_SWITCHER_COOKIE]))
      {
        $this->switcher_cookie = $_COOKIE[MOBILESMART_SWITCHER_COOKIE];
        //echo "Construct cookie: $this->switcher_cookie<br/><br/>";
      }
    }

    // -------------------------------------------------------------------------
    // Method: initialisePlugin
    // Description: WP initialisation of the plugin
    // -------------------------------------------------------------------------
    function initialisePlugin()
    {
      // initialise the admin options
      $this->addAdminOptions();
    }

    // -------------------------------------------------------------------------
    // Method: addAdminOptions
    // Description: add the options
    // -------------------------------------------------------------------------
    function addAdminOptions()
    {
      add_option($this->admin_optionsName, $this->admin_options);
    }

    // -------------------------------------------------------------------------
    // Method: getAdminOptions
    // Description: gets the admin panel options
    // -------------------------------------------------------------------------
    function getAdminOptions()
    {
      // get the options from WP
      $wp_options = get_option($this->admin_optionsName);

      // if already existing data
      if (!empty($wp_options))
      {
        // populate our adminOptions with wp options
        foreach($wp_options as $key=>$wp_option)
        {
          $this->admin_options[$key] = $wp_option;
        }
      }

      // update WP
      update_option($this->admin_optionsName, $this->admin_options);

      return $this->admin_options;
    }

    // -------------------------------------------------------------------------
    // Method: displayAdminOptions
    // Description: displays the admin panel
    // -------------------------------------------------------------------------
    function displayAdminOptions()
    {
      $options = $this->getAdminOptions();

      if (isset($_POST['submit']))
      {
        $status_messages = array();
        // Enable / Disable theme switching
        if (isset($_POST['enable_theme_switching']))
        {
          // enable theme switching
          if ($options['enable_theme_switching'] != true)
          {
            $options['enable_theme_switching'] = true;

            $status_messages[] = array('updated', __('Theme switching enabled.', MOBILESMART_DOMAIN));
          }
        }
        else
        {
          // disable theme switching
          if ($options['enable_theme_switching'] != false)
          {
            $options['enable_theme_switching'] = false;

            $status_messages[] = array('updated', __('Theme switching disabled.', MOBILESMART_DOMAIN));
          }
        }

        // Enable / Disable manual switching
        if (isset($_POST['enable_manual_switch']))
        {
          if ($options['enable_manual_switch'] != true)
          {
            $options['enable_manual_switch'] = true;

            $status_messages[] = array('updated', __('Manual theme switching enabled.', MOBILESMART_DOMAIN));
          }
        }
        else
        {
          if ($options['enable_manual_switch'] != false)
          {
            $options['enable_manual_switch'] = false;

            $status_messages[] = array('updated', __('Manual theme switching disabled.', MOBILESMART_DOMAIN));
          }
        }

        // Enable / Disable footer manual switching
        if (isset($_POST['enable_manual_switch_in_footer']))
        {
          if ($options['enable_manual_switch_in_footer'] != true)
          {
            $options['enable_manual_switch_in_footer'] = true;

            $status_messages[] = array('updated', __('Manual theme switching in footer enabled.', MOBILESMART_DOMAIN));
          }
        }
        else
        {
          if ($options['enable_manual_switch_in_footer'] != false)
          {
            $options['enable_manual_switch_in_footer'] = false;

            $status_messages[] = array('updated', __('Manual theme switching in footer disabled.', MOBILESMART_DOMAIN));
          }
        }

        // Enable / Disable desktop manual switching
        if (isset($_POST['allow_desktop_switcher']))
        {
          if ($options['allow_desktop_switcher'] != true)
          {
            $options['allow_desktop_switcher'] = true;

            $status_messages[] = array('updated', __('Manual theme switching on desktop enabled.', MOBILESMART_DOMAIN));
          }
        }
        else
        {
          if ($options['allow_desktop_switcher'] != false)
          {
            $options['allow_desktop_switcher'] = false;

            $status_messages[] = array('updated', __('Manual theme switching on desktop disabled.', MOBILESMART_DOMAIN));
          }
        }

        // Get choice of mobile theme
        if ($options['mobile_theme'] != $_POST['theme'])
        {
          $options['mobile_theme'] = $_POST['theme'];

          $status_messages[] = array('updated', __('Mobile theme updated to: ', MOBILESMART_DOMAIN) . $_POST['theme']);
        }

        // output status messages
        if (!empty($status_messages))
        {
          ?>
            <div class="updated">
              <?php foreach ($status_messages as $message) : ?>
                <p><strong><?php echo $message[1] ?></strong></p>
              <?php endforeach; ?>
            </div>
          <?php
        }

        // update the options
        update_option($this->admin_optionsName, $options);
      }

      // Display the admin page
      ?>
      <div class="wrap">
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
          <h2>Mobile Smart</h2>
          <h3>Mobile Theme</h3>
          <p>Enable switching via user agent detection:</p>
          <label for="enable_theme_switching">Enable <input type="checkbox" name="enable_theme_switching" id="enable_theme_switching" <?php if ($options['enable_theme_switching']) { echo "checked"; } ?>/>
          </label>
          <h3>Mobile theme</h3>
          <p>Choose the mobile theme that will be displayed when a mobile device is detected.</p>
            <label for="theme">Mobile theme: <select id="theme" name="theme">
                <?php
                  $themes = get_themes();
                  foreach ($themes as $theme)
                  {
                    ?>
                    <option value="<?php echo $theme['Template']; ?>" <?php if ($theme['Template'] == $options['mobile_theme']) { echo "selected"; } ?>><?php echo $theme['Template']; ?></option>
                    <?php
                  }
                ?>
              </select></label>

          <hr/>
          <h3>Manual Switching</h3>
          <h4>Enable Manual Switcher</h4>
          <p>You can add a link to your pages which will allow the user to manually select the version
           (desktop or mobile) that they want. Once you enable Manual Switching, you can use either the
           footer link or the Mobile Smart Manual Switcher widget.</p>
          <label for="enable_manual_switch">Enable Manual Switcher <input type="checkbox" name="enable_manual_switch" id="enable_manual_switch" <?php if ($options['enable_manual_switch']) { echo "checked"; } ?>/>
          </label><br/>

          <h4>Enable a Manual Switcher link in the footer</h4>
          <p><em>Manual switching (above) must be enabled for this to work properly.</em></p>
          <label for="enable_manual_switch_in_footer">Enable Manual Switcher in footer <input type="checkbox" name="enable_manual_switch_in_footer" id="enable_manual_switch_in_footer" <?php if ($options['enable_manual_switch_in_footer']) { echo "checked"; } ?>/>
          </label><br/>

          <h4>Allow manual switching on desktop</h4>
          <p>This is most useful for debugging your themes. You probably
          do not want to allow your users to switch to the mobile version whilst viewing on a desktop in other cases.</p>
          <p><em>Manual switching (above) must be enabled for this to work properly.</em></p>
          <label for="allow_desktop_switcher">Enable Manual Switcher Link whilst on Desktop <input type="checkbox" name="allow_desktop_switcher" id="allow_desktop_switcher" <?php if ($options['allow_desktop_switcher']) { echo "checked"; } ?>/>
          </label>

          <hr/>
          <h3>Mobile Device User Agents</h3>
          <p>Add user agents:          <span style="color: red">Coming soon...</span></p>
          <div class="submit">
            <input type="submit" name="submit" value="<?php _e('Update Settings', 'MobileSmart'); ?>"/>
          </div>
        </form>
      </div>
      <?php
    }

    // ---------------------------------------------------------------------------
    // Function: getUserAgentString
    // Description: gets the user agent string
    // ---------------------------------------------------------------------------
    function getUserAgentString()
    {
      return strtolower($_SERVER['HTTP_USER_AGENT']);
    }

    // ---------------------------------------------------------------------------
    // Function: getAcceptString
    // Description: gets the accept string
    // ---------------------------------------------------------------------------
    function getAcceptString()
    {
      return strtolower($_SERVER['HTTP_ACCEPT']);
    }

    // ---------------------------------------------------------------------------
    // Function: getCurrentDevice
    // Description: gets the current device
    // ---------------------------------------------------------------------------
    function getCurrentDevice()
    {
      if ($this->device == '')
      {
        if ($this->detectOperaMini())
        {
          $this->device = MOBILE_DEVICE_OPERA_MINI;
        }
        else if ($this->detectIphone())
        {
          $this->device = MOBILE_DEVICE_IPHONE;
        }
        else if ($this->detectIpod())
        {
          $this->device = MOBILE_DEVICE_IPOD;
        }
        else if ($this->detectAndroidWebKit())
        {
          $this->device = MOBILE_DEVICE_ANDROID_WEBKIT;
        }
        else if ($this->detectSeries60())
        {
          $this->device = MOBILE_DEVICE_SERIES60;
        }
        else if ($this->detectSymbianOS())
        {
          $this->device = MOBILE_DEVICE_SYMBIAN_OS;
        }
        else if ($this->detectWindowsMobile())
        {
          $this->device = MOBILE_DEVICE_WINDOWS_MOBILE;
        }
        else if ($this->detectBlackBerry())
        {
          $this->device = MOBILE_DEVICE_BLACKBERRY;
        }
        else if ($this->detectPalmOS())
        {
          $this->device = MOBILE_DEVICE_PALM_OS;
        }
        else if ($this->detectIsMobile())
        {
          $this->device = MOBILE_DEVICE_OTHER;
        }
        // To do...add the rest
      }
      return $this->device;
    }

    // ---------------------------------------------------------------------------
    // Function: getCurrentDeviceTier
    // Description: gets the current device tier
    // ---------------------------------------------------------------------------
    function getCurrentDeviceTier()
    {
      if ($this->deviceTier == '')
      {
        if ($this->detectTierTouch())
        {
          $this->device_tier = MOBILE_DEVICE_TIER_TOUCH;
        }
        if ($this->detectTierSmartphones())
        {
          $this->device_tier = MOBILE_DEVICE_TIER_SMARTPHONE;
        }
        if ($this->detectTierOtherPhones())
        {
          $this->device_tier = MOBILE_DEVICE_TIER_OTHER;
        }
      }

      return $this->device_tier;
    }


    // ---------------------------------------------------------------------------
    // Function: filter_add_body_classes
    // Description: adds device specific CSS class to the body
    // - Filter: see add_filter('body_class'...)
    // ---------------------------------------------------------------------------
    function filter_addBodyClasses($classes)
    {
      $options = $this->getAdminOptions();

      // if theme switching enabled
      if ($options['enable_theme_switching'] == true)
      {
        // if is a mobile device
        if ($this->detectIsMobile())
        {
          $classes[] .= "mobile" ;
        }

        // add current device string to body class
        $classes[] .= $this->getCurrentDevice();

        // add the tier of device also to body class
        $classes[] .= $this->getCurrentDeviceTier();
      }

      return $classes;
    }

    // ---------------------------------------------------------------------------
    // Function: filter_switchTheme
    // Description: switches the theme if it's a mobile device to the specified theme
    // - Filter: see add_filter('template'...)
    // ---------------------------------------------------------------------------
    function filter_switchTheme($theme)
    {
      // get options
      $options = $this->getAdminOptions();

      // if theme switching enabled
      if ($options['enable_theme_switching'] == true)
      {
        // if is a mobile device or is mobile due to cookie switching
        if ($this->switcher_isMobile())
        {
          $theme = $options['mobile_theme'];
          //echo "Switch theme: $theme<br/>";
        }
        else
        {
          //echo "Don't switch theme<br/><br/>";
        }
      }

      return $theme;
    }

     //---------------------------------------------------------------------------
     // Function: switcher_isMobile
     // Description: determines whether the mode is mobile or switched
     // ---------------------------------------------------------------------------
     function switcher_isMobile()
     {
        $is_mobile = false;

        // get the mobile detect value
        $detectmobile = $this->detectIsMobile();

        //echo "Detect Mobile: ".($detectmobile ? "true" : "false")."<br/>";
        //echo "Cookie: ".($this->switcher_cookie ? "true" : "false")." - value: {$this->switcher_cookie}<br/>";

        // check the switcher cookie
        if ($detectmobile && $this->switcher_cookie)
        {
          if (($this->switcher_cookie == MOBILESMART_SWITCHER_DESKTOP_STR))
          {
            $is_mobile = false;
          }
          else
          {
            $is_mobile = true;
          }
        }
        // if we're not a mobile, then we inverse the check string
        else if (!$detectmobile)
        {
          if (($this->switcher_cookie == MOBILESMART_SWITCHER_MOBILE_STR))
          {
            $is_mobile = true;
          }
          else
          {
            $is_mobile = false;
          }
        }
        else
        {
          $is_mobile = $detectmobile;
        }

        //echo "Is Mobile: ".($is_mobile ? "true" : "false")."<br/><br/>";

        return $is_mobile;
     }

     // ---------------------------------------------------------------------------
     // Function: addSwitcherLink
     // Description: checks if the plugin option is enabled and if so adds the html switcher
     // ---------------------------------------------------------------------------
     function addSwitcherLink()
     {
        // get options
        $options = $this->getAdminOptions();

        // if theme switching enabled
        if ($options['enable_manual_switch'] == true)
        {
          // if is a mobile device or cookie switcher allows it.
          $is_mobile = $this->switcher_isMobile();
          if ($is_mobile || $options['allow_desktop_switcher'])
          {
            ?>
      <!-- START MobileSmart - Switcher - http://www.dansmart.co.uk/ -->
      <div id="mobilesmart_switcher">
        <?php if ($is_mobile) : ?>
          <a href="<?php echo $this->get_switcherLink(MOBILESMART_SWITCHER_DESKTOP_STR); ?>"><?php _e('Switch to desktop version', MOBILESMART_DOMAIN); ?></a>
        <?php else : ?>
          <a href="<?php echo $this->get_switcherLink(MOBILESMART_SWITCHER_MOBILE_STR); ?>"><?php _e('Switch to mobile version', MOBILESMART_DOMAIN); ?></a>
        <?php endif; ?>
      </div>
      <!-- END MobileSmart - Switcher - http://www.dansmart.co.uk/ -->
            <?php
          }
        }
     }

     // ---------------------------------------------------------------------------
     // Function: action_addSwitcherLinkInFooter
     // Description: action call for too add link into wp_footer
     // ---------------------------------------------------------------------------
     function action_addSwitcherLinkInFooter()
     {
        // get options
        $options = $this->getAdminOptions();

        // if theme switching enabled
        if ($options['enable_manual_switch'] == true && $options['enable_manual_switch_in_footer'] == true)
        {
          // display the link
          $this->addSwitcherLink();
        }
     }

    // ---------------------------------------------------------------------------
    // Function: get_switcherLink
    // Description: gets the link to display the switcher
    // Parameters: version - should be 'mobile' or 'desktop'
    // ---------------------------------------------------------------------------
    function get_switcherLink($version)
    {
      $switcher_str = add_query_arg (array (MOBILESMART_SWITCHER_GET_PARAM => $version));

      return $switcher_str;
    }

    // ---------------------------------------------------------------------------
    // Function: action_addSwitcherLink
    // Description: checks if the html switcher link has been called and acts appropriately
    // ---------------------------------------------------------------------------
    function action_handleSwitcherLink()
    {
      if (isset($_GET[MOBILESMART_SWITCHER_GET_PARAM]))
      {
        // get the version
        $version = $_GET[MOBILESMART_SWITCHER_GET_PARAM];

        // set the cookie to say which version it is
        setcookie(MOBILESMART_SWITCHER_COOKIE, $version, time()+MOBILESMART_SWITCHER_COOKIE_EXPIRE, COOKIEPATH, COOKIE_DOMAIN);

        // save version in class for viewing the page before a refresh
        $this->switcher_cookie = $version;

        //echo "Version to set: $version<br/>";
        //echo "Set version: $this->switcher_cookie<br/><br/>";
      }
    }

    // ---------------------------------------------------------------------------
    // Function: isTierTouch
    // Description: is the current device tier - touch
    // ---------------------------------------------------------------------------
    function isTierTouch()
    {
      return $this->getCurrentDeviceTier() == MOBILE_DEVICE_TIER_TOUCH;
    }

    // ---------------------------------------------------------------------------
    // Function: isTierSmartphone
    // Description: is the current device tier - smartphone
    // ---------------------------------------------------------------------------
    function isTierSmartphone()
    {
      return $this->getCurrentDeviceTier() == MOBILE_DEVICE_TIER_SMARTPHONE;
    }

    // ---------------------------------------------------------------------------
    // Function: isTierOtherMobile
    // Description: is the current device tier - other mobile devices (non-smartphone / non-touch)
    // ---------------------------------------------------------------------------
    function isTierOtherMobile()
    {
      return $this->getCurrentDeviceTier() == MOBILE_DEVICE_TIER_OTHER;
    }

    // ***************************************************************************
    // DETECTION FUNCTIONS
    // ***************************************************************************

    // ---------------------------------------------------------------------------
    // Function: detectOperaMini
    // Description: special case to detect Opera Mini, uses different headers
    // ---------------------------------------------------------------------------
    function detectOperaMini()
    {
      if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
       return true;
      else {
        if (isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']))
          return true;
        else return false;
      }
    }

    // ---------------------------------------------------------------------------
    // Function: detectIphone
    // Description: detect if it is an iPhone (not an iPod)
    // ---------------------------------------------------------------------------
     function detectIphone()
     {
       $result = false;
       if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceIphone']) > -1)
       {
           if ($this->detectIpod() != true) // Ipod touch reports as Iphone also so don't include
           {
              $result = true;
           }
       }
       return $result;
     }

     // ---------------------------------------------------------------------------
    // Function: detectIpod
    // Description: detect if it is an iPod Touch
    // ---------------------------------------------------------------------------
     function detectIpod()
     {
        $result = false;
        if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceIpod']) > -1)
        {
           $result = true;
        }

        return $result;
     }

      // ---------------------------------------------------------------------------
      // Function: detectIphoneFamily
      // Description: detects if it is an iPhone or iPod Touch
      // ---------------------------------------------------------------------------
     function detectIphoneFamily()
     {
        $result = false;
        if ($this->detectIphone() || $this->detectIpod)
        {
         $result = true;
        }

        return $result;
     }

      // ---------------------------------------------------------------------------
      // Function: detectAndroid
      // Description: detects if it is an Android based handset
      // ---------------------------------------------------------------------------
     function detectAndroid()
     {
       $result = false;
        if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceAndroid']) > -1)
        {
          $result = true;
        }

        return $result;
     }

     // ---------------------------------------------------------------------------
      // Function: detectWebkit
      // Description: detects if it is a Webkit browser
      // ---------------------------------------------------------------------------
     function detectWebkit()
     {
        $result = false;
        if (stripos($this->getUserAgentString(), $this->device_ua_array['engineWebKit']) > -1)
        {
          $result = true;
        }

        return $result;
     }

     // ---------------------------------------------------------------------------
      // Function: detectAndroidWebKit
      // Description: detects if it is an Android based handset with Webkit browser
      // ---------------------------------------------------------------------------
     function detectAndroidWebKit()
     {
        $result = false;
        if ($this->detectAndroid() && $this->detectWebkit())
        {
          $result = true;
        }

        return $result;
     }


     // ---------------------------------------------------------------------------
      // Function: detectSeries60
      // Description: detects if it is a Series 60 based browser
      // ---------------------------------------------------------------------------
     function detectSeries60()
     {
        $result = false;
        // Check if it's WebKit first
        if ($this->detectWebkit() == true)
        {
          // check if it's Series 60
          if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceSymbian']) > -1 ||
              stripos($this->getUserAgentString(), $this->device_ua_array['deviceS60']) > -1)
          {
             $result = true;
          }
        }

        return $result;
     }

     // ---------------------------------------------------------------------------
      // Function: detectSymbianOS
      // Description: detects if it is any Symbian OS-based device,
      //              including older S60, Series 70, Series 80, Series 90, and UIQ,
      //              or other browsers running on these devices.
      // ---------------------------------------------------------------------------
     function detectSymbianOS()
     {
       $result = false;
         if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceSymbian']) > -1 ||
             stripos($this->getUserAgentString(), $this->device_ua_array['deviceS60']) > -1 ||
             stripos($this->getUserAgentString(), $this->device_ua_array['deviceS70']) > -1 ||
             stripos($this->getUserAgentString(), $this->device_ua_array['deviceS80']) > -1 ||
             stripos($this->getUserAgentString(), $this->device_ua_array['deviceS90']) > -1)
        {
          $result = true;
        }

        return $result;
     }

     // ---------------------------------------------------------------------------
      // Function: detectWindowsMobile
      // Description: detects if it is a Windows Mobile based device
      // ---------------------------------------------------------------------------
     function detectWindowsMobile()
     {
       $result = false;
        // Most devices use 'Windows CE', but some report 'iemobile'
        // and some older ones report as 'PIE' for Pocket IE.
        if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceWinMob']) > -1 ||
            stripos($this->getUserAgentString(), $this->device_ua_array['deviceIeMob']) > -1 ||
            stripos($this->getUserAgentString(), $this->device_ua_array['enginePie']) > -1)
        {
            $result = true;
        }

        if ($this->detectWapWml() == true &&
            stripos($this->getUserAgentString(), $this->device_ua_array['deviceWindows']) > -1)
        {
          $result = true;
        }

        return $result;
     }

     // ---------------------------------------------------------------------------
      // Function: detectBlackberry
      // Description: detects if it is a Blackberry
      // ---------------------------------------------------------------------------
     function detectBlackBerry()
     {
       $result = false;
       if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceBB']) > -1)
       {
         $result = true;
       }
       if (stripos($this->getAcceptString(), $this->device_ua_array['vndRIM']) > -1)
       {
         $result = true;
       }

       return $result;
     }

     // ---------------------------------------------------------------------------
      // Function: detectPalmOS
      // Description: detects if it is a PalmOS
      // ---------------------------------------------------------------------------
     function detectPalmOS()
     {
       $result = false;
        // Most devices nowadays report as 'Palm', but some older ones reported as Blazer or Xiino.
        if (stripos($this->getUserAgentString(), $this->device_ua_array['devicePalm']) > -1 ||
            stripos($this->getUserAgentString(), $this->device_ua_array['engineBlazer']) > -1 ||
            stripos($this->getUserAgentString(), $this->device_ua_array['engineXiino']) > -1)
        {
          $result = true;
        }

        return $result;
     }

     // ---------------------------------------------------------------------------
      // Function: detectSmartphone
      // Description: detects if it is a smartphone of any kind
      // ---------------------------------------------------------------------------
     function detectSmartphone()
     {
       $result = false;
        if ($this->detectIphoneFamily() == true)
           $result = true;
        if ($this->detectSeries60() == true)
           $result = true;
        if ($this->detectSymbianOS() == true)
           $result = true;
        if ($this->detectWindowsMobile() == true)
           $result = true;
        if ($this->detectBlackBerry() == true)
           $result = true;
        if ($this->detectPalmOS() == true)
           $result = true;
        if ($this->detectAndroid() == true)
           $result = true;
        if ($this->detectOperaMini() == true)
           $result = true;

        return $result;
     }


     // ---------------------------------------------------------------------------
      // Function: detectBrewDevice
      // Description: detects if it is a brew device
      // ---------------------------------------------------------------------------
     function detectBrewDevice()
     {
       $result = false;

       if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceBrew']) > -1)
       {
         $result = true;
       }

       return $result;
     }

     // ---------------------------------------------------------------------------
     // Function: detectDangerHiptop
     // Description: detects if it is a Danger Hiptop device
     // ---------------------------------------------------------------------------
     function detectDangerHiptop()
     {
       $result = false;
       if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceDanger']) > -1 ||
            stripos($this->getUserAgentString(), $this->device_ua_array['deviceHiptop']) > -1)
        {
          $result = true;
        }

        return $result;
     }

     // ---------------------------------------------------------------------------
      // Function: detectOperaMobile
      // Description: detects if it is Opera Mobile (different to Opera Mini)
      // ---------------------------------------------------------------------------
     function detectOperaMobile()
     {
       $result = false;
        if (stripos($this->getUserAgentString(), $this->device_ua_array['engineOpera']) > -1)
        {
           if (stripos($this->getUserAgentString(), $this->device_ua_array['mobi']) > -1)
           {
              $result = true;
           }
        }

       return $result;
     }

     // ---------------------------------------------------------------------------
     // Function: detectWapWml
     // Description: detects whether the device supports WAP or WML.
     // ---------------------------------------------------------------------------
     function detectWapWml()
     {
       $result = false;
       if (stripos($this->getAcceptString(), $this->device_ua_array['vndwap']) > -1 ||
           stripos($this->getAcceptString(), $this->device_ua_array['wml']) > -1)
       {
          $result = true;
       }

       return $result;
     }

     // ---------------------------------------------------------------------------
     // Function: detectIsMobile
     // Description: detect most recent/current mid-tier Feature Phones
     //              as well as smartphone-class devices.
     // ---------------------------------------------------------------------------
     function detectIsMobile()
     {
        //Ordered roughly by market share, WAP/XML > Brew > Smartphone.
        if ($this->detectWapWml() == true)
           return true;
        if ($this->detectBrewDevice() == true)
           return true;
        if ($this->detectOperaMobile() == true)
           return true;
        if (stripos($this->getUserAgentString(), $this->device_ua_array['engineUpBrowser']) > -1)
           return true;
        if (stripos($this->getUserAgentString(), $this->device_ua_array['engineOpenWeb']) > -1)
           return true;
        if (stripos($this->getUserAgentString(), $this->device_ua_array['deviceMidp']) > -1)
           return true;
        if ($this->detectSmartphone() == true)
           return true;
        if ($this->detectDangerHiptop() == true)
           return true;

         if (stripos($this->getUserAgentString(), $this->device_ua_array['devicePda']) > -1)
           return true;
         if (stripos($this->getUserAgentString(), $this->device_ua_array['mobile']) > -1)
           return true;

         //detect older phones from certain manufacturers and operators.
         if (stripos($this->getUserAgentString(), $this->device_ua_array['uplink']) > -1)
           return true;
         if (stripos($this->getUserAgentString(), $this->device_ua_array['manuSonyEricsson']) > -1)
           return true;
         if (stripos($this->getUserAgentString(), $this->device_ua_array['manuericsson']) > -1)
           return true;
         if (stripos($this->getUserAgentString(), $this->device_ua_array['manuSamsung1']) > -1)
           return true;
         if (stripos($this->getUserAgentString(), $this->device_ua_array['svcDocomo']) > -1)
           return true;
         if (stripos($this->getUserAgentString(), $this->device_ua_array['svcKddi']) > -1)
           return true;
         if (stripos($this->getUserAgentString(), $this->device_ua_array['svcVodafone']) > -1)
           return true;

        else
           return false;
     }


    //*****************************
    // For Mobile Web Site Design
    //*****************************


     //**************************
     // The quick way to detect for a tier of devices.
     //   This method detects for devices which can
     //   display touch-optimised content (e.g. iPhone)
     function detectTierTouch()
     {
        if ($this->detectIphoneFamily() == true)
           return true;
        if ($this->detectAndroid() == true)
           return true;
        if ($this->detectAndroidWebKit() == true)
           return true;
        else
           return false;
     }

     //**************************
     // The quick way to detect for a tier of devices.
     //   This method detects for all smartphones, but
     //   excludes iPhones and iPod Touches.
     function detectTierSmartphones()
     {
        if ($this->detectSmartphone() == true)
        {
          if ($this->detectTierTouch() == true)
          {
             return false;
          }
          else
             return true;
        }
        else
           return false;
     }

     //**************************
     // The quick way to detect for a tier of devices.
     //   This method detects for all other types of phones,
     //   but excludes the iPhone and Smartphone Tier devices.
     function detectTierOtherPhones()
     {
        if ($this->detectIsMobile() == true)
        {
          if ($this->detectTierTouch() == true)
          {
             return false;
          }
          if ($this->detectTierSmartphones() == true)
          {
             return false;
          }
          else
             return true;
        }
        else
           return false;
     }
  } // MobileSmart
}

// -------------------------------------------------------------------------
// Instantiate class
// -------------------------------------------------------------------------
if (class_exists("MobileSmart"))
{
  $mobile_smart = new MobileSmart();
}


// -------------------------------------------------------------------------
// Actions and Filters
// -------------------------------------------------------------------------
if (isset($mobile_smart))
{
  // Activation
  register_activation_hook(__FILE__, array(&$mobile_smart, 'initialisePlugin'));

  // Actions
  add_action('admin_menu', MobileSmart_ap);
  add_action('setup_theme', array($mobile_smart, 'action_handleSwitcherLink'));
  add_action('wp_footer', array($mobile_smart, 'action_addSwitcherLinkInFooter'));

  // Filters
  add_filter('body_class', array(&$mobile_smart, 'filter_addBodyClasses'));
  add_filter('template', array(&$mobile_smart, 'filter_switchTheme'));
  add_filter('option_template', array(&$mobile_smart, 'filter_switchTheme'));
  add_filter('option_stylesheet', array(&$mobile_smart, 'filter_switchTheme'));
}

// -------------------------------------------------------------------------
// initialise the Admin Panel
// -------------------------------------------------------------------------
if (!function_exists("MobileSmart_ap"))
{
  function MobileSmart_ap()
  {
    global $mobile_smart;

    if (!isset($mobile_smart)) return;

    // add the options page
    if (function_exists('add_options_page'))
    {
      add_options_page("Mobile Smart", "Mobile Smart", 9, basename(__FILE__),
                       array(&$mobile_smart, 'displayAdminOptions'));
    }
  }
}

?>
