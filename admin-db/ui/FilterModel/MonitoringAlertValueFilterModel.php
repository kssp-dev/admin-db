<?php

require_once 'NumberFilterModel.php';

class MonitoringAlertValueFilterModel extends NumberFilterModel
{
    protected function init(): void
    {
        parent::init();

        $this->op->default = '!=';
        $this->value->default = '0';
    }

}
