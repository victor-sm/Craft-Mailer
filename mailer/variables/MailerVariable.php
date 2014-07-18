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

	public function log_records($id=null)
	{
		$logs = array();

		if ($id != null && !is_numeric($id)) {
			$logs = null;
		}
		elseif ($id == null) {
			$records 	= Mailer_LogRecord::model()->findAll(array('order' => 'id DESC'));
			$logs 		= Mailer_LogModel::populateModels($records);
		}
		else {
			$records = Mailer_LogRecord::model()->findbyPk($id);
			$logs 	 = Mailer_LogModel::populateModel($records);
		}

		return $logs;
	}
	
}