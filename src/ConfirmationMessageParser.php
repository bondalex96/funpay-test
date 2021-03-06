<?php

declare(strict_types=1);


namespace App;

use Exception;


final class ConfirmationMessageParser
{
    /**
     * Производит разбор сообщения о подтверждении платежа.
     *
     * @param string $message
     * @return ConfirmationMessageData
     * @throws Exception
     */
    public static function parse(string $message): ConfirmationMessageData
    {
        $confirmationCode = self::extractConfirmationCode($message);
        $paymentAccount = self::extractPaymentAccount($message);
        $paymentAmount = self::extractPaymentAmount($message);

        self::ensurePropertiesNotNull(
            $message,
            $confirmationCode,
            $paymentAccount,
            $paymentAmount
        );

        return  new ConfirmationMessageData(
          $confirmationCode,
          $paymentAmount,
          $paymentAccount
        );
    }

    /**
     * Извлекает из сообщения данные о кошельке.
     *
     * @param string $message
     * @return string|null
     */
    private static function extractPaymentAccount(string $message): ?string
    {
        // Проанализировал поведение эмулятора выяснил, что формат
        // кошелька имеет вид: число "41001" + число из 8-11 цифр.
        $paymentAccountPattern = "/\b(41001\d{8,11})\b/";
        preg_match(
            $paymentAccountPattern,
            $message,
            $matches
        );

        return  $matches[0] ?? null;
    }

    /**
     * Извлекает из сообщения данные о сумме платежа.
     *
     * @param string $message
     * @return PaymentAmount|null
     */
    private static function extractPaymentAmount(string $message): ?PaymentAmount
    {
        $paymentAmountPattern = "/([\d]+[\.,][\d]+|\d+) ?([А-Яа-яA-Za-z]+)/u";
        preg_match(
            $paymentAmountPattern,
            $message,
            $matches
        );

        $amount = (string) $matches[1] ?? null;
        $currency = (string) $matches[2] ?? null;
        if (!$amount || !$currency) {
            return  null;
        }

        $amount = floatval(str_replace(",",".", $amount));

        return new PaymentAmount($amount, $currency);
    }

    /**
     * Извлекает из сообщения данные о коде подтверждения.
     *
     * @param string $message
     * @return string|null
     */
    private static function extractConfirmationCode(string $message): ?string
    {
        //Мне не нравится вариант ориентации на 4 цифры пароля.
        // Однако, этот вариант лучше ориентирован на изменения
        // пунктуации и текста, но самый ненадежный. Тут принято
        // компромиссное решение. Надеюсь, оно оптимально в данной ситуации.
        $confirmationCodePattern = '/\b(\d{4})\b/';
        preg_match($confirmationCodePattern, $message, $matches);

        return  $matches[0] ?? null;
    }

    /**
     * Позволяет убедиться, что данные разобраны. Если нет, выбросит исключение.
     *
     * @param string $message
     * @param string|null $confirmationCode
     * @param string|null $paymentAccount
     * @param PaymentAmount|null $paymentAmount
     * @throws Exception
     */
    public static function ensurePropertiesNotNull(
        string $message,
        ?string $confirmationCode,
        ?string $paymentAccount,
        ?PaymentAmount $paymentAmount
    ): void {

        $propertiesParsed = self::checkPropertiesAreNotNull(
            $confirmationCode,
            $paymentAccount,
            $paymentAmount
        );
        if (!$propertiesParsed) {
            $problematicProperties = [];
            if (self::checkIsNull($confirmationCode)) {
                array_push($problematicProperties, 'код подтверждения');
            }
            if (self::checkIsNull($paymentAmount)) {
                array_push($problematicProperties, 'сумма');
            }
            if (self::checkIsNull($paymentAccount)) {
                array_push($problematicProperties, 'кошелек');
            }

            $exceptionMessage = self::formFailParsingMessage(
                $problematicProperties,
                $message
            );
            throw new Exception($exceptionMessage);
        }
    }

    /**
     * Проверяет полученные аргументы на наличие пустых значений.
     *
     * @param mixed ...$properties
     * @return bool
     */
    private static function checkPropertiesAreNotNull(...$properties): bool {
        foreach ($properties as $property) {
            if (self::checkIsNull($property)) {
                return  false;
            }
        }

        return  true;
    }

    /**
     * Формирует сообщение об ошибке разбора данных.
     *
     * @param array $properties
     * @param $originContent
     * @return string
     */
    private static function formFailParsingMessage(
        array $properties,
        $originContent
    ): string {
        return 'Не удалось произвести разбор данных:' . implode(",", $properties)
            .'. Содержимое сообщения: "'.$originContent.'".';
    }

    /**
     * Проверяет, что значение аргумента нулевое.
     *
     * @param $value
     * @return bool
     */
    private static function checkIsNull($value): bool
    {
        return  $value === null;
    }
}