<?php
/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * All rights reserved.
 *
 * Open Source Solutions Limited is a company registered in Dublin,
 * Ireland with the Companies Registration Office (#438231). We
 * trade as Open Solutions with registered business name (#329120).
 *
 * Contact: Barry O'Donovan - info (at) opensolutions (dot) ie
 *          http://www.opensolutions.ie/
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL:
 *     http://www.opensolutions.ie/licenses/new-bsd
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@opensolutions.ie so we can send you a copy immediately.
 *
 * @category   OSS
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Resource
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Resource_Moduleconfig extends Zend_Application_Resource_ResourceAbstract
{

    /**
    * Initialize
    *
    * @return Zend_Config
    */
    public function init()
    {
        return $this->_getModuleConfig();
    }


    /**
    * Load the module's config
    *
    * @return Zend_Config
    */
    protected function _getModuleConfig()
    {
        $bootstrap = $this->getBootstrap();

        if (!($bootstrap instanceof Zend_Application_Bootstrap_Bootstrap)) throw new Zend_Application_Exception('Invalid bootstrap class');

        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $bootstrap->getModuleName() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;

        $cfgdir = new DirectoryIterator($path);
        $modOptions = $this->getBootstrap()->getOptions();

        foreach ($cfgdir as $file)
        {
            if ($file->isFile())
            {
                $filename = $file->getFilename();

                if (in_array(substr(trim(strtolower($filename)), -3), array('ini', 'xml')) == true)
                {
                    $options = $this->_loadOptions($path . $filename);

                    if (($len = strpos($filename, '.')) !== false)
                    {
                        $cfgtype = substr($filename, 0, $len);
                    }
                    else
                    {
                        $cfgtype = $filename;
                    }

                    if (strtolower($cfgtype) == 'module')
                    {
                        $modOptions = array_merge($modOptions, $options);
                    }
                    else
                    {
                        //$modOptions['resources'][$cfgtype] = $options;
                        $modOptions = array_merge($modOptions, $options);
                    }
                }
            }
        }

        $this->getBootstrap()->setOptions($modOptions);
    }


    /**
    * Load the config file
    *
    * @param string $fullpath
    * throws Zend_Config_Exception
    * @return array
    */
    protected function _loadOptions($fullpath)
    {
        if (file_exists($fullpath))
        {
            switch (substr(trim(strtolower($fullpath)), -3))
            {
                case 'ini': $cfg = new Zend_Config_Ini($fullpath, $this->getBootstrap()->getEnvironment()); break;
                case 'xml': $cfg = new Zend_Config_Xml($fullpath, $this->getBootstrap()->getEnvironment()); break;
                default: throw new Zend_Config_Exception('Invalid format for config file'); break;
            }
        }
        else
        {
            throw new Zend_Application_Resource_Exception('Ini file does not exist.');
        }

        return $cfg->toArray();
    }

}

