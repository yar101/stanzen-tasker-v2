<?php

namespace Database\Factories;

use App\Models\Contractor;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $usersCount = User::all()->count();
        $currencyVariants = ['RUB', 'USD', 'EUR', 'CNY'];
        $priorityVariants = ['I', 'II', 'III'];
        return [
            'title' => $this->faker->realText(30),
            'description' => $this->faker->realText(50),
            'status' => $this->faker->numberBetween(1, 5),
            'created_by' => $this->faker->numberBetween(1, $usersCount),
            'cost' => $this->faker->numberBetween(500, 100000),
            'deadline_start' => now(),
            'deadline_end' => $this->faker->dateTimeBetween(now(), Carbon::now()->addDays(30)),
            'currency' => $this->faker->randomElement($currencyVariants),
            'priority' => $this->faker->randomElement($priorityVariants),
            'created_at' => now(),
            'parent_task' => null,
            'updated_at' => null,
            'contractor' => Contractor::all()->random()->id,
        ];
    }
}
