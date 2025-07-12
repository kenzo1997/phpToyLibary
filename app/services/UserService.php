<?php
namespace app\services;


use app\models\User;

class UserService {
    /** @var User[] */
    private array $users = [];

    public function __construct() {
        $this->users = [
            new User(1, 'John Doe'),
            new User(2, 'Jane Smith'),
            new User(3, 'Alice Johnson'),
        ];
    }

    public function getUsers() {
        return $this->users;
    }

    public function getUserById(int $id): ?User {
        foreach ($this->users as $user) {
            if ($user->id === $id) {
                return $user;
            }
        }

        return null;
    }

    public function getProjects() {
        $projects = [
            ['name' => 'Website Redesign', 'status' => 'In Progress', 'due_date' => '2025-06-01', 'owner' => 'Alice'],
            ['name' => 'Mobile App Launch', 'status' => 'Completed', 'due_date' => '2025-04-20', 'owner' => 'Bobby'],
            ['name' => 'Marketing Campaign', 'status' => 'Pending', 'due_date' => '2025-07-15', 'owner' => 'Carol'],
            ['name' => 'Bug Fix Sprint', 'status' => 'In Progress', 'due_date' => '2025-05-22', 'owner' => 'David'],
        ];

        return $projects;
    }
}
?>

