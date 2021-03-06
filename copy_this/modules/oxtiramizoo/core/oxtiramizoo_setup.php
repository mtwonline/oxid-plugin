<?php

/**
 * This class is used to install or update oxTiramizoo module
 *
 * @package: oxTiramizoo
 */
class oxTiramizoo_Setup
{
    /**
     * Current version of oxTiramizoo module
     */
    const VERSION = '0.9.0';

    /**
     * Error message
     * @var string
     */
    protected $_migrationErrors = array();

    /**
     * Install or update module if needed
     */
    public function install()
    {
        $oxTiramizooConfig = oxRegistry::get('oxTiramizooConfig');

        $currentInstalledVersion = $oxTiramizooConfig->getShopConfVar('oxTiramizoo_version');

        $tiramizooIsInstalled = $oxTiramizooConfig->getShopConfVar('oxTiramizoo_is_installed');

        try 
        { 
            if (!$tiramizooIsInstalled || !$currentInstalledVersion) {
                $this->runMigrations();
                $oxTiramizooConfig->saveShopConfVar( "bool", 'oxTiramizoo_is_installed', 1);
            } else if ($tiramizooIsInstalled && (version_compare(oxTiramizoo_setup::VERSION, $currentInstalledVersion) > 0)) {
                $this->runMigrations();
            }

        } catch(oxException $e) {
            $errorMessage = $e->getMessage() . "<ul><li>" . implode("</li><li>", $this->_migrationErrors) . "</li></ul>";
            
            $this->getModule()->deactivate();

            throw new oxException($errorMessage);
        }
    }

    public function getModule()
    {
        $oModule = oxnew('oxModule');
        $oModule->load('oxTiramizoo');

        return $oModule;
    }

    /**
     * This method executes all migration methods newer than already installed version and older than new version
     */
    public function runMigrations()
    {
        $oxTiramizooConfig = oxRegistry::get('oxTiramizooConfig');

        $currentInstalledVersion = $oxTiramizooConfig->getShopConfVar('oxTiramizoo_version') ? $oxTiramizooConfig->getShopConfVar('oxTiramizoo_version') : '0.0.0';

        $migrationsMethods = $this->getMigrationMethods();

        foreach($migrationsMethods as $methodVersion => $migrationMethod)
        {
            if (version_compare($methodVersion, $currentInstalledVersion) > 0) {
                if (version_compare($methodVersion, oxTiramizoo_setup::VERSION) <= 0) {
                    call_user_func_array(array($this, $migrationMethod), array());

                    if ($this->stopMigrationsIfErrors()) {
                        throw new oxException('<p>Cannot execute the following sql queries:</p>');
                    }
                    oxTiramizooConfig::getInstance()->saveShopConfVar( "str", 'oxTiramizoo_version', $methodVersion);                    
                }
            }
        }
    }

    public function getMigrationMethods($class = __CLASS__)
    {
        $methodsNames = get_class_methods($class);

        $migrationsMethods = array();

        foreach ($methodsNames as $methodName) 
        {
            if (strpos($methodName, 'migration_') === 0) {
                $methodVersion = str_replace('migration_', '', $methodName);
                $methodVersion = str_replace('_', '.', $methodVersion);
                $migrationsMethods[$methodVersion] = $methodName;
            }
        }        

        uksort($migrationsMethods, 'version_compare');

        return $migrationsMethods;
    }

    public function stopMigrationsIfErrors()
    {
        $oxTiramizooConfig = oxRegistry::get('oxTiramizooConfig');

        if (count($this->_migrationErrors)) {
            //disable tiramizoo if db errors
            $oxTiramizooConfig->saveShopConfVar( "bool", 'oxTiramizoo_enable_module', 0);
            return true;
        } else {
            $oxTiramizooConfig->saveShopConfVar( "str", 'oxTiramizoo_update_errors', '');
            return false;
        }
    }

    /**
     * Update database to version 0.9.0 
     */
    public function migration_0_9_0()
    {
        $oTiramizooConfig = oxRegistry::get('oxTiramizooConfig');

        $this->executeSQL("CREATE TABLE IF NOT EXISTS oxtiramizooretaillocation (
                                OXID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL PRIMARY KEY,
                                OXSHOPID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
                                OXNAME varchar(128) NOT NULL DEFAULT '',
                                OXAPITOKEN varchar(128) NOT NULL DEFAULT ''
                           ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->executeSQL("CREATE TABLE IF NOT EXISTS oxtiramizooretaillocationconfig (
                                OXID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL PRIMARY KEY,
                                OXVARNAME varchar(128) NOT NULL DEFAULT '',
                                OXVARTYPE varchar(4) NOT NULL DEFAULT '',
                                OXVARVALUE TEXT NOT NULL,
                                OXLASTSYNC datetime NOT NULL,
                                OXRETAILLOCATIONID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
                           ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->executeSQL("CREATE TABLE IF NOT EXISTS oxtiramizooarticleextended (
                                OXID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL PRIMARY KEY,
                                TIRAMIZOO_ENABLE INT(1) NOT NULL DEFAULT 0,
                                TIRAMIZOO_USE_PACKAGE INT(1) NOT NULL DEFAULT 1,
                                OXARTICLEID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
                           ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->executeSQL("CREATE TABLE IF NOT EXISTS oxtiramizoocategoryextended (
                                OXID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL PRIMARY KEY,
                                TIRAMIZOO_ENABLE INT(1) NOT NULL DEFAULT 1,
                                TIRAMIZOO_WIDTH FLOAT NOT NULL DEFAULT 0,
                                TIRAMIZOO_HEIGHT FLOAT NOT NULL DEFAULT 0,
                                TIRAMIZOO_LENGTH FLOAT NOT NULL DEFAULT 0,
                                TIRAMIZOO_WEIGHT FLOAT NOT NULL DEFAULT 0,
                                TIRAMIZOO_USE_PACKAGE INT(1) NOT NULL DEFAULT 1,
                                OXCATEGORYID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
                           ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->executeSQL("CREATE TABLE IF NOT EXISTS oxtiramizooorderextended (
                                OXID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL PRIMARY KEY,
                                TIRAMIZOO_STATUS VARCHAR(255),
                                TIRAMIZOO_TRACKING_URL VARCHAR(1024) NOT NULL,
                                TIRAMIZOO_RESPONSE TEXT NOT NULL,
                                TIRAMIZOO_REQUEST_DATA TEXT NOT NULL,
                                TIRAMIZOO_WEBHOOK_RESPONSE TEXT NOT NULL,
                                TIRAMIZOO_EXTERNAL_ID VARCHAR(40) NOT NULL,
                                OXORDERID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
                           ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->executeSQL("CREATE TABLE IF NOT EXISTS oxtiramizooschedulejob (
                                OXID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL PRIMARY KEY,
                                OXSHOPID char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
                                OXJOBTYPE varchar(32),
                                OXPARAMS text NOT NULL DEFAULT '',
                                OXCREATEDAT datetime,
                                OXFINISHEDAT datetime,                                
                                OXRUNAFTER datetime,
                                OXRUNBEFORE datetime,
                                OXREPEATCOUNTER INT(11) NOT NULL DEFAULT 0,
                                OXEXTERNALID char(32),
                                OXSTATE varchar(32) NOT NULL DEFAULT 'new',
                                OXLASTERROR varchar(32)
                          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->executeSQL("INSERT IGNORE INTO oxdeliveryset SET
                                OXID = 'Tiramizoo',
                                OXSHOPID = '" . $oTiramizooConfig->getShopId() . "',
                                OXACTIVE = 0,
                                OXACTIVEFROM = '0000-00-00 00:00:00',
                                OXACTIVETO = '0000-00-00 00:00:00',
                                OXTITLE = 'Tiramizoo',
                                OXTITLE_1 = 'Tiramizoo',
                                OXTITLE_2 = 'Tiramizoo',
                                OXTITLE_3 = 'Tiramizoo',
                                OXPOS = 1;");

        if ($this->columnExistsInTable('OXSHOPINCL', 'oxdeliveryset')) {
            $this->executeSQL("UPDATE oxdeliveryset SET
                                    OXSHOPINCL = 1
                                WHERE
                                    OXID = 'Tiramizoo'
                                AND
                                    OXSHOPID = '" . $oTiramizooConfig->getShopId() . "'");
        }

         $this->executeSQL("INSERT IGNORE INTO oxdelivery SET
                                OXID = 'TiramizooStandardDelivery',
                                OXSHOPID = '" . $oTiramizooConfig->getShopId() . "',
                                OXACTIVE = 1,
                                OXACTIVEFROM = '0000-00-00 00:00:00',
                                OXACTIVETO = '0000-00-00 00:00:00',
                                OXTITLE = 'Tiramizoo Standard Delivery',
                                OXTITLE_1 = 'Tiramizoo Standard Delivery',
                                OXTITLE_2 = 'Tiramizoo Standard Delivery',
                                OXTITLE_3 = 'Tiramizoo Standard Delivery',
                                OXADDSUMTYPE = 'abs',
                                OXADDSUM = 8,
                                OXDELTYPE = 'p',
                                OXPARAM = 0,
                                OXPARAMEND = 999999,
                                OXFIXED = 0,
                                OXSORT = 1,
                                OXFINALIZE = 1;");

        if ($this->columnExistsInTable('OXSHOPINCL', 'oxdelivery')) {
            $this->executeSQL("UPDATE oxdelivery SET
                                    OXSHOPINCL = 1
                                WHERE
                                    OXID = 'TiramizooStandardDelivery'
                                AND
                                    OXSHOPID = '" . $oTiramizooConfig->getShopId() . "'");
        }

        $this->executeSQL("INSERT IGNORE INTO oxdel2delset SET
                                OXID = MD5(CONCAT('TiramizooStandardDelivery', 'Tiramizoo')),
                                OXDELID = 'TiramizooStandardDelivery',
                                OXDELSETID = 'Tiramizoo';");

        $oTiramizooConfig->saveShopConfVar( "str", 'oxTiramizoo_api_url', 'https://sandbox.tiramizoo.com/api/v1'); 
        $oTiramizooConfig->saveShopConfVar( "str", 'oxTiramizoo_shop_url', '');
        $oTiramizooConfig->saveShopConfVar( "bool", 'oxTiramizoo_articles_stock_gt_0', 1);
        $oTiramizooConfig->saveShopConfVar( "int", 'oxTiramizoo_package_strategy', 0);
    }


    /**
     * Execute sql query
     * 
     * @param string $sql SQL query to execute
     * @return: SQL query result
     */
    protected function executeSQL($sql)
    {
        $result = oxDb::getDb()->Execute($sql);

        if ($result === false) {
            $this->_migrationErrors[] = $sql;
        }

        return $result;
    }

    /**
     * Create sql query add column to table
     * 
     * @param string $tableName  Table name
     * @param string $columnName Column name
     * @param string $columnData Column datatype
     */
    protected function addColumnToTable($tableName, $columnName, $columnData)
    {
        if (!$this->columnExistsInTable($columnName, $tableName)) {
            $sql = "ALTER TABLE " . $tableName . " ADD " . $columnName . " " . $columnData . ";";
            $result = $this->executeSQL($sql);
        }
    }

    /**
     * Check if column exists in table
     * 
     * @param string $tableName  Table name
     * @param string $columnName Column name
     * @return boolean
     */
    protected function columnExistsInTable($columnName, $tableName)
    {
        $sql = "SHOW COLUMNS FROM " . $tableName . " LIKE '" . $columnName . "'";
        $result = oxDb::getDb()->Execute($sql);

        return $result->RecordCount() > 0;
    }
}