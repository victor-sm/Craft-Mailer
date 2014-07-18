<?php
namespace Craft;

class Mailer_ExportModel extends BaseModel
{

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
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
		if ($this->sendto_usergroups == false && $this->sendto_users == false) {
			$this->addError('sendto', Craft::t('No recipients specified'));
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

		//Return
		return parent::validate($attributes, false);
	}

}