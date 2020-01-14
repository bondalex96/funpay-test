<?php declare(strict_types=1);


namespace Tests;


use App\ConfirmationMessageParser;
use PHPUnit\Framework\TestCase;

final class ConfirmationMessageParserTest extends TestCase
{
    public function testParseMessageAboutWalletIsInvalid(): void
    {
        $this->expectExceptionObject(
            new \Exception(
                'Не удалось произвести разбор данных:'
                .'код подтверждения,сумма,кошелек. Содержимое сообщения:'
                .' "Кошелек Яндекс.Денег указан неверно".'
            )
        );
        ConfirmationMessageParser::parse("Кошелек Яндекс.Денег указан неверно");
    }

    public function testParseMessageAboutAmountIsInvalid(): void
    {
        $this->expectExceptionObject(
            new \Exception(
                'Не удалось произвести разбор данных:'
                .'код подтверждения,сумма,кошелек. Содержимое сообщения:'
                .' "Сумма указана неверно".'
            )
        );
        ConfirmationMessageParser::parse("Сумма указана неверно");
    }

    public function testParseMessageAboutInsufficientFunds(): void
    {
        $this->expectExceptionObject(
            new \Exception(
                'Не удалось произвести разбор данных:'
                .'код подтверждения,сумма,кошелек. Содержимое сообщения:'
                .' "Недостаточно средств".'
            )
        );
        ConfirmationMessageParser::parse("Недостаточно средств");
    }

    public function testParseEmptyMessage(): void
    {
        $this->expectExceptionObject(
            new \Exception(
                'Не удалось произвести разбор данных:'
                .'код подтверждения,сумма,кошелек. Содержимое сообщения:'
            )
        );
        ConfirmationMessageParser::parse("");
    }

    /**
     * @dataProvider validConfirmationMessagesProvider
     * @param string $message
     */
    public function testParseValidMessage(string $message): void
    {
        $data = ConfirmationMessageParser::parse(
            $message
        );

        $this->assertEquals("4444", $data->getConfirmationCode());
        $this->assertEquals("410012312312312", $data->getPaymentAccount());
        $this->assertEquals(1.01, $data->getPaymentAmount()->getAmount());
        $this->assertEquals('р', $data->getPaymentAmount()->getCurrency());
    }

    /**
     * @dataProvider changedInvalidInformationAboutConfirmationCodeProvider
     * @param string $message
     */
    public function testParseMessageWithChangedInvalidInfoAboutConfirmationCode(string $message): void
    {
        $this->expectExceptionObject(
            new \Exception(
                'Не удалось произвести разбор данных:'
                .'код подтверждения. Содержимое сообщения: "'. $message.'".'
            )
        );
        ConfirmationMessageParser::parse($message);
    }


    /**
     * @dataProvider changedValidInformationAboutConfirmationCodeProvider
     * @param string $message
     */
    public function testParseMessageWithChangedValidInfoAboutConfirmationCode(string $message): void
    {
        $data = ConfirmationMessageParser::parse(
            $message
        );

        $this->assertEquals("4444", $data->getConfirmationCode());
    }

    /**
     * @dataProvider changedValidInfoAboutPaymentAmountInformationProvider
     * @param string $message
     * @param float $expectedAmount
     * @param string $expectedCurrency
     */
    public function testParseMessageWithChangedValidInfoAboutPaymentAmount(
        string $message,
        float $expectedAmount,
        string $expectedCurrency
    ): void {

        $data = ConfirmationMessageParser::parse(
            $message
        );

        $this->assertEquals(
            $expectedAmount,
            $data->getPaymentAmount()->getAmount()
        );
        $this->assertEquals(
            $expectedCurrency,
            $data->getPaymentAmount()->getCurrency()
        );
    }

    /**
     * @dataProvider changedInvalidInfoAboutPaymentAmountInformationProvider
     * @param string $message
     */
    public function testParseMessageWithChangedInvalidInfoAboutPaymentAmount(
        string $message
    ): void {
        $this->expectExceptionObject(
            new \Exception(
                'Не удалось произвести разбор данных:'
                .'сумма. Содержимое сообщения: "'. $message.'".'
            )
        );
        ConfirmationMessageParser::parse($message);
    }

    /**
     * @dataProvider changedValidInformationAboutPaymentAccountProvider
     * @param string $message
     */
    public function testParseMessageWithChangedValidInfoAboutPaymentAccount(
        string $message
    ): void {
        $data = ConfirmationMessageParser::parse(
            $message
        );

        $this->assertEquals("410012312312312", $data->getPaymentAccount());
    }

    /**
     * @dataProvider changedInvalidInformationAboutPaymentAccountProvider
     * @param string $message
     */
    public function testParseMessageWithChangedInvalidInfoAboutPaymentAccount(
        string $message
    ): void {
        $this->expectExceptionObject(
            new \Exception(
                'Не удалось произвести разбор данных:'
                .'кошелек. Содержимое сообщения: "'. $message.'".'
            )
        );
        ConfirmationMessageParser::parse($message);
    }

    public function validConfirmationMessagesProvider(): array
    {
        return [
            ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет 410012312312312"],
            ["Спишется 1,01р.\nПеревод на счет 410012312312312\nПароль: 4444"],
            ["Спишется 1,01р.\nПароль: 4444\nПеревод на счет 410012312312312"],
            ["Пароль: 4444\nПеревод на счет 410012312312312\nСпишется 1,01р."],
            ["Перевод на счет 410012312312312\nПароль: 4444\nСпишется 1,01р."],
            ["Перевод на счет 410012312312312\nСпишется 1,01р.\nПароль: 4444"],
        ];
    }

    public function changedValidInformationAboutConfirmationCodeProvider(): array
    {
        return [
            "С измененным названием поля" => ["Password: 4444\nСпишется 1,01р.\nПеревод на счет 410012312312312"],
            "Без двоеточия" => ["Пароль 4444\nСпишется 1,01р.\nПеревод на счет 410012312312312"],
            "Без пробела между двоеточием" => ["Пароль:4444\nСпишется 1,01р.\nПеревод на счет 410012312312312"],
        ];
    }

    public function changedInvalidInformationAboutConfirmationCodeProvider(): array
    {
        return [
            "Без значения поля 'Пароль'" => ["Пароль: \nСпишется 1,01р.\nПеревод на счет 410012312312312"],
            "Без  поля 'Пароль'" => ["Спишется 1,01р.\nПеревод на счет 410012312312312"],
        ];
    }

    public function changedValidInfoAboutPaymentAmountInformationProvider(): array
    {
        return [
            "Без точки после валюты" => [
                "Пароль: 4444\nСпишется 1,01р\nПеревод на счет 410012312312312",
                1.01,
                'р',
            ],
            "В Евро" => [
                "Пароль: 4444\nСпишется 1,01EUR.\nПеревод на счет 410012312312312",
                1.01,
                'EUR',
            ],
            "В Долларах" => [
                "Пароль: 4444\nСпишется 1,01USD.\nПеревод на счет 410012312312312",
                1.01,
                'USD',
            ],
            "С измененным названием поля" => [
                "Пароль: 4444\nPayment 1,01р.\nПеревод на счет 410012312312312",
                1.01,
                'р',
            ],
            "С лишним двоеточием" => [
                "Пароль: 4444\nСпишется: 1,01р.\nПеревод на счет 410012312312312",
                1.01,
                'р',
            ],
            "С лишним двоеточием и без пробела" => [
                "Пароль: 4444\nСпишется:1,01р.\nПеревод на счет 410012312312312",
                1.01,
                'р',
            ],
            "Без плавающей точки" => [
                "Пароль: 4444\nСпишется 2р.\nПеревод на счет 410012312312312",
                2,
                'р',
            ],
            "Сумма с большим разрядом" => [
                "Пароль: 4444\nСпишется 999,99р.\nПеревод на счет 410012312312312",
                999.99,
                'р',
            ],
            "С точкой в качестве плавающей точки" => [
                "Пароль: 4444\nСпишется 1.01р.\nПеревод на счет 410012312312312",
                1.01,
                'р'
            ],
            "С пробелом между числом и валютой" => [
                "Пароль: 4444\nСпишется 1.01р.\nПеревод на счет 410012312312312",
                1.01,
                'р'
            ],
            "C полным названием валюты" => [
                "Пароль: 4444\nСпишется 1.01рублей\nПеревод на счет 410012312312312",
                1.01,
                'рублей'
            ],
        ];
    }

    public function changedInvalidInfoAboutPaymentAmountInformationProvider(): array
    {
        return [
            "Без поля 'Спишется'" => ["Пароль: 4444\nПеревод на счет 410012312312312"],
            "Без значения поля 'Спишется'" => ["Пароль: 4444\nСпишется\nПеревод на счет 410012312312312"],
        ];
    }
    public function changedValidInformationAboutPaymentAccountProvider(): array
    {
        return [
            "С лишним двоеточием" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет: 410012312312312"],
            "С лишним двоеточием и без пробела" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет:410012312312312"],
            "С измененным названием поля" => ["Пароль: 4444\nСпишется 1,01р.\nКошелек 410012312312312"],
        ];
    }

    public function changedInValidInformationAboutPaymentAccountProvider(): array
    {
        return [
            "Без поля 'Перевод на счет'" => ["Пароль: 4444\nСпишется 1,01р."],
            "Без информации о номере кошелька" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет"],
            "С коротким номером кошелька" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет: 41001123"],
            "С длинным номером кошелька" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет: 41001123123123123"],
            "С первыми пятью цифрами, отличными от 41001" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет 400002312312312"],
        ];
    }
}