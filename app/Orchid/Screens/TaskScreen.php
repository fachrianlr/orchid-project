<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\TD;

use App\Models\Task;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;


class TaskScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tasks' => Task::latest()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Task';
    }

    public function description(): ?string
    {
        return 'Data Table Task List';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Create')
                ->modal('createTaskModal')
                ->method('create')
                // ->icon('plus'),
                ->class('btn btn-primary'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            // create modal
            Layout::modal('createTaskModal', Layout::rows([
                Input::make('task.name')
                    ->title('Name')
                    ->placeholder('Enter task name')
                    ->help('The name of the task to be created.'),
            ]))
                ->title('Create')
                ->applyButton('Create'),

            // Edit modal
            Layout::modal('editTaskModal', Layout::rows([
                Input::make('task.id')->type('hidden'),
                Input::make('task.name')
                    ->title('Name')
                    ->placeholder('Enter task name')
                    ->help('The name of the task to be edited.')
                    ->value('task.name'),
            ]))
                ->title('Edit Task')
                ->applyButton('Save Changes')
                ->async('asyncTask'),

            Layout::table('tasks', [
                TD::make('name'),
                TD::make('active'),
                TD::make('created_at'),
                TD::make('updated_at'),
                TD::make('actions')
                    ->render(function (Task $task) {
                        return '<div style="display: flex; gap: 5px;">' .
                            ModalToggle::make('Edit')
                            ->modal('editTaskModal')
                            ->method('edit')
                            ->modalTitle('Edit Task')
                            ->asyncParameters([
                                'task' => $task->id,
                            ])
                            ->class('btn btn-warning') .
                            Button::make('Delete')
                            ->confirm('After deleting, the task will be gone forever.')
                            ->method('delete', ['task' => $task->id])
                            ->class('btn btn-danger')
                            . '</div>';
                    }),
            ]),
        ];
    }

    public function create(Request $request)
    {
        // Validate form data, save task to database, etc.
        $request->validate([
            'task.name' => 'required|max:255',
        ]);

        $task = new Task();
        $task->name = $request->input('task.name');
        $task->save();
    }

    public function delete(Task $task)
    {
        $task->delete();
    }

    public function edit(Task $task, Request $request)
    {
        $request->validate([
            'task.name' => 'required|max:255',
        ]);

        $task->name = $request->input('task.name');
        $task->save();
    }

    public function asyncTask(Task $task)
    {
        return [
            'task' => $task
        ];
    }
}
