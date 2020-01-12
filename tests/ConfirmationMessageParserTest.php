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
     * @dataProvider changedConfirmationCodeInformationProvider
     * @param string $message
     */
    public function testParseMessageWithChangedConfirmationCodeInformation(string $message): void
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
     * @dataProvider changedPaymentAmountInformationProvider
     * @param string $message
     */
    public function testParseMessageWithChangedPaymentAmountInformation(string $message): void
    {
        $this->expectExceptionObject(
            new \Exception(
                'Не удалось произвести разбор данных:'
                .'сумма. Содержимое сообщения: "'. $message.'".'
            )
        );
        ConfirmationMessageParser::parse($message);
    }

    /**
     * @dataProvider changedPaymentAccountInformationProvider
     * @param string $message
     */
    public function testParseMessageWithChangedPaymentAccountInformation(string $message): void
    {
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

    public function changedConfirmationCodeInformationProvider(): array
    {
        //TODO: потенциально, некоторые варианты можно распарсить, чтобы функция
        // была более гибкой к изменениям. Вероятно, следует добавить отдельный кейс
        return [
            "С измененным названием поля" => ["Password: 4444\nСпишется 1,01р.\nПеревод на счет 410012312312312"],
            "Без значения поля 'Пароль'" => ["Пароль: \nСпишется 1,01р.\nПеревод на счет 410012312312312"],
            "Без  поля 'Пароль'" => ["Спишется 1,01р.\nПеревод на счет 410012312312312"],
            "С лишними кавычками" => ["Пароль 4444\nСпишется 1,01р.\nПеревод на счет 410012312312312"],
        ];
    }

    public function changedPaymentAmountInformationProvider(): array
    {
        //TODO: потенциально, некоторые варианты можно распарсить, чтобы функция
        // была более гибкой к изменениям. Вероятно, следует добавить отдельный кейс
        return [
            "Без точки после валюты" => ["Пароль: 4444\nСпишется 1,01р\nПеревод на счет 410012312312312"],
            "Без поля 'Спишется'" => ["Пароль: 4444\nПеревод на счет 410012312312312"],
            "Без значения поля 'Спишется'" => ["Пароль: 4444\nСпишется\nПеревод на счет 410012312312312"],
            "С измененным названием поля" => ["Пароль: 4444\nPayment 1,01р.\nПеревод на счет 410012312312312"],
            "С лишним двоеточием" => ["Пароль: 4444\nСпишется: 1,01р.\nПеревод на счет 410012312312312"],
        ];
    }

    public function changedPaymentAccountInformationProvider(): array
    {
        //TODO: потенциально, некоторые варианты можно распарсить, чтобы функция
        // была более гибкой к изменениям. Вероятно, следует добавить отдельный кейс
        return [
            "С лишним двоеточием" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет: 410012312312312"],
            "Без поля 'Перевод на счет'" => ["Пароль: 4444\nСпишется 1,01р."],
            "Без информации о номере кошелька" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет"],
            "С невалидным номером кошелька" => ["Пароль: 4444\nСпишется 1,01р.\nПеревод на счет: 0000"],
            "С измененным названием поля" => ["Пароль: 4444\nСпишется 1,01р.\nTransfer to the account 410012312312312"],
        ];
    }
}