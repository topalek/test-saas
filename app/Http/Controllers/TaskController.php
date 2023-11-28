<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::query()->paginate(5);
        return view('task.index', compact('tasks'));
    }

    public function edit(Task $task)
    {
        return view('task.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $task->update(['task' => $request->input('task')]);
        return to_route('tasks.index');
    }

    public function store(Request $request)
    {
        Task::create(['task' => $request->input('task'), 'owner_id' => Auth::id()]);
        \auth()->user()->consume('add-tasks-limited', 1);
        return to_route('tasks.index');
    }

    public function create()
    {
        $subscriptionPlan = auth()->user()->subscription->plan->name ?? null;

        if ($subscriptionPlan === null) {
            return redirect()->route('tasks.index')->with('status', 'You have no active plan.');
        }

        $feature = match ($subscriptionPlan) {
            'bronze', 'silver', 'trial' => 'add-tasks-limited',
            'gold', 'unlim' => 'add-tasks-unlimited',
        };

        if (auth()->user()->cantConsume($feature, 1)) {
            $message = match ($subscriptionPlan) {
                'bronze' => 'You can create only 10 tasks on Silver plan',
                'silver' => 'You can create only 10 tasks on Silver plan',
                'gold' => 'You can create only 10 tasks on Silver plan',
                'trial' => "You can create only 3 tasks on Free Trial, please [<a href='/plan/' class='hover:underline'>choose your plan</a>]",
            };

            return redirect()->route('tasks.index')->with('status', $message);
        }
        return view('task.create');
    }

    public function show(Task $task)
    {
        return view('task.show', compact('task'));
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return to_route('tasks.index');
    }
}
