<?php

namespace Tests\Unit;

use App\Services\Utilities\DatabaseErrorMessageResolver;
use Illuminate\Database\QueryException;
use PDOException;
use Tests\TestCase;

class DatabaseErrorMessageResolverTest extends TestCase
{
    public function test_it_returns_specific_message_for_links_truncation_errors(): void
    {
        $exception = $this->makeQueryException(
            sqlState: '22001',
            driverCode: 1406,
            driverMessage: "Data too long for column 'links' at row 1"
        );

        $message = DatabaseErrorMessageResolver::userMessageFor($exception);

        $this->assertNotNull($message);
        $this->assertStringContainsString('links', strtolower($message));
    }

    public function test_it_returns_generic_message_for_other_truncation_errors(): void
    {
        $exception = $this->makeQueryException(
            sqlState: '22001',
            driverCode: 1406,
            driverMessage: "Data too long for column 'activity' at row 1"
        );

        $message = DatabaseErrorMessageResolver::userMessageFor($exception);

        $this->assertNotNull($message);
        $this->assertStringContainsString('too long', strtolower($message));
    }

    public function test_it_returns_null_for_non_truncation_errors(): void
    {
        $exception = $this->makeQueryException(
            sqlState: '23000',
            driverCode: 1452,
            driverMessage: 'Cannot add or update a child row: a foreign key constraint fails'
        );

        $this->assertNull(DatabaseErrorMessageResolver::userMessageFor($exception));
    }

    private function makeQueryException(string $sqlState, int $driverCode, string $driverMessage): QueryException
    {
        $pdoException = new PDOException($driverMessage, $driverCode);
        $pdoException->errorInfo = [$sqlState, $driverCode, $driverMessage];

        return new QueryException(
            connectionName: 'mysql',
            sql: 'insert into `class_record_details` (`links`) values (?)',
            bindings: ['value'],
            previous: $pdoException,
        );
    }
}
