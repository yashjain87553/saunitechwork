<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   2.3.12
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rewards\Api\Data;

interface TransactionInterface
{
    const TABLE_NAME  = 'mst_rewards_transaction';

    const ID = 'transaction_id';

    const KEY_CUSTOMER_ID = 'customer_id';
    const KEY_AMOUNT = 'amount';
    const KEY_AMOUNT_USED = 'amount_used';
    const KEY_COMMENT = 'comment';
    const KEY_CODE = 'code';
    const KEY_IS_EXPIRED = 'is_expired';
    const KEY_IS_EXPIRATION_EMAIL_SENT = 'is_expiration_email_sent';
    const KEY_EXPIRES_AT = 'expires_at';
    const KEY_CREATED_AT = 'created_at';
    const KEY_ACTIVATED_AT = 'activated_at';
    const KEY_IS_ACTIVATED = 'is_activated';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getAmount();

    /**
     * @param int $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * @return int
     */
    public function getAmountUsed();

    /**
     * @param int $amountUsed
     * @return $this
     */
    public function setAmountUsed($amountUsed);

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return int
     */
    public function getIsExpired();

    /**
     * @param int $isExpired
     * @return $this
     */
    public function setIsExpired($isExpired);

    /**
     * @return int
     */
    public function getIsExpirationEmailSent();

    /**
     * @param int $isExpirationEmailSent
     * @return $this
     */
    public function setIsExpirationEmailSent($isExpirationEmailSent);

    /**
     * @return string
     */
    public function getExpiresAt();

    /**
     * @param int $expiresAt
     * @return $this
     */
    public function setExpiresAt($expiresAt);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getActivatedAt();

    /**
     * @param string $activatedAt
     * @return $this
     */
    public function setActivatedAt($activatedAt);

    /**
     * @return int
     */
    public function getIsActivated();

    /**
     * @param int $isActivated
     * @return $this
     */
    public function setIsActivated($isActivated);
}