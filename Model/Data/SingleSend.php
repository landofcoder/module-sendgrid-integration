<?php
/**
 * Copyright (c) 2019  Landofcoder
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

namespace Lof\SendGrid\Model\Data;

use Lof\SendGrid\Api\Data\SingleSendInterface;

/**
 * Class SingleSend
 *
 * @package Lof\SendGrid\Model\Data
 */
class SingleSend extends \Magento\Framework\Api\AbstractExtensibleObject implements SingleSendInterface
{

    /**
     * Get singlesend_id
     * @return string|null
     */
    public function getSinglesendId()
    {
        return $this->_get(self::SINGLESEND_ID);
    }

    /**
     * Set singlesend_id
     * @param string $singlesendId
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSinglesendId($singlesendId)
    {
        return $this->setData(self::SINGLESEND_ID, $singlesendId);
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\SendGrid\Api\Data\SingleSendExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Lof\SendGrid\Api\Data\SingleSendExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\SendGrid\Api\Data\SingleSendExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get create_date
     * @return string|null
     */
    public function getCreateDate()
    {
        return $this->_get(self::CREATE_DATE);
    }

    /**
     * Set create_date
     * @param string $createDate
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setCreateDate($createDate)
    {
        return $this->setData(self::CREATE_DATE, $createDate);
    }

    /**
     * Get update_date
     * @return string|null
     */
    public function getUpdateDate()
    {
        return $this->_get(self::UPDATE_DATE);
    }

    /**
     * Set update_date
     * @param string $updateDate
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setUpdateDate($updateDate)
    {
        return $this->setData(self::UPDATE_DATE, $updateDate);
    }

    /**
     * Get singlesend
     * @return string|null
     */
    public function getSinglesend()
    {
        return $this->_get(self::SINGLESEND);
    }

    /**
     * Set singlesend
     * @param string $singlesend
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSinglesend($singlesend)
    {
        return $this->setData(self::SINGLESEND, $singlesend);
    }
}
