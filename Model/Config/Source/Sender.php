<?php
/**
 * Copyright (c) 2020  Landofcoder
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lof\SendGrid\Model\Config\Source;

use Lof\SendGrid\Helper\Data;
use Lof\SendGrid\Model\ResourceModel\Sender\CollectionFactory;
use Magento\Framework\App\Action\Context;

/**
 * Class Sender
 *
 * @package Lof\SendGrid\Model\Config\Source
 */
class Sender implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var CollectionFactory
     */
    private $_collection;

    public function __construct(
        Context $context,
        CollectionFactory $collection
    ) {
        $this->context = $context;
        $this->_collection = $collection;
    }
    public function toOptionArray()
    {
        $options = [];
        $list = $this->_collection->create()->addFieldToFilter('verified', '1');
        foreach ($list as $item) {
            $options[] = [
                'label' => __($item->getNickName()),
                'value' => $item->getSenderId(),
            ];
        }
        return $options;
    }
}
