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
 * Interface SingleSendInterface
 *
 * @package Lof\SendGrid\Api\Data
 */
interface SingleSendInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

<<<<<<< HEAD
    const SINGLESEND = 'singlesend';
    const SINGLESEND_ID = 'singlesend_id';
    const UPDATE_DATE = 'update_date';
    const CREATE_DATE = 'create_date';
=======
    const ID = 'id';
    const SINGLESEND_ID = 'singlesend_id';
    const CREATE_DATE = 'create_date';
    const UPDATE_DATE = 'update_date';
>>>>>>> create module settings, menu, model, database
    const NAME = 'name';

    /**
     * Get singlesend_id
     * @return string|null
     */
    public function getSinglesendId();

    /**
     * Set singlesend_id
     * @param string $singlesendId
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSinglesendId($singlesendId);

    /**
<<<<<<< HEAD
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setName($name);
=======
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setId($id);
>>>>>>> create module settings, menu, model, database

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\SendGrid\Api\Data\SingleSendExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Lof\SendGrid\Api\Data\SingleSendExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\SendGrid\Api\Data\SingleSendExtensionInterface $extensionAttributes
    );

    /**
<<<<<<< HEAD
=======
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setName($name);

    /**
>>>>>>> create module settings, menu, model, database
     * Get create_date
     * @return string|null
     */
    public function getCreateDate();

    /**
     * Set create_date
     * @param string $createDate
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setCreateDate($createDate);

    /**
     * Get update_date
     * @return string|null
     */
    public function getUpdateDate();

    /**
     * Set update_date
     * @param string $updateDate
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setUpdateDate($updateDate);
<<<<<<< HEAD

    /**
     * Get singlesend
     * @return string|null
     */
    public function getSinglesend();

    /**
     * Set singlesend
     * @param string $singlesend
     * @return \Lof\SendGrid\Api\Data\SingleSendInterface
     */
    public function setSinglesend($singlesend);
}
=======
}

>>>>>>> create module settings, menu, model, database
