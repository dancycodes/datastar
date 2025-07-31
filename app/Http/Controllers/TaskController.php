<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\DatastarHelpers;
use Illuminate\Http\Request;
use Closure;
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
            new Middleware(function (Request $request, Closure $next) {
                if (auth()->user()->tasks()->count() === 1) {
                    throw new \Exception(__('Only 1 task... TEST WORKS'));
                    // abort(403, __('You cannot create a task when you already have one. Please delete the existing task first.'));
                    // return redirect()->route('verification.notice');
                    // dump('Only 1 task... TEST WORKS');
                    // dd('Show with the previous');
                }
                return $next($request);
            }),
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
        $signals = $this->readSignals();

        $task_data = $this->validate(
            $signals,
            $this->rules()
        );

        $task = auth()->user()->tasks()->create($task_data);

        if (auth()->user()->tasks()->count() === 1) {
            $this->patchElements(view('pages.todos', ['tasks' => auth()->user()->tasks])->fragment('task-list'));
        } else {
            $this->patchElements(
                view('components.tasks.item', compact('task')),
                [
                    'selector' => '#task-list',
                    'mode' => 'append'
                ]
            );
        }

        return $this->patchSignals([
            'title' => '',
        ])
            ->toastify(
                'success',
                __('Task created successfully!')
            )
            ->getEventStream();
    }

    public function destroy(Task $task): StreamedResponse
    {
        $id = $task->id;

        $task->delete();

        $this->removeElements("#task-{$id}");

        if (auth()->user()->tasks()->count() === 0) {
            $this->patchElements(view('pages.todos', ['tasks' => auth()->user()->tasks()->latest()->get()])->fragment('task-list'));
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

        return $this->patchElements(view('components.tasks.item', compact('task'))->fragment('task-description'))
            ->toastify(
                'success',
                $task->is_completed ? __('Congratulations on completing the task!') : __('Task updated successfully!')
            )
            ->getEventStream();
    }

    public function getForm(Task $task): StreamedResponse
    {
        return $this->patchSignals([
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
        return $this->patchElements(view('components.tasks.item', compact('task')))
            ->getEventStream();
    }

    public function update(Task $task): StreamedResponse
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

        return $this->patchElements(view('components.tasks.item', compact('task')))
            ->toastify(
                'success',
                __('Task updated successfully!')
            )
            ->getEventStream();
    }
}
