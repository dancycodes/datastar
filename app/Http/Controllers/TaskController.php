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
            $this->addPatchElements(view('pages.index', ['tasks' => auth()->user()->tasks])->fragment('task-list'));
        } else {
            $this->addPatchElements(
                view('components.tasks.item', compact('task')),
                [
                    'selector' => '#task-list',
                    'mode' => 'append'
                ]
            );
        }

        $this->addPatchSignals([
            'title' => '',
        ]);

        $this->addToastify(
            'success',
            __('Task created successfully!')
        );

        return $this->sendEvents();
    }

    public function destroy(Task $task)
    {
        $id = $task->id;

        $task->delete();

        $this->addRemoveElements("#task-{$id}");

        if (Task::count() === 0) {
            $this->addPatchElements(view('pages.index', ['tasks' => Task::all()])->fragment('task-list'));
        }

        $this->addToastify(
            'success',
            __('Task deleted successfully!')
        );

        return $this->sendEvents();
    }

    public function toggleComplete(Task $task)
    {
        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        $this->addPatchElements(view('components.tasks.item', compact('task'))->fragment('task-description'));

        $this->addToastify(
            'success',
            $task->is_completed ? __('Congratulations on completing the task!') : __('Task updated successfully!')
        );

        return $this->sendEvents();
    }

    public function getForm(Task $task)
    {
        $this->addPatchSignals([
            "title_{$task->id}" => $task->title,
            "due_date_{$task->id}" => $task->due_date->format('Y-m-d'),
            'errors' => [
                "title_{$task->id}" => '',
                "due_date_{$task->id}" => '',
            ],
        ]);

        $this->addPatchElements(view('components.tasks.form', compact('task')));

        return $this->sendEvents();
    }

    public function getItem(Task $task)
    {
        $this->addPatchElements(view('components.tasks.item', compact('task')));

        return $this->sendEvents();
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

        $this->addPatchElements(view('components.tasks.item', compact('task')));

        $this->addToastify(
            'success',
            __('Task updated successfully!')
        );

        return $this->sendEvents();
    }
}