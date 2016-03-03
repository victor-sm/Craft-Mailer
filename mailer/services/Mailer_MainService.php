<?php
namespace Craft;

class Mailer_MainService extends BaseApplicationComponent
{
    
    /**
	 * Create MailerTasks from passed $formData
	 *
	 * @param Mailer_FormModel $formData
	 * @return bool
	 */
	public function newMailer_fromForm(Mailer_FormModel $formData)
	{			
		//Create EmailModel
		$email = new EmailModel();
		
		$email->subject 	= $formData->subject;
		$email->fromName 	= $formData->sender_name;
		$email->fromEmail 	= $formData->sender_mail;
		$email->htmlBody 	= $formData->htmlBody;

		if ($formData->attachment) {
			$email->attachments = $formData->attachment;
		}

		

		//Custom Mailer
		if ($formData->sendto_custom) {
			//Log
			MailerPlugin::log('Creating Custom Mailer_RecipientsModel', LogLevel::Info);
			
			//Create recipients array
			$custom_recipients = array(
				array(
					'to' => $formData->to,
					'cc' => $formData->cc,
					'bcc' => $formData->bcc
				)
			);

			//Save to model
			$recipients = new Mailer_RecipientsModel();
			$recipients->recipients = $custom_recipients;

			//Check
			if (!$recipients->validate()) {
				MailerPlugin::log('Custom Mailer_RecipientsModel validation errors: "'. implode('; ', $recipients->getErrors()) .'"', LogLevel::Error);
				return false;
			}
			else {
				MailerPlugin::log('Custom Mailer_RecipientsModel validation successful', LogLevel::Info);
			}

			//Create mailer
			$this->newMailer($recipients, $email, Craft::t('Custom') );

			//Clean
			unset($recipients);
		}
		
		
		//Usergroups Mailer
		if ($formData->sendto_usergroups) {
			//Log
			MailerPlugin::log('Creating UserGroups Mailer_RecipientsModel', LogLevel::Info);
			
			//Get Recipients
			$usergroup_recipients = $this->getUserGroupRecipients($formData->usergroups, $formData->users);

			if ($usergroup_recipients != null) { //null = All users got excluded

				//Save to model
				$recipients = new Mailer_RecipientsModel();
				$recipients->recipients = $usergroup_recipients;

				//Check
				if (!$recipients->validate()) {
					MailerPlugin::log('UserGroups Mailer_RecipientsModel validation errors: "'. implode('; ', $recipients->getErrors()) .'"', LogLevel::Error);
					return false;
				}
				else {
					MailerPlugin::log('UserGroups Mailer_RecipientsModel validation successful', LogLevel::Info);
				}

				//Create mailer
				$this->newMailer($recipients, $email, Craft::t('User Groups'));

				//Clean
				unset($recipients);
			}
		}
		
		
		//Users Mailer
		if ($formData->sendto_users) {
			//Log
			MailerPlugin::log('Creating Users Mailer_RecipientsModel', LogLevel::Info);
			
			//Get Recipients
			$user_recipients = $this->getUserRecipients($formData->users);

			//Save to model
			$recipients	= new Mailer_RecipientsModel();
			$recipients->recipients = $user_recipients;

			//Check
			if (!$recipients->validate()) {
				MailerPlugin::log('Users Mailer_RecipientsModel validation errors: "'. implode('; ', $recipients->getErrors()) .'"', LogLevel::Error);
				return false;
			}
			else {
				MailerPlugin::log('Users Mailer_RecipientsModel validation successful', LogLevel::Info);
			}

			//Create mailer
			$this->newMailer($recipients, $email, Craft::t('Users'));

			//Clean
			unset($recipients);
		}
		
		
		return true;
	}




	/**
	 * Start a MailerTask
	 *
	 * @param Mailer_RecipientsModel $recipients
	 * @param EmailModel $email
	 * @return bool
	 */
	public function newMailer(Mailer_RecipientsModel $recipients, EmailModel $email, $description=null)
	{
		//Description
		$description = craft()->plugins->getPlugin('mailer')->getName().': '.$description;
		
		//Log
		MailerPlugin::log('Create new MailerTask: '.$description, LogLevel::Info);

		//StartTask
		$mailer_task = craft()->tasks->createTask('Mailer', $description, array(
			'recipients' 	=> $recipients,
			'email' 		=> $email
		));
		
		if ($mailer_task->hasErrors()) {
			MailerPlugin::log('Failed to create a MailerTask: "'. implode('; ', $mailer_task->getErrors()) .'"', LogLevel::Error);
		}
		else {
			MailerPlugin::log('New MailerTask successfully created: "'.$description.'"', LogLevel::Info);
		}


		return true;
	}




	/**
	 * Creates a recipients-array from UserGroup-Ids
	 *
	 * @param array $usergroup_ids
	 * @param array $exlude_user_ids
	 * @return array (Or null if all users got excluded)
	 */
	public function getUserGroupRecipients($usergroup_ids, $exlude_user_ids=array())
	{	
		//Get all users from specified usergroups
		$criteria 			= craft()->elements->getCriteria(ElementType::User);
		$criteria->groupId 	= $usergroup_ids;
		$criteria->limit = null;
		$users 				= $criteria->find();


		//Include admins?
		if (in_array('admin', $usergroup_ids)) {
			$admin_criteria 		= craft()->elements->getCriteria(ElementType::User);
			$admin_criteria->admin 	= true;
			$admin_users 			= $admin_criteria->find();

			$users = array_merge($admin_users, $users);
		}


		//Get ids of those users
		$user_ids = array();

		foreach ($users as $user) {
			$user_ids[] = $user->id;
		}


		//Remove duplicates
		$user_ids = array_unique($user_ids);


		//Check
		if (count($user_ids) == 0) {
			MailerPlugin::log('getUserGroupRecipients(): No Users found in passed UserGroups. UserGroups IDs: "'. implode(', ', $usergroup_ids) .'"', LogLevel::Error);
			return false;
		}
		else {
			MailerPlugin::log('getUserGroupRecipients(): Number of valid Users: '.count($user_ids), LogLevel::Info);
		}


		//Exclude IDs
		if (!empty($exlude_user_ids)) {
			$user_ids = array_diff($user_ids, $exlude_user_ids);

			if (count($user_ids) == 0) {
				//All users got excluded
				MailerPlugin::log('getUserGroupRecipients(): All Users excluded', LogLevel::Info);
				return null;
			}
		}


		//Get recipients-array
		$recipients = $this->getUserRecipients($user_ids);
		

		//Check
		if (!$recipients) {
			return false;
		}


		return $recipients;
	}


	/**
	 * Creates a recipients-array from User-Ids
	 *
	 * @param array $user_ids
	 * @return array
	 */
	public function getUserRecipients($user_ids)
	{
		//Get all Users by ids
 		$criteria 		= craft()->elements->getCriteria(ElementType::User);
		$criteria->id 	= $user_ids;
		$criteria->limit = null;
		$users 			= $criteria->find();
		

		//Create recipients-array
		$recipients = array();

		foreach ($users as $user) {
			$recipients[] = array(
					'to' => $user->email
				);
		}


		//Check
		if (count($recipients) == 0) {
			MailerPlugin::log('getUserRecipients(): No Users found with IDs: "'. implode(', ', $user_ids) .'"', LogLevel::Error);
			return false;
		}
		else {
			MailerPlugin::log('getUserRecipients(): Number of valid Recipients: '.count($recipients), LogLevel::Info);
		}


		return $recipients;
	}




	/**
	 * Create CSV from User data
	 *
	 * @param array $exportData
	 * @return string
	 */
	public function createUserCSV($exportData)
	{
		$user_ids = array();

		//UserGroup
		if ($exportData->sendto_usergroups) {
			//Criteria
			$criteria 			= craft()->elements->getCriteria(ElementType::User);
			$criteria->groupId 	= $exportData->usergroups;
			$criteria->limit = null;
			$users 				= $criteria->find();

			//Get ids of those users
			foreach ($users as $user) {
				$user_ids[] = $user->id;
			}

			unset($users);
		}

		//Merge with Users
		if ($exportData->sendto_users && count($exportData->users) > 0) {
			$user_ids = array_merge($user_ids, $exportData->users);
		}

		//Remove duplicates
		$user_ids = array_unique($user_ids);

		//Get UserData
		$criteria 			= craft()->elements->getCriteria(ElementType::User);
		$criteria->id 		= $user_ids;
		$criteria->limit = null;
		$users 				= $criteria->find();

		//CSV-Data
		$csv_data 	= array();
		$csv_data[]	= array(Craft::t('First name'), Craft::t('Last name'), Craft::t('Username'), Craft::t('Email'));

		//Add to CSV-Data
		foreach ($users as $user) {
			$csv_data[]	= array($user->firstName, $user->lastName, $user->username, $user->email);
		}

		//Generate CSV
		$csv = '';
		foreach ($csv_data as $row) {
			$csv .= implode(';', $row);
			$csv .= "\n";
		}

		//Return CSV
		return $csv;
	}


}
