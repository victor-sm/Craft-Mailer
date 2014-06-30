<?php
namespace Craft;

/**
 * Mailer task
 */
class MailerTask extends BaseTask
{
	/**
	 * Defines the settings.
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'recipients'	=> AttributeType::Mixed,
			'email' 		=> AttributeType::Mixed,
			'batchMode' 	=> array(AttributeType::Bool, 'default' => craft()->plugins->getPlugin('mailer')->getSettings()->batchMode),
			'batchMails' 	=> array(AttributeType::Number, 'default' => craft()->plugins->getPlugin('mailer')->getSettings()->batchMails),
			'batchTime' 	=> array(AttributeType::Number, 'default' => craft()->plugins->getPlugin('mailer')->getSettings()->batchTime)
		);
	}


	/**
	 * Returns the default description for this task.
	 *
	 * @return string
	 */
	public function getDescription()
	{

		$description = Craft::t('Sending Mails');

		return $description;
	}


	/**
	 * Gets the total number of steps for this task.
	 *
	 * @return int
	 */
	public function getTotalSteps()
	{
		$steps = count($this->getSettings()->recipients->recipients);

		if (!is_int($steps)) {
			return 0;
		}

		return $steps;
	}


	/**
	 * Runs a task step.
	 *
	 * @param int $step
	 * @return bool
	 */
	public function runStep($step)
	{
		//BatchMode?
		if ($this->getSettings()->batchMode) {
			if ($step != 0 && ($step % $this->getSettings()->batchMails == 0)) {
				sleep($this->getSettings()->batchTime);
			}
		}


		//Get MailerModel
		$email = $this->getSettings()->email;


		//Get recipients for this task-loop
		$recipients = $this->getSettings()->recipients->recipients;
		$recipients = $recipients[$step];



		//Set recipients
		$email->toEmail = $recipients['to'];

		if (!empty($recipients['cc'])) {
			$email->cc 	= explode(',', $recipients['cc']); //Email Model needs array for CC-mails
		}
		else {
			$email->cc = null;
		}

		if (!empty($recipients['bcc'])) {
			$email->bcc = explode(',', $recipients['bcc']); //Email Model needs array for BCC-mails
		}
		else {
			$email->bcc = null;
		}
		

		//Send
		try {
			craft()->email->sendEmail($email);
		}
		catch (\Exception $e) {
			MailerPlugin::log($e->getMessage(), LogLevel::Error);
		}


		//Clean & Return
		unset($email);
		return true;
	}

}
