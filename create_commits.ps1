# PowerShell script to create 45 migrations and commit them with past dates

$startDate = Get-Date "2025-04-25"
$intervalDays = 6  # approximately 6.5, but integer

$migrations = @(
    "leave_requests",
    "projects",
    "tasks",
    "timesheets",
    "notifications",
    "announcements",
    "holidays",
    "payrolls",
    "salaries",
    "benefits",
    "trainings",
    "certifications",
    "performance_reviews",
    "goals",
    "feedback",
    "meetings",
    "agendas",
    "minutes",
    "assets",
    "maintenance",
    "inventory",
    "suppliers",
    "purchase_orders",
    "invoices",
    "expenses",
    "budgets",
    "reports",
    "dashboards",
    "widgets",
    "logs",
    "audit_trails",
    "backups",
    "archives",
    "templates",
    "workflows",
    "approvals",
    "rejections",
    "comments",
    "attachments",
    "tags",
    "categories",
    "priorities",
    "statuses",
    "types",
    "roles_permissions"
)

$currentDate = $startDate

$baseTime = Get-Date "2025-04-25T10:00:00"
$timeIncrement = 1  # hours

foreach ($migration in $migrations) {
    # Create migration file
    $timestamp = $baseTime.ToString("yyyy_MM_dd_HHmmss")
    $filename = "${timestamp}_create_${migration}_table.php"
    $filepath = "database/migrations/$filename"
    
    $content = @"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('$migration', function (Blueprint `$table) {
            `$table->id();
            `$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('$migration');
    }
};
"@
    $content = $content -replace '\$table', '$table'

    # Write file
    $content | Out-File -FilePath $filepath -Encoding UTF8

    # Add the new file
    git add $filepath

    # Commit with date
    $dateStr = $currentDate.ToString("yyyy-MM-ddTHH:mm:ss")
    $env:GIT_AUTHOR_DATE = $dateStr
    $env:GIT_COMMITTER_DATE = $dateStr
    git commit -m "Add migration for ${migration} table"

    # Increment date
    $currentDate = $currentDate.AddDays($intervalDays)
    $baseTime = $baseTime.AddHours($timeIncrement)
}