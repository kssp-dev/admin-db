<?php

class StringFilterModel extends \Atk4\Ui\Table\Column\FilterModel\TypeString
{
    protected function init(): void
    {
        parent::init();

        $this->op->default = 'contains';
        
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
