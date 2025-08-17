<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\DatastarHelpers;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TaskController extends Controller implements HasMiddleware
{
    use DatastarHelpers;

    public static function middleware(): array
    {
        return [
            new Middleware('verified', only: ['toggleComplete', 'destroy']),
        ];
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'due_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function store(): StreamedResponse
    {
        dump('hello');
        dd('here');

        $task_data = sse()->validate($this->rules());

        $task = auth()->user()->tasks()->create($task_data);

        if (auth()->user()->tasks()->count() === 1) {
            sse()->patchElements(view('pages.todos', ['tasks' => auth()->user()->tasks])->fragment('task-list'));
        } else {
            sse()->patchElements(
                view('components.tasks.item', compact('task')),
                [
                    'selector' => '#task-list',
                    'mode' => 'append'
                ]
            );
        }

        // $this->toastify(
        //     'success',
        //     __('Task created successfully!')
        // );

        return sse()->patchSignals([
            'title' => '',
        ])
            ->getEventStream(function () {
                sse()
                    ->throwException(new \Exception('Task not found'));
            });
    }

    public function destroy(Task $task): StreamedResponse
    {
        $id = $task->id;

        $task->delete();

        sse()->removeElements("#task-{$id}");

        if (auth()->user()->tasks()->count() === 0) {
            sse()->patchElements(view('pages.todos', ['tasks' => auth()->user()->tasks()->latest()->get()])->fragment('task-list'));
        }

        return $this->toastify(
            'success',
            __('Task deleted successfully!')
        )
            ->getEventStream();
    }

    public function toggleComplete(Task $task): StreamedResponse
    {
        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        $this->toastify(
            'success',
            $task->is_completed ? __('Congratulations on completing the task!') : __('Task updated successfully!')
        );

        return sse()->patchElements(view('components.tasks.item', compact('task'))->fragment('task-description'))
            ->getEventStream();
    }

    public function getForm(Task $task): StreamedResponse
    {
        return sse()->patchSignals([
            "title_{$task->id}" => $task->title,
            "due_date_{$task->id}" => $task->due_date->format('Y-m-d'),
            'errors' => [
                "title_{$task->id}" => '',
                "due_date_{$task->id}" => '',
            ],
        ])
            ->patchElements(view('components.tasks.form', compact('task')))
            ->getEventStream();
    }

    public function getItem(Task $task): StreamedResponse
    {
        return sse()->patchElements(view('components.tasks.item', compact('task')))
            ->getEventStream();
    }

    public function update(Task $task): StreamedResponse
    {
        $taskData = sse()->validate(
            $this->setRulesKey($task->id),
        );

        $task->update([
            'title' => $taskData["title_{$task->id}"],
            'due_date' => $taskData["due_date_{$task->id}"],
        ]);

        $this->toastify(
            'success',
            __('Task updated successfully!')
        );

        return sse()->patchElements(view('components.tasks.item', compact('task')))
            ->getEventStream();
    }
}
