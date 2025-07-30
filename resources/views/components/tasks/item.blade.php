<div id="task-{{ $task->id }}" class="bg-gray-200 p-2 shadow rounded overflow-hidden">
    <div class="flex justify-between items-center ">
        <div class="flex gap-x-2 items-center">
            <input data-on-change="{{ datastar()->patch(['TaskController', 'toggleComplete'], ['task' => $task->id]) }}" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600" @if($task->is_completed) checked @endif />
            @fragment('task-description')
                <div id="task-description-{{ $task->id }}">
                    <h3 class="text-sm font-semibold {{ $task->is_completed ? 'line-through text-gray-500' : '' }}">{{ $task->title }}</h3>
                    @if(!$task->is_completed)
                        <p class="text-sm {{ $task->due_date->isPast() ? 'text-red-600 font-medium' : ($task->due_date->isToday() ? 'text-orange-600 font-medium' : ($task->due_date->isTomorrow() ? 'text-yellow-600 font-medium' : 'text-gray-600')) }}">
                            {{ $task->due_date->isPast() ? 'Overdue ' . $task->due_date->diffForHumans() : 'Due ' . $task->due_date->diffForHumans() }}
                        </p>
                    @endif
                </div>
            @endfragment

        </div>
        <div class="flex gap-x-2 items-center">
            <button
                title="edit"
                data-on-click="{{ datastar()->get('/tasks/get-form/' . $task->id) }}"
            >
                <svg class="w-5 h-5 text-blue-800 cursor-pointer hover:text-blue-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M14 4.182A4.136 4.136 0 0 1 16.9 3c1.087 0 2.13.425 2.899 1.182A4.01 4.01 0 0 1 21 7.037c0 1.068-.43 2.092-1.194 2.849L18.5 11.214l-5.8-5.71 1.287-1.31.012-.012Zm-2.717 2.763L6.186 12.13l2.175 2.141 5.063-5.218-2.141-2.108Zm-6.25 6.886-1.98 5.849a.992.992 0 0 0 .245 1.026 1.03 1.03 0 0 0 1.043.242L10.282 19l-5.25-5.168Zm6.954 4.01 5.096-5.186-2.218-2.183-5.063 5.218 2.185 2.15Z" clip-rule="evenodd"/>
                </svg>

            </button>
            <button
                title="delete"
                data-on-click="{{ datastar()->delete(['TaskController', 'destroy'], ['task' => $task->id]) }}"
            >
                <svg class="w-5 h-5 text-red-800 cursor-pointer hover:text-red-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/>
                </svg>
            </button>
        </div>
    </div>
</div>
