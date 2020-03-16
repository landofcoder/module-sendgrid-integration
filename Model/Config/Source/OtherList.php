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
use Magento\Framework\App\Action\Context;

/**
 * Class SubscribeList
 *
 * @package Lof\SendGrid\Model\Config\Source
 */
class OtherList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Data
     */
    private $data;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Context $context,
        Data $helper
    ) {
        $this->context = $context;
        $this->helper = $helper;
    }
    public function toOptionArray()
    {
        $options = [];
        $list = $this->helper->getUnsubscriberGroup();
        foreach ($list as $item) {
            $options[] = [
                'label' => $item->name,
                'value' => __($item->name),
            ];
        }
        return $options;
    }
}
