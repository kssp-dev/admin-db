<?php

class EnumFilterModel extends \Atk4\Ui\Table\Column\FilterModel\TypeEnum
{
    protected function init(): void
    {
        parent::init();
        
        $this->name = 'filter_model_' .
			preg_replace('/\W/', '_',
				strval($this->lookupField->getOwner()->table)
			) .
			'_' . $this->lookupField->shortName;
        
        if ($this->getApp()->tryGetRequestQueryParam('atk_clear_filter') ?? false) {
            $this->forget();
        }
    }

}
