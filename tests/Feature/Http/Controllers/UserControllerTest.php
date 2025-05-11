<?php

beforeEach(function () {
    $this->seed();
});

it('can get users', function () {
    $this->get(route('users.index'))
        ->assertSuccessful()
        ->assertJsonCount(10, 'data');
});

it('can show a user by id', function () {
    $this->get(route('users.show', ['user' => 1]))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ],
        ]);
});

it('can search users', function () {
    $this->get(route('users.index', ['search' => 'Test User']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('can create a user', function () {
    $this->post(route('users.store'), [
        'name' => 'John Doe',
        'email' => 'T3hYq@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'cpf' => '000.000.000-00',
        'phone' => '(00) 00000-0000',
    ])->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'T3hYq@example.com',
        'cpf' => '000.000.000-00',
        'phone' => '00 00000-0000',
    ]);
});

it('can update a user by id', function () {
    $this->put(route('users.update', ['user' => 1]), [
        'name' => 'John Doe',
        'email' => 'T3hYq@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'cpf' => '000.000.000-00',
        'phone' => '(00) 00000-0000',
    ])->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'T3hYq@example.com',
        'cpf' => '000.000.000-00',
        'phone' => '00 00000-0000',
    ]);
});

it('can delete a user by id', function () {
    $this->delete(route('users.destroy', ['user' => 1]))
        ->assertSuccessful();

    $this->assertDatabaseMissing('users', [
        'id' => 1,
    ]);
});
