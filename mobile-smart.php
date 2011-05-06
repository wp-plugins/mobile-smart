<?php
/*
Plugin Name: Mobile Smart
Plugin URI: http://www.dansmart.co.uk
Version: v1.2
Author: <a href="http://www.dansmart.co.uk/">Dan Smart</a>
Description: Mobile Smart contains helper tools for mobile devices, including allowing
             determination of mobile device type or tier in CSS and PHP code, using
             detection by Mobile ESP project.
 */

/*  Copyright 2011 Dan Smart  (email : dan@dansmart.co.uk)

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


/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 * Attributation:
 *  - Detection performed by MobileESP project code (www.mobileesp.com)
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */


// -------------------------------------------------------------------------
// Defines
// -------------------------------------------------------------------------
define('MOBILESMART_DOMAIN', 'mobilesmart');
define('MOBILESMART_PLUGIN_PATH', WP_PLUGIN_DIR . '\mobile-smart');

// MAIN DEVICES (for more, see lib/mdetect.php which can be detected directly)
define ('MOBILE_DEVICE_OPERA_MINI', 'operamini');
define ('MOBILE_DEVICE_IPHONE', 'iphone');
define ('MOBILE_DEVICE_IPAD', 'ipad');
define ('MOBILE_DEVICE_IPOD', 'ipod');
define ('MOBILE_DEVICE_ANDROID', 'android');
define ('MOBILE_DEVICE_ANDROID_WEBKIT', 'android_webkit');
define ('MOBILE_DEVICE_ANDROID_TABLET', 'android table');
define ('MOBILE_DEVICE_SERIES60', 'series_60');
define ('MOBILE_DEVICE_SYMBIAN_OS', 'symbian_os');
define ('MOBILE_DEVICE_WINDOWS_MOBILE', 'windows_mobile');
define ('MOBILE_DEVICE_WINDOWS_PHONE_7', 'windows_phone_7');
define ('MOBILE_DEVICE_BLACKBERRY', 'blackberry');
define ('MOBILE_DEVICE_BLACKBERRY_TABLET', 'blackberry_tablet');
define ('MOBILE_DEVICE_BLACKBERRY_WEBKIT', 'blackberry_webkit');
define ('MOBILE_DEVICE_BLACKBERRY_TOUCH', 'blackberry_touch');
define ('MOBILE_DEVICE_PALM_OS', 'palm_os');
define ('MOBILE_DEVICE_OTHER', 'other_mobile');

// TIERS
define ('MOBILE_DEVICE_TIER_TOUCH', 'mobile-tier-touch');
define ('MOBILE_DEVICE_TIER_TABLET', 'mobile-tier-tablet');
define ('MOBILE_DEVICE_TIER_RICH_CSS', 'mobile-tier-rich-css');
define ('MOBILE_DEVICE_TIER_SMARTPHONE', 'mobile-tier-smartphone');
define ('MOBILE_DEVICE_TIER_OTHER', 'mobile-tier-other-mobile');

// MANUAL SWITCHING
define ('MOBILESMART_SWITCHER_GET_PARAM', 'mobile_switch');
define ('MOBILESMART_SWITCHER_MOBILE_STR', 'mobile');
define ('MOBILESMART_SWITCHER_DESKTOP_STR', 'desktop');
define ('MOBILESMART_SWITCHER_COOKIE', 'mobile-smart-switcher');
define ('MOBILESMART_SWITCHER_COOKIE_EXPIRE', 3600); // 3600


// SOME DUMMY TIER SCREEN DIMENSIONS FOR TRANSCODING IMAGES
define ('MOBILE_DEVICE_TIER_TOUCH_MAX_WIDTH', 300);
define ('MOBILE_DEVICE_TIER_TOUCH_MAX_HEIGHT', 400);
define ('MOBILE_DEVICE_TIER_TABLET_MAX_WIDTH', 300);
define ('MOBILE_DEVICE_TIER_TABLET_MAX_HEIGHT', 400);
define ('MOBILE_DEVICE_TIER_RICH_CSS_MAX_WIDTH', 300);
define ('MOBILE_DEVICE_TIER_RICH_CSS_MAX_HEIGHT', 400);
define ('MOBILE_DEVICE_TIER_SMARTPHONE_MAX_WIDTH', 200);
define ('MOBILE_DEVICE_TIER_SMARTPHONE_MAX_HEIGHT', 250);
define ('MOBILE_DEVICE_TIER_OTHER_MAX_WIDTH', 100);
define ('MOBILE_DEVICE_TIER_OTHER_MAX_HEIGHT', 150);

// -------------------------------------------------------------------------
// Includes
// -------------------------------------------------------------------------
require_once('lib/mdetect.php');
require_once('mobile-smart-switcher-widget.php');

// -------------------------------------------------------------------------
// Plugin Class
// -------------------------------------------------------------------------
if (!class_exists("MobileSmart"))
{
  class MobileSmart extends uagent_info
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

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // Method: Constructor
    // -------------------------------------------------------------------------
    // PHP 4 version
    function MobileSmart()
    {
      // init parent constructor
      parent::uagent_info();
      
      // translations
      load_plugin_textdomain(MOBILESMART_DOMAIN, MOBILESMART_PLUGIN_PATH.'/language');

      if (isset($_COOKIE[MOBILESMART_SWITCHER_COOKIE]))
      {
        $this->switcher_cookie = $_COOKIE[MOBILESMART_SWITCHER_COOKIE];
        //echo "Construct cookie: $this->switcher_cookie<br/><br/>";
      }
    }
    
    // PHP 5 version
    function __construct()
    {
      $this->MobileSmart();
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
      
      $themes = get_themes();
      
      /*echo '<pre>';
      print_r($themes);
      echo '</pre>';*/

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
        
        // Enable / Disable image transcoding
        if (isset($_POST['enable_image_transcoding']))
        {
          if ($options['enable_image_transcoding'] != true)
          {
            $options['enable_image_transcoding'] = true;

            $status_messages[] = array('updated', __('Image transcoding enabled.', MOBILESMART_DOMAIN));
          }
        }
        else
        {
          if ($options['enable_image_transcoding'] != false)
          {
            $options['enable_image_transcoding'] = false;

            $status_messages[] = array('updated', __('Image transcoding disabled.', MOBILESMART_DOMAIN));
          }
        }

        // Get choice of mobile theme
        if ($options['mobile_theme'] != $_POST['theme'])
        {
          $theme_name = $_POST['theme'];
          
          if (array_key_exists($theme_name, $themes))
          {
            $options['mobile_theme'] = $themes[$theme_name]['Template'];
            $options['mobile_theme_stylesheet'] = $themes[$theme_name]['Stylesheet'];

            $status_messages[] = array('updated', __('Mobile theme updated to: ', MOBILESMART_DOMAIN) . $_POST['theme']);
          }
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
                  foreach ($themes as $theme_name => $theme)
                  {
                    ?>
                    <option value="<?php echo $theme_name; ?>" <?php if ($theme['Template'] == $options['mobile_theme']) { echo "selected"; } ?>><?php echo $theme['Name']; ?></option>
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
          <h3>Transcoding</h3>
          <h4>In development: Enable image transcoding</h4>
          <p>Do not enable this unless you want to try the TimThumb powered image transcoding.</p>
          <p><em>Manual switching (above) must be enabled for this to work properly.</em></p>
          <label for="enable_image_transcoding">Enable image transcoding <input type="checkbox" name="enable_image_transcoding" id="enable_image_transcoding" <?php if ($options['enable_image_transcoding']) { echo "checked"; } ?>/>
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
      return $this->Get_Uagent();
    }

    // ---------------------------------------------------------------------------
    // Function: getAcceptString
    // Description: gets the accept string
    // ---------------------------------------------------------------------------
    function getAcceptString()
    {
      return $this->Get_HttpAccept();
    }

    // ---------------------------------------------------------------------------
    // Function: getCurrentDevice
    // Description: gets the current device
    // ---------------------------------------------------------------------------
    function getCurrentDevice()
    {
      if ($this->device == '')
      {
        if ($this->DetectOperaMini())
        {
          $this->device = MOBILE_DEVICE_OPERA_MINI;
        }
        else if ($this->DetectIpad())
        {
          $this->device = MOBILE_DEVICE_IPAD;
        }
        else if ($this->DetectIphone())
        {
          $this->device = MOBILE_DEVICE_IPHONE;
        }
        else if ($this->DetectIpod())
        {
          $this->device = MOBILE_DEVICE_IPOD;
        }
        else if ($this->DetectAndroid())
        {
          $this->device = MOBILE_DEVICE_ANDROID;
        }
        else if ($this->DetectAndroidTablet())
        {
          $this->device = MOBILE_DEVICE_ANDROID_TABLET;
        }
        else if ($this->DetectAndroidWebkit())
        {
          $this->device = MOBILE_DEVICE_ANDROID_WEBKIT;
        }
        else if ($this->DetectSeries60())
        {
          $this->device = MOBILE_DEVICE_SERIES60;
        }
        else if ($this->DetectSymbianOS())
        {
          $this->device = MOBILE_DEVICE_SYMBIAN_OS;
        }
        else if ($this->DetectWindowsMobile())
        {
          $this->device = MOBILE_DEVICE_WINDOWS_MOBILE;
        }
        else if ($this->DetectWindowsPhone7())
        {
          $this->device = MOBILE_DEVICE_WINDOWS_PHONE_7;
        }
        else if ($this->DetectBlackBerry())
        {
          $this->device = MOBILE_DEVICE_BLACKBERRY;
        }
        else if ($this->DetectBlackBerryTablet())
        {
          $this->device = MOBILE_DEVICE_BLACKBERRY_TABLET;
        }
        else if ($this->DetectBlackBerryWebkit())
        {
          $this->device = MOBILE_DEVICE_BLACKBERRY_WEBKIT;
        }
        else if ($this->DetectBlackBerryTouch())
        {
          $this->device = MOBILE_DEVICE_BLACKBERRY_TOUCH;
        }
        else if ($this->DetectPalmOS())
        {
          $this->device = MOBILE_DEVICE_PALM_OS;
        }
        else if ($this->DetectIsMobile())
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
        if ($this->DetectTierTablet())
        {
          $this->device_tier = MOBILE_DEVICE_TIER_TABLET;
        }
        if ($this->DetectTierIphone())
        {
          $this->device_tier = MOBILE_DEVICE_TIER_TOUCH;
        }
        if ($this->DetectTierRichCSS())
        {
          $this->device_tier = MOBILE_DEVICE_TIER_RICH_CSS;
        }
        if ($this->DetectTierRichCss())
        {
          $this->device_tier = MOBILE_DEVICE_TIER_SMARTPHONE;
        }
        if ($this->DetectTierOtherPhones())
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
        if ($this->DetectIsMobile())
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
    
    // ---------------------------------------------------------------------------
    // Function: filter_switchTheme_stylesheet
    // Description: switches the theme if it's a mobile device to the specified theme - stylesheet - for child themes
    // - Filter: see add_filter('template'...)
    // ---------------------------------------------------------------------------
    function filter_switchTheme_stylesheet($theme)
    {
      // get options
      $options = $this->getAdminOptions();

      // if theme switching enabled
      if ($options['enable_theme_switching'] == true)
      {
        // if is a mobile device or is mobile due to cookie switching
        if ($this->switcher_isMobile())
        {
          $theme = $options['mobile_theme_stylesheet'];
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
        $detectmobile = $this->DetectIsMobile();

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
     // Function: DetectIsMobile
     // Description: is it a mobile device (including iPad)
     // ---------------------------------------------------------------------------
     function DetectIsMobile()
     {
       return ( $this->DetectMobileQuick() || $this->DetectIpad() );
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
        setcookie(MOBILESMART_SWITCHER_COOKIE,
                  $version,
                  time()+MOBILESMART_SWITCHER_COOKIE_EXPIRE,
                  COOKIEPATH,
                  str_replace('http://www','',get_bloginfo('url')));

        // save version in class for viewing the page before a refresh
        $this->switcher_cookie = $version;

        //echo "Version to set: $version<br/>";
        //echo "Set version: $this->switcher_cookie<br/><br/>";
      }
    }
    
    // ---------------------------------------------------------------------------
    // Function: isTierTablet
    // Description: is the current device tier - table
    // ---------------------------------------------------------------------------
    function isTierTablet()
    {
      return $this->getCurrentDeviceTier() == MOBILE_DEVICE_TIER_TABLET;
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
    // Function: isTierRichCSS
    // Description: is the current device tier - Rich CSS
    // ---------------------------------------------------------------------------
    function isTierRichCSS()
    {
      return $this->getCurrentDeviceTier() == MOBILE_DEVICE_TIER_RICH_CSS;
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

     /**
      * Magic function - to catch old naming scheme of method with decapitalised first character. Change was caused by inclusion of mdetect.php
      * @param type $name
      * @param type $arguments 
      */
     function __call($name, $arguments)
     {
       $old_naming_scheme = ucwords($name);
       
       // check for method with capitalised first character - for backwards compatibility, as previous plugin had lowercase first characters in method name
       if (method_exists($this, $old_naming_scheme))
       {
         $name($arguments);
       }
     }

     // ------------------------------------------------------------------------
     // Function: filter_processContent
     // Description: processes the post's content and transcodes the post's images
     // Credits: idea and regexp taken from wpmp_transcoder.php, but brought into
     //          MobileSmart domain with improvements
     // ------------------------------------------------------------------------
     function filter_processContent($the_content)
     {
       $options = $this->getAdminOptions();
       
       // only process the content if we're in mobile mode
      if (!$this->switcher_isMobile() || !$options['enable_image_transcoding'])
        return $the_content;
     
       preg_match_all("/\<img.* src=((?:'[^']*')|(?:\"[^\"]*\")).*\>/Usi", $the_content, $images);

       foreach ($images[0] as $images_key=>$image)
       {
        $img_src = $images[1][$images_key];

        // remove the site url
        $site_url = str_replace('/', '\/', get_bloginfo('siteurl'));
        $img_src = preg_replace("/[\"|']".$site_url."(.*)[\"|']/", '\1', $img_src);

        // get the width and height
        preg_match_all("/(width|height)[=:'\"\s]*(\d+)(?:px|[^\d])/Usi", $image, $img_dimensions);

        $width = 0; $height = 0;
        foreach ($img_dimensions[2] as $dim_index=>$dim_val)
        {
          if ($img_dimensions[1][$dim_index] == 'height')
            $height = $dim_val;
          else if ($img_dimensions[1][$dim_index] == 'width')
            $width = $dim_val;
        }

        // * * * * * * *
        // to do: get max dimensions of images for each device / tier from somewhere like WURFL
        switch ($this->deviceTier)
        {
          case MOBILE_DEVICE_TIER_TOUCH: $max_width = MOBILE_DEVICE_TIER_TOUCH_MAX_WIDTH; $max_height = MOBILE_DEVICE_TIER_TOUCH_MAX_HEIGHT; break;
          case MOBILE_DEVICE_TIER_TABLET: $max_width = MOBILE_DEVICE_TIER_TABLET_MAX_WIDTH; $max_height = MOBILE_DEVICE_TIER_TABLET_MAX_HEIGHT; break;
          case MOBILE_DEVICE_TIER_SMARTPHONE: $max_width = MOBILE_DEVICE_TIER_SMARTPHONE_MAX_WIDTH; $max_height = MOBILE_DEVICE_TIER_SMARTPHONE_MAX_HEIGHT; break;
          case MOBILE_DEVICE_TIER_RICH_CSS: $max_width = MOBILE_DEVICE_TIER_RICH_CSS_MAX_WIDTH; $max_height = MOBILE_DEVICE_TIER_RICH_CSS_MAX_HEIGHT; break;
          case MOBILE_DEVICE_TIER_OTHER: $max_width = MOBILE_DEVICE_TIER_OTHER_MAX_WIDTH; $max_height = MOBILE_DEVICE_TIER_OTHER_MAX_HEIGHT; break;
          default: $max_width = 100; $max_height = 100; break;
        }
        // * * * * * * *

        // rescale image
        if ($width > $max_width)
        {
          $height = floor($width / $max_width) * $height;
          $width = $max_width;
        }

        if ($height > $max_height)
        {
          $width = floor($height / $max_height) * $width;
          $height = $max_height;
        }

        // create new rescaled image
        $rescaled_image = '<img src="'.MOBILESMART_PLUGIN_URL.'/includes/timthumb.php?src='.$img_src.'&w='.$width.'&h='.$height.'&zc=0"'
                          .' width="'.$width.'"'.' height="'.$height.'"'.'/>';

        // replace the entire text of the old image with the text of the resized image
        $the_content = str_replace($image, $rescaled_image, $the_content);
       }

       return $the_content;
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

  // Switcher {
    // Actions
    add_action('admin_menu', MobileSmart_ap);
    add_action('setup_theme', array($mobile_smart, 'action_handleSwitcherLink'));
    add_action('wp_footer', array($mobile_smart, 'action_addSwitcherLinkInFooter'));

    // Filters
    add_filter('body_class', array(&$mobile_smart, 'filter_addBodyClasses'));
    add_filter('template', array(&$mobile_smart, 'filter_switchTheme'));
    add_filter('stylesheet', array(&$mobile_smart, 'filter_switchTheme_stylesheet'));
 // } End Switcher

  // Content transformation {
    // Filters
    add_filter('the_content', array(&$mobile_smart, 'filter_processContent'));
  // } End Content transformation
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
