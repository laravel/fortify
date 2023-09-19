<?php

namespace Database\Factories;

use App\Models\User;

/**
 * @template TModel of \App\Models\User
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class UserFactory extends \Orchestra\Testbench\Factories\UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = User::class;
}
