<?php

namespace hiapi\heppy\modules;

use hiapi\legacy\lib\deps\err;
use hiapi\legacy\lib\deps\check;

class BalanceModule extends AbstractModule
{
    /** {@inheritdoc} */
    public $uris = [
        'balance' => 'http://www.unitedtld.com/epp/finance-1.0',
    ];

    public $object = 'finance';

    public function balanceInfo(array $row = []) : array
    {
        try {
            $info = $this->tool->commonRequest("{$this->object}:info", [
            ], $this->getFilterCallback(), [
                'balance' => 'balance',
            ]);
        } catch (\Thowable $e) {
            return [];
        }

        return $this->buildPoll($info);
    }

    protected function buildPoll(array $data) : array
    {
        $minBalance = $this->tool->getMinBalance() ?? 0;
        if ($minBalance > $data['balance']) {
            return [];
        }

        return [
            'request_date' => date("Y-m-d H:i:s"),
            'type' => 'low_balance',
            'name' => 'balance',
            'action_client' => $this->tool->getRegistrar(),
        ];
    }
}
