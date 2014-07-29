<?php
/**
 * @package   Mailer
 * @author    Victor In.
 * @copyright Copyright 2014
 * @link      https://github.com/victor-in/Craft-Mailer
 * @license   MIT
 */
namespace Craft;

class MailerPlugin extends BasePlugin
{
    function getName()
    {
		return $this->getSettings()->name;
    }

    function getVersion()
    {
        return '0.3';
    }

    function getDeveloper()
    {
        return 'Victor In.';
    }

    function getDeveloperUrl()
    {
        return 'https://github.com/victor-in/';
    }
	
	public function hasCpSection()
    {
		return true;
    }
	
	protected function defineSettings()
    {
        return array(
            'name'       => array(AttributeType::String, 'default' => 'Mailer'),
            'safeMode'   => array(AttributeType::Bool, 'default' => true),
            'batchMode'  => array(AttributeType::Bool, 'default' => true),
            'batchMails' => array(AttributeType::Number, 'default' => '300'),
            'batchTime'  => array(AttributeType::Number, 'default' => '60')
        );
    }

    public function registerCpRoutes()
    {
        return array(
            'mailer/log\/(?P<logId>\S+)' => 'mailer/_log',
        );
    }
	
	public function getSettingsHtml()
	{
		return craft()->templates->render('mailer/_settings', array(
			'settings' => $this->getSettings()
		));
	}
	
	public function prepSettings($settings)
    {
		return $settings;
    }
	
	public function init()
	{
		parent::init();
		
		//Include plugin JS
        if(!craft()->isConsole()){
            if (craft()->request->isCpRequest() && craft()->request->getSegment(1) == 'mailer') {
    			craft()->templates->includeJsFile( UrlHelper::getResourceUrl('mailer/mailer.js') );

                craft()->log->removeRoute('WebLogRoute');
                craft()->log->removeRoute('ProfileLogRoute');
            }
        }
	}
	
}
