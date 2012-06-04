<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\BrowserBundle\Services;

/**
 * Detect browser and platfrom.
 * 
 * Requires browsecap (@see http://php.net/manual/en/function.get-browser.php)
 * 
 * @method boolean isiPhone()
 * @method boolean isiPad()
 * @method boolean isAndroid()
 * @method boolean isBlackBerry()
 * @method boolean isPalm()
 * @method boolean isSymbian()
 * @method boolean isWindowsMobile()
 * 
 * @method boolean isChrome()
 * @method boolean isOpera()
 * @method boolean isIE()
 * @method boolean isFirefox()
 * @method boolean isSafari()
 */
class BrowserDetector
{
    protected $info;
    protected $isTablet;
    
    protected $tabletDevices = array (
        'BlackBerryTablet' => 'PlayBook|RIM Tablet',
        'iPad' => 'iPad.*Mobile',
        'Kindle' => 'Kindle|Silk.*Accelerated',
        'SamsungTablet' => 'SCH\-I800|GT\-P1000|Galaxy.*Tab',
        'MotorolaTablet' => 'xoom|sholest',
        'AsusTablet' => 'Transformer|TF101',
        'NookTablet' => 'NookColor|nook browser|BNTV250A|LogicPD Zoom2',
        'GenericTablet' => 'Tablet|ViewPad7|LG\-V909|MID7015|BNTV250A|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b',
    );
    
    public function __construct()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) $this->info = get_browser();
    }
    
    /**
     * Magic method for any is...
     *   
     * @param string $name
     * @param array $arguments
     * @return mixed 
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 2) == 'is') return $this->matchBrowser(substr($name, 2));
        
        if ($name == 'name') return $this->getName();
        if (substr($name, 0, 3) == 'get') $name = substr($name, 3);
        return $this->getProperty($name);
    }
	
    /**
     * Returns true if any type of mobile device detected, including special ones.
     * 
     * @return boolean
     */
    public function isMobile()
    {
        return !empty($this->info->isMobile);
    } 
    
    /**
     * Return true if any type of tablet device is detected.
     * 
     * @return boolean 
     */
    public function isTablet()
    {
        if (!isset($this->isTablet)) $this->detectTablet();
        return $this->isTablet;
    }

    /**
     * Return true if any mobile device detected, that's not a phone.
     * 
     * @return boolean 
     */
    public function isPhone()
    {
        return $this->isMobile() && !$this->isTablet();
    }
    
    
    /**
     * Get the browser name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->info->browser;
    }
    
    /**
     * Cast to string.
     * 
     * @return string
     */
    public function __toString() {
        return $this->info->browser . (isset($this->info->version) ? ' ' . $this->info->version : '');
    }
    
    
    /**
     * Detect browser.
     * 
     * @param type $key
     * @return type 
     */
    protected function matchBrowser($key)
    {
        return strtolower(preg_replace('/\s/', '', $key)) == strtolower(preg_replace('/\s/', '', $this->info->browser));
    }
    
    /**
     * Detect if a define is a mobile phone or tables.
     * 
     * @return boolean
     */
    protected function detectTablet()
    {
        $this->isTablet = false;
        
        foreach ($this->tabletDevices as $key => $regex){
            if (preg_match('/'.$regex.'/is', $_SERVER['HTTP_USER_AGENT'])){
                $this->isTablet = true;
                break;
            }
        }
        
        return $this->isTablet;    
    }
    
    /**
     * Get the platform.
     * 
     * @return string
     */
    protected function getProperty($key)
    {
        $key = strtolower($key);        
        return isset($this->info->$key) ? $this->info->$key : null;
    }
}