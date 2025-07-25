<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\DatastarHelpers;

class TaskController extends Controller
{
    use DatastarHelpers;

    public function store(): void
    {
        $signals = $this->readSignals();

        $task_data = $this->validate(
            $signals,
            [
                'title' => 'required|string|min:3|max:255',
                'due_date' => 'required|date|after_or_equal:today',
            ]
        );

        // Create the task using the validated data
        $task = Task::create($task_data);

        if (Task::count() === 1) {
            $this->patchElements(view('pages.index', ['tasks' => Task::all()])->fragment('task-list'));
        } else {
            $this->patchElements(
                view('components.tasks.item', compact('task')),
                [
                    'selector' => '#task-list',
                    'mode' => 'append'
                ]
            );
        }

        $this->patchSignals([
            'title' => '',
        ]);

        $this->toastify(
            'success',
            __('Task created successfully!')
        );
    }

    public function destroy(Task $task): void
    {
        $id = $task->id;

        $task->delete();

        $this->removeElements("#task-{$id}");

        if (Task::count() === 0) {
            $this->patchElements(view('pages.index', ['tasks' => Task::all()])->fragment('task-list'));
        }

        $this->toastify(
            'success',
            __('Task deleted successfully!')
        );
    }

    public function toggleComplete(Task $task): void
    {
        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        $this->patchElements(view('components.tasks.item', compact('task'))->fragment('task-description'));

        $this->toastify(
            'success',
            $task->is_completed ? __('Congratulations on completing the task!') : __('Task updated successfully!')
        );
    }

    public function getForm(Task $task): void
    {
        $this->patchSignals([
            "title_{$task->id}" => $task->title,
            "due_date_{$task->id}" => $task->due_date->format('Y-m-d'),
            'errors' => [
                "title_{$task->id}" => '',
                "due_date_{$task->id}" => '',
            ],
        ]);

        $this->patchElements(view('components.tasks.form', compact('task')));
    }

    public function getItem(Task $task)
    {
        $this->patchElements(view('components.tasks.item', compact('task')));
    }

    public function update(Task $task)
    {
        $signals = $this->readSignals();

        $taskData = $this->validate(
            $signals,
            [
                "title_{$task->id}" => 'required|string|min:3|max:255',
                "due_date_{$task->id}" => 'required|date|after_or_equal:today',
            ]
        );

        $task->update([
            'title' => $taskData["title_{$task->id}"],
            'due_date' => $taskData["due_date_{$task->id}"],
        ]);

        $this->patchElements(view('components.tasks.item', compact('task')));

        $this->toastify(
            'success',
            __('Task updated successfully!')
        );
    }
}
