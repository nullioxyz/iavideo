<?php

namespace App\Domain\Credits\UseCases\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\UseCases\ListCreditStatementUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListCreditStatementUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_user_ledger_entries_paginated(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $user->creditLedger()->create([
            'delta' => -1,
            'balance_before' => 3,
            'balance_after' => 2,
            'reason' => 'input creation',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => 1,
        ]);

        $other->creditLedger()->create([
            'delta' => -2,
            'balance_before' => 3,
            'balance_after' => 1,
            'reason' => 'other user',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => 2,
        ]);

        $useCase = new ListCreditStatementUseCase;

        $result = $useCase->execute((int) $user->getKey(), 15, 1);

        $this->assertSame(1, $result->total());
        $this->assertSame('input creation', $result->items()[0]->reason);
    }
}
