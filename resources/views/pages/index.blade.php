<x-layouts.app>
    <div class="w-full max-w-lg mx-auto p-4 space-y-4">
        
        <h1 class="text-2xl font-semibold text-center">{{ __('LARAVEL + DATASTAR Simple ToDo') }}</h1>

        <div class="p-4 bg-white rounded shadow space-y-4">
            <div>
                <input data-bind="title" type="text" class="text-sm w-full p-1.5 border rounded" placeholder="{{ __('Task Title') }}">
                <div class="text-red-500 text-sm mt-1" data-show="$errors?.title" data-text="$errors?.title"></div>
            </div>
            <div>
                <input data-bind="due_date" type="date" class="text-sm w-full p-1.5 border rounded" placeholder="{{ __('Due Date') }}">
                <div class="text-red-500 text-sm mt-1" data-show="$errors?.due_date" data-text="$errors?.due_date"></div>
            </div>
            
            <button
                class="cursor-pointer hover:text-blue-500 hover:border-1 hover:border-blue-500 hover:bg-inherit text-sm w-full bg-blue-500 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:border-0 disabled:cursor-not-allowed disabled:text-white"
                data-on-click="{{ dspost(route('task.store')) }}"
                data-attr-disabled="!($title && $due_date)"
            >
                {{ __('Add Task') }}
            </button>
        </div>

        @fragment('task-list')
        <div id="task-list" class="p-4 bg-white rounded shadow space-y-4">
                @forelse ($tasks as $task)
                    <x-tasks.item :task="$task" />
                @empty
                    <p class="text-center font-semibold">No Task Added Yet</p>
                @endforelse
        </div>
        @endfragment

    </div>
</x-layouts.app>
