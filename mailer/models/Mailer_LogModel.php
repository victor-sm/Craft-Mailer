<?php
namespace Craft;

class Mailer_LogModel extends BaseModel
{

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
            'id'       		=> AttributeType::Number,

            'subject'       => AttributeType::String,
            'htmlBody'      => AttributeType::String,
            'status'        => array(AttributeType::Enum, 'values' => "finished,running,failed"),
            'description'   => AttributeType::String,

            'dateCreated'   => AttributeType::DateTime,
            'dateFinished'  => AttributeType::DateTime,

            'success'       => AttributeType::Number,
            'errors'        => AttributeType::Mixed,
        );
	}

}