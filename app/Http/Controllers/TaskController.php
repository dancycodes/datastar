<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\DatastarHelpers;
// use Illuminate\Routing\Controllers\HasMiddleware;
// use Illuminate\Routing\Controllers\Middleware;

class TaskController extends Controller // implements HasMiddleware
{
    use DatastarHelpers;

    // public static function middleware(): array
    // {
    //     return [
    //         'auth'
    //     ];
    // }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'due_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function store()
    {
        $signals = $this->readSignals();

        $task_data = $this->validate(
            $signals,
            $this->rules()
        );


        $task = auth()->user()->tasks()->create($task_data);

        if (auth()->user()->tasks()->count() === 1) {
            $this->addPatchElements(view('pages.todos', ['tasks' => auth()->user()->tasks])->fragment('task-list'));
        } else {
            $this->addPatchElements(
                view('components.tasks.item', compact('task')),
                [
                    'selector' => '#task-list',
                    'mode' => 'append'
                ]
            );
        }

        return $this->addPatchSignals([
            'title' => '',
        ])
            ->addToastify(
                'success',
                __('Task created successfully!')
            )
            ->sendEvents();
    }

    public function destroy(Task $task)
    {
        $id = $task->id;

        $task->delete();

        $this->addRemoveElements("#task-{$id}");

        if (auth()->user()->tasks()->count() === 0) {
            $this->addPatchElements(view('pages.todos', ['tasks' => auth()->user()->tasks()->latest()->get()])->fragment('task-list'));
        }

        return $this->addToastify(
            'success',
            __('Task deleted successfully!')
        )->sendEvents();
    }

    public function toggleComplete(Task $task)
    {
        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        return $this->addPatchElements(view('components.tasks.item', compact('task'))->fragment('task-description'))
            ->addToastify(
                'success',
                $task->is_completed ? __('Congratulations on completing the task!') : __('Task updated successfully!')
            )
            ->sendEvents();
    }

    public function getForm(Task $task)
    {
        return $this->addPatchSignals([
            "title_{$task->id}" => $task->title,
            "due_date_{$task->id}" => $task->due_date->format('Y-m-d'),
            'errors' => [
                "title_{$task->id}" => '',
                "due_date_{$task->id}" => '',
            ],
        ])
            ->addPatchElements(view('components.tasks.form', compact('task')))
            ->sendEvents();
    }

    public function getItem(Task $task)
    {
        return $this->addPatchElements(view('components.tasks.item', compact('task')))->sendEvents();
    }

    public function update(Task $task)
    {
        $signals = $this->readSignals();

        $taskData = $this->validate(
            $signals,
            $this->setRulesKey($task->id),
        );

        $task->update([
            'title' => $taskData["title_{$task->id}"],
            'due_date' => $taskData["due_date_{$task->id}"],
        ]);

        return $this->addPatchElements(view('components.tasks.item', compact('task')))
            ->addToastify(
                'success',
                __('Task updated successfully!')
            )
            ->sendEvents();
    }
}