<?php

    use App\Models\Task;
    use function Laravel\Folio\{render, name, middleware};
    use Illuminate\View\View;

    name('home');

    middleware('auth');

    render(function (View $view) {
        return $view->with('tasks', auth()->user()->tasks()->latest()->get());
    }); 

?>

<x-layouts.app>
    <div class="w-full max-w-lg mx-auto p-4 space-y-4">

        <h1 class="text-2xl font-semibold text-center">{{ __('LARAVEL + DATASTAR Simple ToDo') }}</h1>

        <x-ui.card>
            <x-ui.input
                name="title"
                :placeholder="__('Task Title')"
                :label="__('Task Title')"
                :field_validates_controller="'TaskController'"
            />
            <x-ui.input
                name="due_date"
                type="date"
                :placeholder="__('Due Date')"
                :label="__('Due Date')"
                :field_validates_controller="'TaskController'"
            />

            <button
                class="btn"
                data-on-click="{{ datastar()->post(['TaskController', 'store']) }}"
                data-attr-disabled="!($title && $due_date)"
            >
                {{ __('Add Task') }}
            </button>
        </x-ui.card>

        @fragment('task-list')
        <x-ui.card id="task-list">
                @forelse ($tasks as $task)
                    <x-tasks.item :task="$task" />
                @empty
                    <p class="text-center font-semibold">No Task Added Yet</p>
                @endforelse
        </x-ui.card>
        @endfragment

    </div>
</x-layouts.app>
