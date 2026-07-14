# Database Setup Guide
## MySQL, Migrations, dan Seeding

**Target:** Setup database MySQL dengan schema sesuai ERD

---

## 1. Prerequisites

| Software | Version | Notes |
|----------|---------|-------|
| MySQL | 8.0+ | Database server |
| PHP | 8.3+ | For artisan commands |

---

## 2. Database Creation

### 2.1 Create Database

```sql
CREATE DATABASE cbt_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2.2 Update `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cbt_platform
DB_USERNAME=root
DB_PASSWORD=
```

---

## 3. Migration Structure

Ikuti hierarchy dari [DATABASE-DESIGN.md](../DATABASE-DESIGN.md):

```
evaluation_periods
        │
        ▼
questionnaires
        │
        ▼
components
        │
        ▼
sub_components
        │
        ▼
indicators
        │
        ▼
questions

users
response_sessions
response_answers
evaluation_results
evaluation_result_details
recommendations
settings
```

---

## 4. Migrations

### 4.1 Users Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'respondent']);
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable(); // Nullable for respondents
            $table->boolean('isActive')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### 4.2 Evaluation Periods Table

```php
Schema::create('evaluation_periods', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->dateTime('startDate');
    $table->dateTime('endDate');
    $table->boolean('isActive')->default(false);
    $table->timestamps();
});
```

### 4.3 Questionnaires Table

```php
Schema::create('questionnaires', function (Blueprint $table) {
    $table->id();
    $table->foreignId('evaluationPeriodId')->constrained('evaluation_periods');
    $table->string('title');
    $table->text('description')->nullable();
    $table->integer('durationMinutes')->default(60);
    $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
    $table->timestamps();
});
```

### 4.4 Components Table

```php
Schema::create('components', function (Blueprint $table) {
    $table->id();
    $table->foreignId('questionnaireId')->constrained('questionnaires');
    $table->string('name');
    $table->text('description')->nullable();
    $table->integer('orderNumber')->default(0);
    $table->timestamps();
});
```

### 4.5 Sub Components Table

```php
Schema::create('sub_components', function (Blueprint $table) {
    $table->id();
    $table->foreignId('componentId')->constrained('components');
    $table->string('name');
    $table->text('description')->nullable();
    $table->integer('orderNumber')->default(0);
    $table->timestamps();
});
```

### 4.6 Indicators Table

```php
Schema::create('indicators', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subComponentId')->constrained('sub_components');
    $table->string('name');
    $table->text('description')->nullable();
    $table->integer('orderNumber')->default(0);
    $table->timestamps();
});
```

### 4.7 Questions Table

```php
Schema::create('questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('indicatorId')->constrained('indicators');
    $table->text('questionText');
    $table->decimal('weight', 5, 2)->default(1.00);
    $table->integer('orderNumber')->default(0);
    $table->timestamps();
});
```

### 4.8 Response Sessions Table

```php
Schema::create('response_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('userId')->constrained('users');
    $table->foreignId('questionnaireId')->constrained('questionnaires');
    $table->enum('status', ['inProgress', 'submitted', 'timeout'])->default('inProgress');
    $table->dateTime('startedAt');
    $table->dateTime('submittedAt')->nullable();
    $table->integer('remainingSeconds');
    $table->timestamps();
});
```

### 4.9 Response Answers Table

```php
Schema::create('response_answers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('responseSessionId')->constrained('response_sessions');
    $table->foreignId('questionId')->constrained('questions');
    $table->unsignedTinyInteger('score'); // Likert scale 1-7
    $table->timestamps();

    $table->unique(['responseSessionId', 'questionId']);
});
```

### 4.10 Evaluation Results Table

```php
Schema::create('evaluation_results', function (Blueprint $table) {
    $table->id();
    $table->foreignId('responseSessionId')->constrained('response_sessions');
    $table->decimal('overallScore', 10, 2);
    $table->decimal('overallPercentage', 5, 2);
    $table->string('overallCategory', 1); // A-E
    $table->text('conclusion')->nullable();
    $table->timestamps();
});
```

### 4.11 Evaluation Result Details Table

```php
Schema::create('evaluation_result_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('evaluationResultId')->constrained('evaluation_results');
    $table->foreignId('indicatorId')->constrained('indicators');
    $table->decimal('score', 10, 2);
    $table->decimal('percentage', 5, 2);
    $table->string('category', 1); // A-E
    $table->text('recommendation')->nullable();
    $table->timestamps();
});
```

### 4.12 Recommendations Table

```php
Schema::create('recommendations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('indicatorId')->constrained('indicators');
    $table->decimal('minScore', 5, 2);
    $table->decimal('maxScore', 5, 2);
    $table->string('category', 1); // A-E
    $table->text('recommendationText');
    $table->timestamps();
});
```

### 4.13 Settings Table

```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->text('description')->nullable();
    $table->timestamps();
});
```

---

## 5. Run Migrations

```bash
# Run all migrations
php artisan migrate

# Rollback if needed
php artisan migrate:rollback
```

---

## 6. Seeders

### 6.1 DatabaseSeeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
```

### 6.2 AdminSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'role' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@cbt.com',
            'password' => Hash::make('password'),
            'isActive' => true,
        ]);
    }
}
```

### 6.3 SettingSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'activePeriodId', 'value' => null, 'description' => 'Periode evaluasi yang aktif'],
            ['key' => 'evaluationDuration', 'value' => '60', 'description' => 'Durasi evaluasi dalam menit'],
            ['key' => 'autoSaveInterval', 'value' => '30', 'description' => 'Interval auto save dalam detik'],
            ['key' => 'allowResume', 'value' => 'true', 'description' => 'Izinkan resume sesi terputus'],
            ['key' => 'timeoutMinutes', 'value' => '5', 'description' => 'Timeout sebelum sesi dianggap selesai'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
```

---

## 7. Run Seeders

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=AdminSeeder
```

---

## 8. Factory (untuk Testing)

### 8.1 UserFactory

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'role' => 'respondent',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => null, // Respondents don't have passwords
            'isActive' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'password' => 'password',
        ]);
    }
}
```

---

## 9. Verification

```bash
# Check migration status
php artisan migrate:status

# Check tables in database
mysql -u root -p cbt_platform -e "SHOW TABLES;"
```

**Expected tables:**
1. users
2. evaluation_periods
3. questionnaires
4. components
5. sub_components
6. indicators
7. questions
8. response_sessions
9. response_answers
10. evaluation_results
11. evaluation_result_details
12. recommendations
13. settings

---

**Lanjut ke:** [05-MODULE-AUTH](05-MODULE-AUTH.md)
