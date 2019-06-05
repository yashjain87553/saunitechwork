<?php

namespace Gold\Example\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Example implements ArrayInterface
{
	/**
	 * Options getter
	 * @return array
	 */
	public function toOptionArray()
	{
		$arr = $this->toArray();
		$ret = [];

		foreach ($arr as $key => $value) {
			$ret[] = [
				'value' => $key,
				'label' => $value
			];
		}

		return $ret;
	}

	/**
	 * Get options in "key-value" format
	 * @return array
	 */
	public function toArray()
	{
		return [
			'option_1' => __('Option #1'),
			'option_2' => __('Option #2'),
			'option_3' => __('Option #3')
		];
	}
}