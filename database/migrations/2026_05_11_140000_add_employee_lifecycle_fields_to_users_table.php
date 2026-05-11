<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('probation_ends_at')->nullable()->after('employment_status');
            $table->date('contract_ends_at')->nullable()->after('probation_ends_at');
            $table->timestamp('resignation_submitted_at')->nullable()->after('contract_ends_at');
            $table->timestamp('resigned_at')->nullable()->after('resignation_submitted_at');
            $table->text('resignation_reason')->nullable()->after('resigned_at');
            $table->timestamp('exit_interview_completed_at')->nullable()->after('resignation_reason');
            $table->timestamp('account_auto_disable_at')->nullable()->after('exit_interview_completed_at');

            $table->index('probation_ends_at');
            $table->index('contract_ends_at');
            $table->index('account_auto_disable_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['probation_ends_at']);
            $table->dropIndex(['contract_ends_at']);
            $table->dropIndex(['account_auto_disable_at']);
            $table->dropColumn([
                'probation_ends_at',
                'contract_ends_at',
                'resignation_submitted_at',
                'resigned_at',
                'resignation_reason',
                'exit_interview_completed_at',
                'account_auto_disable_at',
            ]);
        });
    }
};
