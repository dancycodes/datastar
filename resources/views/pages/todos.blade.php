<?php

    use App\Models\Task;
    use function Laravel\Folio\{render, name, middleware};
    use Illuminate\View\View;

    name('todos.index');

    // middleware(['auth', 'verified']);
    middleware(['auth']);

    render(function (View $view) {
        return $view->with('tasks', auth()->user()->tasks()->latest()->get());
    }); 

?>

<x-layouts.app>
    <div class="w-full max-w-lg mx-auto space-y-4">
        <div class="w-full text-right">
            <button 
                class="btn-danger !w-fit !mx-auto"
                data-on-click="{{ datastar()->post(['AuthController','logout']) }}"
            >
                {{ __('Logout') }}
            </button>
        </div>
        

        <div class="text-center space-y-2">
            <h1 class="text-2xl font-semibold">{{ __('LARAVEL + DATASTAR Simple ToDo') }}</h1>
            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-medium text-green-800">
                        {{ __('Welcome back, :name! Your email is verified.', ['name' => auth()->user()->name]) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="w-full flex flex-col items-center gap-y-2">
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
                            <div class="text-center space-y-3">
                                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-center font-semibold text-gray-600">{{ __('No Tasks Added Yet') }}</p>
                                    <p class="text-center text-sm text-gray-500 mt-1">{{ __('Create your first task above to get started!') }}</p>
                                </div>
                            </div>
                        @endforelse
                </x-ui.card>
            @endfragment
        </div>

        

    </div>
</x-layouts.app>