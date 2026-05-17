<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table): void {
            $table->dropUnique('payrolls_user_id_month_year_unique');

            $table->string('period_type', 16)->default('monthly')->after('type');
            $table->date('period_start')->nullable()->after('year');
            $table->date('period_end')->nullable()->after('period_start');
            $table->unsignedSmallInteger('period_sequence')->default(1)->after('period_end');
            $table->decimal('taxable_income', 15, 2)->default(0)->after('net_salary');
            $table->decimal('non_taxable_income', 15, 2)->default(0)->after('taxable_income');
            $table->decimal('pph21_tax', 15, 2)->default(0)->after('non_taxable_income');
            $table->decimal('bpjs_employee_total', 15, 2)->default(0)->after('pph21_tax');
            $table->decimal('bpjs_employer_total', 15, 2)->default(0)->after('bpjs_employee_total');
            $table->decimal('employer_contribution_total', 15, 2)->default(0)->after('bpjs_employer_total');
            $table->json('coretax_payload')->nullable()->after('details');

            $table->unique(['user_id', 'period_type', 'period_start', 'period_end', 'period_sequence'], 'payrolls_user_period_unique');
            $table->index(['period_type', 'year', 'month'], 'payrolls_period_type_year_month_index');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table): void {
            $table->dropUnique('payrolls_user_period_unique');
            $table->dropIndex('payrolls_period_type_year_month_index');

            $table->dropColumn([
                'period_type',
                'period_start',
                'period_end',
                'period_sequence',
                'taxable_income',
                'non_taxable_income',
                'pph21_tax',
                'bpjs_employee_total',
                'bpjs_employer_total',
                'employer_contribution_total',
                'coretax_payload',
            ]);

            $table->unique(['user_id', 'month', 'year'], 'payrolls_user_id_month_year_unique');
        });
    }
};
