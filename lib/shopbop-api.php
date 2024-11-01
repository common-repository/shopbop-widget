<?php

$shopbopPathArray = explode(DIRECTORY_SEPARATOR, __DIR__);
$shopbopRootPath = join(DIRECTORY_SEPARATOR, array_slice($shopbopPathArray, 0, array_search('wp-content', $shopbopPathArray)));

include_once $shopbopRootPath . '/wp-load.php';

if (version_compare( $wp_version, '4.7', '>=')) {
    include_once $shopbopRootPath . DIRECTORY_SEPARATOR . WPINC . '/IXR/class-IXR-message.php';
    include_once $shopbopRootPath . DIRECTORY_SEPARATOR . WPINC . '/IXR/class-IXR-value.php';
} else {
    include_once $shopbopRootPath . DIRECTORY_SEPARATOR . WPINC . '/class-IXR.php';
}

if(!class_exists("ShopbopCoreWidgetXmlRpc"))
{
    /**
     * XMLRPC methods for the widget.
     *
     * @package ShopbopCoreWidgetXmlRpc
     *
     * @author  widget <widget@stickyeyes.com>
     */
    class ShopbopCoreWidgetXmlRpc
    {
        /**
         * Widget prefix string for constants.
         *
         * @var string $widgetPrefix
         */
        public static $widgetPrefix = 'SHOPBOP_';

    	/**
    	 * Constructor.
         *
         * @return void
    	 */
    	public function __construct()
    	{

    	}

    	public function handleRequest()
        {
            if($_SERVER['REQUEST_METHOD'] != 'POST') {
                header('HTTP/1.0 405 Method Not Allowed');
                exit();
            }

            $xmlrpcMessage = new \IXR_Message(file_get_contents('php://input'));
            $xmlrpcMessage->parse();

            $methods = $this->xmlrpcMethods();

            if(!array_key_exists($xmlrpcMessage->methodName, $methods)) {
                header('HTTP/1.0 400 Bad Request');
                exit();
            }

            $result = call_user_func($methods[$xmlrpcMessage->methodName], $xmlrpcMessage->params);
            $responseBody = new \IXR_Value($result);

            header('HTTP/1.0 200 Ok');
            echo $responseBody->getXml();
            exit();
        }

        /**
         * Adds the xmlrpc function.
         *
         * @return array $methods list of the methods for xmlrpc.
         */
        public function xmlrpcMethods()
        {
            return array(
                "shopbop.setAuthKey"     => array($this, 'setAuthKey'),
                "shopbop.resetRequested" => array($this, 'resetRequested')
            );
        }


        /**
         * This takes the registration key and stores the key in to widget options.
         *
         * @param string $args registration key and additional data.
         *
         * @return string
         */
        public function setAuthKey($args = null)
        {
            $widgetWsOptions = get_option("ShopbopWidgetWsOptions");
            if(isset($widgetWsOptions['registered']) && $widgetWsOptions['registered'] === true)
                return false; // already registered

            if(isset($args) && is_string($args[0]))
            {
                $widgetWsOptions = array(
                    'widgetId'       => $args[0],
                    'registered'     => true,
                    'resetRequested' => false,
                );

                update_option("ShopbopWidgetWsOptions", $widgetWsOptions);

                return get_admin_url();
            }

            return false;
        }

        /**
         * This checks to see if a reset has been requested.
         *
         * @return boolean
         */
        public function resetRequested()
        {
            $widgetWsOptions = get_option("ShopbopWidgetWsOptions");

            if(!isset($widgetWsOptions['resetRequested']))
                return false;

            return $widgetWsOptions['resetRequested'];
        }
    }
}
$xmlRpc = new ShopbopCoreWidgetXmlRpc();
$xmlRpc->handleRequest();