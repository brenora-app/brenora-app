<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('industry')->nullable()->change();
            $table->string('owner')->nullable()->change();
            $table->decimal('annual_revenue', 12, 2)->nullable()->change();
            $table->unsignedTinyInteger('health_score')->nullable()->change();
            $table->date('renewal_date')->nullable()->change();
            $table->string('subscription_plan', 120)->nullable();
            $table->unsignedSmallInteger('contract_length_months')->nullable();
            $table->string('engagement_status', 32)->default('active')->index();
            $table->string('segment', 80)->default('unclassified')->index();
            $table->decimal('expansion_value', 12, 2)->nullable();
            $table->decimal('forecast_revenue', 12, 2)->nullable();
            $table->string('opportunity_stage', 32)->nullable()->index();
            $table->string('consent_status', 24)->default('pending')->index();
            $table->timestamp('consent_updated_at')->nullable();
            $table->index(['status', 'renewal_date']);
            $table->string('salesforce_id', 30)->nullable()->unique();
            $table->timestamp('salesforce_synced_at')->nullable();
        });

        Schema::table('insights', function (Blueprint $table) {
            $table->text('reviewer_correction')->nullable();
        });

        Schema::table('commercial_actions', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->string('playbook', 120)->nullable();
            $table->text('outcome')->nullable();
            $table->timestamp('completed_at')->nullable();
        });

        Schema::create('account_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('salesforce_id', 30)->nullable()->unique();
            $table->string('name', 160);
            $table->string('title', 120)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('phone', 40)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('consent_status', 24)->default('pending');
            $table->timestamp('consent_updated_at')->nullable();
            $table->timestamps();
            $table->index(['account_id', 'is_primary']);
        });

        Schema::create('account_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 48);
            $table->string('summary', 255);
            $table->json('details')->nullable();
            $table->timestamps();
            $table->index(['account_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });

        Schema::create('salesforce_connections', function (Blueprint $table) {
            $table->id();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->string('instance_url', 255);
            $table->timestamp('last_synced_at')->nullable();
            $table->foreignId('connected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $hasUnassessedAccounts = DB::table('accounts')
            ->where('status', 'needs_review')
            ->orWhereNull('annual_revenue')
            ->orWhereNull('health_score')
            ->orWhereNull('renewal_date')
            ->exists();
        if ($hasUnassessedAccounts) {
            throw new \RuntimeException('Resolve unassessed account values before rolling back customer revenue workflows.');
        }

        Schema::dropIfExists('account_activity');
        Schema::dropIfExists('account_contacts');
        Schema::dropIfExists('salesforce_connections');

        Schema::table('commercial_actions', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['assigned_to', 'playbook', 'outcome', 'completed_at']);
        });

        Schema::table('insights', function (Blueprint $table) {
            $table->dropColumn('reviewer_correction');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['status', 'renewal_date']);
            $table->dropIndex(['engagement_status']);
            $table->dropIndex(['segment']);
            $table->dropIndex(['opportunity_stage']);
            $table->dropIndex(['consent_status']);
            $table->dropColumn([
                'subscription_plan',
                'contract_length_months',
                'engagement_status',
                'segment',
                'expansion_value',
                'forecast_revenue',
                'opportunity_stage',
                'consent_status',
                'consent_updated_at',
                'salesforce_id',
                'salesforce_synced_at',
            ]);
            $table->decimal('annual_revenue', 12, 2)->nullable(false)->change();
            $table->unsignedTinyInteger('health_score')->nullable(false)->change();
            $table->date('renewal_date')->nullable(false)->change();
        });

    }
};
