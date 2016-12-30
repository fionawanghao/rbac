<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
+------------------------------------------------------------------------------
* Bootstrap
+------------------------------------------------------------------------------
*
* @uses Yaf_Bootstrap_Abstract
* @package
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$
+------------------------------------------------------------------------------
*/
class Bootstrap extends Yaf_Bootstrap_Abstract
{
    // {{{ functions
    // {{{ public function _initConfig()

    /**
     * 初始化配置
     *
     * @access public
     * @return void
     */
    public function _initConfig()
    {
        $config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $config);
    }

    // }}}
    // {{{ public function _initConstant()

    /**
     * 初始化常量
     *
     * @access public
     * @return void
     */
    public function _initConstant()
    {
        define('UC_TABLE_UC_DOMAIN', Yaf_Registry::get('config')->table->uc_domain);
        define('UC_TABLE_UC_ROLE', Yaf_Registry::get('config')->table->uc_role);
        define('UC_TABLE_UC_USER', Yaf_Registry::get('config')->table->uc_user);
        define('UC_TABLE_UC_RESOURCE', Yaf_Registry::get('config')->table->uc_resource);
        define('UC_TABLE_UC_USER_DOMAIN_ROLE_RELATION', Yaf_Registry::get('config')->table->uc_user_domain_role_relation);
        define('UC_TABLE_UC_DOMAIN_ROLE_RESOURCE_RELATION', Yaf_Registry::get('config')->table->uc_domain_role_resource_relation);

    }

    // }}}
    // {{{ public function _initView()

    /**
     * 默认禁用模板
     *
     * @param Yaf_Dispatcher $dispatcher
     * @access public
     * @return void
     */
    public function _initView(Yaf_Dispatcher $dispatcher)
    {
        $dispatcher->disableView();
    }

    // }}}
    // {{{ public function _initLoader()

    /**
     * 初始化自动加载器
     *
     * @param Yaf_Dispatcher $dispatcher
     * @access public
     * @return void
     */
    public function _initLoader(Yaf_Dispatcher $dispatcher)
    {
        //Yaf_Loader::import(Yaf_Registry::get('config')->application->vendor . '/autoload.php');
    }

    // }}}
    // {{{ public function _initPlugin()

    /**
     * 初始化插件
     *
     * @param Yaf_Dispatcher $dispatcher
     * @access public
     * @return void
     */
    public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new RoutePlugin());
        //$dispatcher->registerPlugin(new CheckLogonPlugin());
    }

    // }}}
    // }}}
}
