<?php

class UiPersistence extends \Atk4\Ui\Persistence\Ui
{
    public function __construct()
    {
        parent::__construct();

        $this->dateFormat = 'Y-m-d';
        $this->timeFormat = 'H:i:s';
        $this->datetimeFormat = 'Y-m-d H:i:s';
        $this->timezone = 'UTC';
    }

    protected function _typecastSaveField(\Atk4\Data\Field $field, $value): string
    {
        switch ($field->type) {
            case 'datetime':
				$dt = clone $value;
				$dt->setTimezone(new DateTimeZone($this->timezone));
                return $dt->format($this->datetimeFormat);
        }

        return parent::_typecastSaveField($field, $value);
    }
/*
    public function _typecastLoadField(\Atk4\Data\Field $field, $value): ?string
    {
        switch ($field->type) {
            case 'card':
                return str_replace(' ', '', $value);
        }

        return parent::_typecastLoadField($field, $value);
    }
*/
}

?>
