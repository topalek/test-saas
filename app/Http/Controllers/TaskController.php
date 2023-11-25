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
        return to_route('tasks.index');
    }

    public function create()
    {
        return view('task.create');
        $subscriptionPlan = auth()->user()->subscription->plan->name ?? null;

        if ($subscriptionPlan === null) {
            return redirect()->route('tasks.index')->with('status', 'You have no active plan.');
        }

        $feature = match ($subscriptionPlan) {
            'Silver Monthly', 'Silver Yearly', 'Trial' => 'manage-tasks-limited',
            'Gold Monthly', 'Gold Yearly' => 'manage-tasks-unlimited',
        };

        if (auth()->user()->cantConsume($feature, 1)) {
            $message = match ($subscriptionPlan) {
                'Silver Monthly', 'Silver Yearly' => 'You can create only 10 tasks on Silver plan',
                'Trial' => "You can create only 3 tasks on Free Trial, please [<a href='/plan/' class='hover:underline'>choose your plan</a>]",
            };

            return redirect()->route('tasks.index')->with('status', $message);
        }
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
