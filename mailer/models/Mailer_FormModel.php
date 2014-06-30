<?php
namespace Craft;

class Mailer_FormModel extends BaseModel
{

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'subject'		=> array(AttributeType::String, 'required' => true, 'label' => Craft::t('Subject')),
			'htmlBody'  	=> array(AttributeType::String, 'required' => true, 'label' => Craft::t('Message')),
			'sender_name'	=> array(AttributeType::String, 'required' => true, 'label' => Craft::t('Your Name')),
			'sender_mail'	=> array(AttributeType::Email,  'required' => true, 'label' => Craft::t('Your Email')),
			'attachment' 	=> AttributeType::Mixed,

			'sendto_custom' => AttributeType::Bool,
				'to' => AttributeType::Mixed,
				'cc' => AttributeType::Mixed,
				'bcc' => AttributeType::Mixed,

			'sendto_usergroups' => AttributeType::Bool,
				'usergroups' => AttributeType::Mixed,

			'sendto_users' => AttributeType::Bool,
				'users' => AttributeType::Mixed
		);
	}
	
	
	
	/**
	 * @param null $attributes
	 * @param bool $clearErrors
	 * @return bool|void
	 */
	public function validate($attributes = null, $clearErrors = true)
	{
		//ClearErrors?
		if ($clearErrors) {
			$this->clearErrors();
		}
		
		
		//Any recipients specified?
		if ($this->sendto_custom == false && $this->sendto_usergroups == false && $this->sendto_users == false) {
			$this->addError('sendto', Craft::t('No recipients specified'));
		}

		
		//Custom recipients
		if ($this->sendto_custom) {
			$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
			
			//Recipients
			if (!empty($this->to)) {
				/*
				$recipient_emails = preg_split('[,]', $this->to);
				$i = 1;
				
				foreach($recipient_emails as $mail) {
					if (preg_match( $regex, trim($mail) ) == 0) {
						$this->addError('to', '(' .$i. ') ' . trim($mail) . Craft::t(' is not a valid email'));
					}
					$i++;
				}*/

				if (preg_match( $regex, $this->to ) == 0) {
					$this->addError('to', $this->to . Craft::t(' is not a valid email'));
				}
				//Currently only one 'to' address is allowed.

			}
			else {
				$this->addError('to', Craft::t('No recipients specified'));
			}
			
			//CC
			if (!empty( $this->cc )) {
				$cc_emails = preg_split('[,]', $this->cc);
				$i = 1;
				
				foreach($cc_emails as $mail) {
					if (preg_match( $regex, trim($mail) ) == 0) {
						$this->addError('cc', '(' .$i. ') ' . trim($mail) . Craft::t(' is not a valid email'));
					}
					$i++;
				}
			}
			
			//BCC
			if (!empty( $this->bcc )) {
				$bcc_emails = preg_split('[,]', $this->bcc);
				$i = 1;
				
				foreach($bcc_emails as $mail) {
					if (preg_match( $regex, trim($mail) ) == 0) {
						$this->addError('bcc', '(' .$i. ') ' . trim($mail) . Craft::t(' is not a valid email'));
					}
					$i++;
				}
			}
		}
		
		
		//UserGroup recipients
		if ($this->sendto_usergroups) {
			if (!$this->usergroups) {
				$this->addError('usergroups', Craft::t('No usergroups specified'));
			}
			elseif (array_filter($this->usergroups, 'is_int') === $this->usergroups) {
				$this->addError('usergroups', Craft::t('Invalid usergroups specified'));
			}

		}
		
		
		//User recipients
		if ($this->sendto_users) {
			if (!$this->users) {
				$this->addError('users', Craft::t('No users specified'));
			}
			elseif (array_filter($this->users, 'is_int') === $this->users) {
				$this->addError('users', Craft::t('Invalid users specified'));
			}
		}

		
		return parent::validate($attributes, false);
	}
}