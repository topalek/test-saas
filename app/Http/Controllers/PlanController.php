<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LucasDotVin\Soulbscription\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::query()
                     ->whereNot('name', 'trial')
                     ->with('features')
                     ->get()
        ;

        return view('plans.index', ['plans' => $plans, 'currentSubscription' => auth()->user()->subscription]);
    }

    public function edit(Plan $plan)
    {
        return view('task.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update(['plan' => $request->input('plan')]);
        return to_route('tasks.index');
    }

    public function store(Request $request)
    {
        Plan::create(['plan' => $request->input('plan'), 'owner_id' => Auth::id()]);
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
            'Gold Monthly', 'Gold Yearly'              => 'manage-tasks-unlimited',
        };

        if (auth()->user()->cantConsume($feature, 1)) {
            $message = match ($subscriptionPlan) {
                'Silver Monthly', 'Silver Yearly' => 'You can create only 10 tasks on Silver plan',
                'Trial'                           => "You can create only 3 tasks on Free Trial, please [<a href='/plan/' class='hover:underline'>choose your plan</a>]",
            };

            return redirect()->route('tasks.index')->with('status', $message);
        }
    }

    public function show(Plan $plan)
    {
        return view('task.show', compact('plan'));
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return to_route('tasks.index');
    }
}
