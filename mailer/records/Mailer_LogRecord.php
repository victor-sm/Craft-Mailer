<?php
namespace Craft;

class Mailer_LogRecord extends BaseRecord
{
    
    /**
     * @access protected
     * @return string
     */
    public function getTableName()
    {
        return 'mailer_log';
    }


    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
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