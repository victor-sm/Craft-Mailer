<?php
namespace Craft;

class MailerVariable
{
	
    public function name()
	{
		$plugin = craft()->plugins->getPlugin('mailer');

		return $plugin->getName();
	}
	
	public function testmode()
	{
		$testmode = craft()->config->get('testToEmailAddress');

		return $testmode;
	}
	
}