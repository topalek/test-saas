<x-app-layout>
    <div class="container">
        <div class="row">
            <div class="card bg-blueGray-100 w-full">
                <div class="card-header">
                    <div class="card-row">
                        <h6 class="card-title">
                            My Plan
                        </h6>
                    </div>
                </div>

                <div class="card-body">
                    <div class="flex py-3 space-x-2">
                        <div>
                            My current plan: <strong class="capitalize ">{{ $currentSubscription->plan->name ?? 'No active plan' }}</strong>

                            @if($currentSubscription !== null)
                                active until {{ $currentSubscription?->expired_at->format('Y-m-d') }}
                            @endif
                        </div>

                        @if($currentSubscription !== null && $currentSubscription?->plan->name !== 'Trial')
                            <form method="POST" action="{{ route('plan.destroy', $currentSubscription->plan) }}">
                                @csrf
                                @method('DELETE')

                                [
                                <button class="hover:underline">Cancel plan</button>
                                ]
                            </form>
                        @endif
                    </div>

                    <div>
                        <!-- Plans -->
                        <div
                            class="flex flex-col items-center justify-center mt-16 space-y-8 lg:flex-row lg:items-stretch lg:space-x-8 lg:space-y-0"
                        >
                            @foreach($plans as $plan)
                                <section class="flex flex-col w-full max-w-sm p-12 space-y-6 bg-white rounded-lg shadow-md">
                                    <!-- Price -->
                                    <div class="flex-shrink-0">
                                        <span class="text-4xl font-medium tracking-tight">{{ $plan->name }}</span>
                                    </div>

                                    <div class="flex-shrink-0 pb-6 space-y-2 border-b">
                                        <h2 class="text-2xl font-normal">{{ $plan->name }}</h2>
                                    </div>

                                    <!-- Features -->
                                    <ul class="flex-1 space-y-4">
                                        @foreach($plan->features as $feature)
                                            <li class="flex items-start">
                                                <svg
                                                    class="w-6 h-6 text-green-300"
                                                    aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <span class="ml-3 text-base font-medium">{{$feature->title}} {{$feature->charges}}</span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <!-- Button -->
                                    <div class="flex-shrink-0 pt-4">
                                        <form method="POST" x-bind:action="billPlan == 'monthly' ? plan.route.monthly : plan.route.annually">
                                            @csrf
                                            @method('PUT')

                                            <button
                                                class="inline-flex items-center justify-center w-full max-w-xs px-4 py-2 transition-colors border rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                :class="plan.name == 'Gold' ? 'bg-indigo-500 text-white hover:bg-indigo-700' : 'hover:bg-indigo-500 hover:text-white'"
                                                x-text="`Get ${plan.name}`"
                                            ></button>
                                        </form>
                                    </div>
                                </section>
                            @endforeach

                        </div>
                    </div>


                    {{--<div>Silver | Gold</div>
                    <div class="flex space-x-0.5">
                        <form method="POST" action="{{ route('plan.update', 1) }}">
                            @csrf
                            @method('PUT')

                            [<button class="hover:underline">Go monthly for $9.99/month</button>]
                        </form>

                        <div>|</div>

                        <form method="POST" action="{{ route('plan.update', 3) }}">
                            @csrf
                            @method('PUT')

                            [<button class="hover:underline">Go monthly for $19.99</button>]
                        </form>
                    </div>

                    <div class="flex space-x-0.5">
                        <form method="POST" action="{{ route('plan.update', 2) }}">
                            @csrf
                            @method('PUT')

                            [<button class="hover:underline">Go yearly for $99.99/year</button>]
                        </form>

                        <div>|</div>

                        <form method="POST" action="{{ route('plan.update', 4) }}">
                            @csrf
                            @method('PUT')

                            [<button class="hover:underline">Go yearly for $199.99</button>]
                        </form>
                    </div>--}}
                </div>
            </div>
        </div>
    </div>
    <script>
        const setup = () => {
            return {
                billPlan: 'monthly',

                plans: [
                    {
                        route: {
                            monthly: '{{ route('plan.update', 1) }}',
                            annually: '{{ route('plan.update', 2) }}',
                        },
                        name: 'Silver',
                        price: {
                            monthly: 9.99,
                            annually: 99.99,
                        },
                        features: ['10 tasks'],
                    },
                    {
                        route: {
                            monthly: '{{ route('plan.update', 3) }}',
                            annually: '{{ route('plan.update', 4) }}',
                        },
                        name: 'Gold',
                        price: {
                            monthly: 19.99,
                            annually: 199.99,
                        },
                        features: ['Unlimited tasks'],
                    },
                ],
            }
        }

        </x-app-layout>
