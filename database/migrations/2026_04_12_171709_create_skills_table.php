<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // 'Vue.js', 'Laravel', etc.
            $table->string('category', 50)->nullable(); // 'frontend', 'backend', etc.
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
        });

        // Pivot: resume <-> skills
        Schema::create('developer_resume_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained('developer_resumes')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->unique(['resume_id', 'skill_id']);
        });

        // Популярні скіли для автодоповнення
        $skills = [
            ['name' => 'JavaScript', 'category' => 'frontend'],
            ['name' => 'TypeScript', 'category' => 'frontend'],
            ['name' => 'Vue.js', 'category' => 'frontend'],
            ['name' => 'React', 'category' => 'frontend'],
            ['name' => 'Angular', 'category' => 'frontend'],
            ['name' => 'HTML/CSS', 'category' => 'frontend'],
            ['name' => 'PHP', 'category' => 'backend'],
            ['name' => 'Laravel', 'category' => 'backend'],
            ['name' => 'Python', 'category' => 'backend'],
            ['name' => 'Node.js', 'category' => 'backend'],
            ['name' => 'Java', 'category' => 'backend'],
            ['name' => 'Spring Boot', 'category' => 'backend'],
            ['name' => 'MySQL', 'category' => 'database'],
            ['name' => 'PostgreSQL', 'category' => 'database'],
            ['name' => 'MongoDB', 'category' => 'database'],
            ['name' => 'Redis', 'category' => 'database'],
            ['name' => 'Docker', 'category' => 'devops'],
            ['name' => 'Kubernetes', 'category' => 'devops'],
            ['name' => 'Git', 'category' => 'tools'],
            ['name' => 'REST API', 'category' => 'tools'],
            ['name' => 'GraphQL', 'category' => 'tools'],
            ['name' => 'AWS', 'category' => 'cloud'],
            ['name' => 'Linux', 'category' => 'devops'],
            ['name' => 'SQL', 'category' => 'database'],
            ['name' => 'Hibernate', 'category' => 'backend'],
            ['name' => 'JUnit', 'category' => 'testing'],
            ['name' => 'Spring', 'category' => 'backend'],
            ['name' => 'JDBC', 'category' => 'backend'],
            ['name' => 'JPA', 'category' => 'backend'],
            ['name' => 'Mockito', 'category' => 'testing'],
            ['name' => 'Jira', 'category' => 'tools'],
            ['name' => 'Design Patterns', 'category' => 'architecture'],
            ['name' => 'microservices', 'category' => 'architecture'],
        ];

        foreach ($skills as $skill) {
            \Illuminate\Support\Facades\DB::table('skills')->insert(array_merge($skill, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_resume_skills');
        Schema::dropIfExists('skills');
    }
};
