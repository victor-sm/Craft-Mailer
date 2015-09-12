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

			'log' 			=> AttributeType::Mixed,
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

		$description = craft()->plugins->getPlugin('mailer')->getName();

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


		//Log
		if ($step == 0) {
			//Log
			$desc = str_replace(craft()->plugins->getPlugin('mailer')->getName().': ', '', $this->model->description);
			MailerPlugin::log('MailerTask ('.$desc.'): Task started: Step 0', LogLevel::Info);
			
			//Create & Save LogRecord
			$record = new Mailer_LogRecord;
			$record->setIsNewRecord(true);

			$record->subject 		= $email->subject;
			$record->htmlBody 		= $email->htmlBody;
			$record->status 		= 'running';
			$record->description 	= $desc;

			$record->save();
			
			if ($record->hasErrors()) {
				MailerPlugin::log('MailerTask ('.$record->description.'): Failed to save LogRecord: "'. implode('; ', $record->getErrors()) .'"', LogLevel::Error);
			}
			else {
				MailerPlugin::log('MailerTask ('.$record->description.'): New LogRecord created', LogLevel::Info);
			}

			//Create LogModel
			$log = new Mailer_LogModel;
			$log->id 		= $record->id;
			$log->success	= 0;
			$log->errors 	= array();
		}
		else {
			$log = $this->getSettings()->log;
		}


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

			//Add to Log
			MailerPlugin::log('MailerTask ('.$record->description.'): Mail send to "'.$email->toEmail.'"', LogLevel::Info);
			$log->success++;
		}
		catch (\Exception $e) {
			MailerPlugin::log($e->getMessage(), LogLevel::Error);

			//Add to Log
			$errors = $log->errors;
			$errors[] = array('message' => $e->getMessage(), 'email' => $email->toEmail);
			$log->errors = $errors;
		}


		//Update Log
		if ($step == $this->getTotalSteps()-1) {
			//Get LogRecord
			$record = Mailer_LogRecord::model()->findbyPk($log->id);
			
			//Set attributes from LogModel
			$record->dateFinished 	= new DateTime();
			$record->errors 		= $log->errors;
			$record->success 		= $log->success;

			if (count($record->errors) > 0) {
				$record->status = 'failed';
			}
			else {
				$record->status = 'finished';
			}

			//Update
			$record->update();
			
			if ($record->hasErrors()) {
				MailerPlugin::log('MailerTask ('.$record->description.'): Last Step / Failed to update LogRecord: "'. implode('; ', $record->getErrors()) .'"', LogLevel::Error);
			}
			else {
				MailerPlugin::log('MailerTask ('.$record->description.'): Last Step / LogRecord successfully updated', LogLevel::Info);
			}
		}
		else {
			//Save to settings
			$this->getSettings()->log = $log;
		}


		//Clean & Return
		unset($log);
		unset($email);
		unset($recipients);
		return true;
	}

}
