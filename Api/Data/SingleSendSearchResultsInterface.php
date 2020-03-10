<?php
/**
<<<<<<< HEAD
 * Copyright (c) 2019  Landofcoder
 *
=======
 * Copyright (c) 2020  Landofcoder
 * 
>>>>>>> create module settings, menu, model, database
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
<<<<<<< HEAD
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
=======
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
>>>>>>> create module settings, menu, model, database
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lof\SendGrid\Api\Data;

/**
 * Interface SingleSendSearchResultsInterface
 *
 * @package Lof\SendGrid\Api\Data
 */
interface SingleSendSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get SingleSend list.
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface[]
     */
    public function getItems();

    /**
<<<<<<< HEAD
     * Set name list.
=======
     * Set id list.
>>>>>>> create module settings, menu, model, database
     * @param \Lof\SendGrid\Api\Data\SingleSendInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
<<<<<<< HEAD
=======

>>>>>>> create module settings, menu, model, database
