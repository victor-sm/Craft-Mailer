<?php
namespace Craft;

class Mailer_RecipientsModel extends BaseModel
{

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'recipients' => AttributeType::Mixed
			/*
			array(
			    array('to' => 'test1@example.com', 'bcc' => 'test2@example.com', 'cc' => 'test3@example.com'), //Recipients of the 1. mail
			    array('to' => 'test4@example.com, test5@example.com'), //Recipients of the 2. mail
			    array('to' => 'test6@example.com') //and so on...
			);
			*/
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
		if ( $clearErrors ) {
			$this->clearErrors();
		}
		
		
		//Mail RegEx
		$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';


		//Any Recipient specified?
		if(empty($this->recipients)) {
			$this->addError('recipients', Craft::t('No recipients specified'));
		}
		else {

			//Check each array
			foreach($this->recipients as $array) {
				//To (required)
				/*$to_emails = preg_split('[,]', $array['to']);

				foreach($to_emails as $mail) {
					if (preg_match( $regex, trim($mail) ) == 0) {
						$this->addError('to', trim($mail) . Craft::t(' is not a valid email'));
					}
				}*/

					if (preg_match( $regex, $array['to'] ) == 0) {
						$this->addError('to', $array['to'] . Craft::t(' is not a valid email'));
					}
					//Currently only one 'to' address is allowed.


				//CC (optional)
				if (!empty($array['cc'])) {
					$cc_emails = preg_split('[,]', $array['cc']);

					foreach($cc_emails as $mail) {
						if (preg_match( $regex, trim($mail) ) == 0) {
							$this->addError('cc', trim($mail) . Craft::t(' is not a valid email'));
						}
					}
				}

				//BCC (optional)
				if (!empty($array['bcc'])) {
					$bcc_emails = preg_split('[,]', $array['bcc']);

					foreach($bcc_emails as $mail) {
						if (preg_match( $regex, trim($mail) ) == 0) {
							$this->addError('bcc', trim($mail) . Craft::t(' is not a valid email'));
						}
					}
				}
			}
			
		}

		return parent::validate($attributes, false);
	}
}