<?php
namespace Magenest\GiftRegistry\Model;

class Cron
{
    protected $dataHelper;

    public function __construct(
        \Magenest\GiftRegistry\Helper\Data $dataHelper
    )
    {
        $this->dataHelper = $dataHelper;
    }

    public function updateExpiredGift()
    {
        $this->dataHelper->updateExpiredGift();
    }
}
