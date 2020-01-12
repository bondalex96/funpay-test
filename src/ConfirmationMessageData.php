<?php

declare(strict_types=1);


namespace App;

/**
 * Содержит данные сообщения о подтверждении платежа.
 */
final class ConfirmationMessageData
{
    /**
     * Код подтверждения.
     */
    private string $confirmationCode;

    /**
     * Сумма платежа.
     */
    private PaymentAmount $paymentAmount;

    /**
     * Номер кошелька.
     */
    private string $paymentAccount;

    /**
     * @param string $confirmationCode
     * @param PaymentAmount $paymentAmount
     * @param string $paymentAccount
     */
    public function __construct(
        string $confirmationCode,
        PaymentAmount $paymentAmount,
        string $paymentAccount
    ) {
        $this->confirmationCode = $confirmationCode;
        $this->paymentAmount = $paymentAmount;
        $this->paymentAccount = $paymentAccount;
    }

    /**
     * @return string
     */
    public function getConfirmationCode(): string
    {
        return $this->confirmationCode;
    }

    /**
     * @return PaymentAmount
     */
    public function getPaymentAmount(): PaymentAmount
    {
        return $this->paymentAmount;
    }

    /**
     * @return string
     */
    public function getPaymentAccount(): string
    {
        return $this->paymentAccount;
    }
}