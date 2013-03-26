<?php

    if ( !class_exists('oxTiramizooConfig') ) {
        require_once getShopBasePath() . '/modules/oxtiramizoo/core/oxtiramizoo_config.php';
    }

    if ( !class_exists('oxTiramizooSetup') ) {
        require_once getShopBasePath() . '/modules/oxtiramizoo/core/oxtiramizoo_setup.php';
    }

    if ( !class_exists('oxTiramizooHelper') ) {
        require_once getShopBasePath() . '/modules/oxtiramizoo/core/oxtiramizoo_helper.php';
    }


    /**
    * Tiramizoo test class to add some data and test...
    *
    * @package: oxTiramizoo
    */
    class oxTiramizoo_test extends Shop_Config
    {

    public function init()
    {
    }

    /**
    * Executes parent method parent::render() and returns name of template
    *
    * @return string
    */
    public function render()
    {

        $serviceAreasResponse = oxTiramizooConfig::getInstance()->getShopConfVar('service_areas_70197');

        print_r($serviceAreasResponse);

        die('OK');
    }


}