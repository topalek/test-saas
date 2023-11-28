<x-app-layout>
    @section('ststus')
    <div class="container">
        <div class="row">
            <div class="card bg-white mt-5">
                <div class="card-header border-b border-blueGray-200">
                    <div class="card-header-container d-flex align-content-center justify-between">
                        <h6 class="card-title m-0">
                            {{ trans('cruds.task.title_singular') }}
                            {{ trans('global.list') }}
                        </h6>

                        <a class="btn btn-primary" href="{{ route('tasks.create') }}">
                            {{ trans('global.add') }} {{ trans('cruds.task.title_singular') }}
                        </a>
                    </div>
                </div>
                <div class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table table-index w-full">
                            <thead>
                            <tr>
                                <th class="w-28">
                                    {{ trans('cruds.task.fields.id') }}
                                </th>
                                <th>
                                    {{ trans('cruds.task.fields.task') }}
                                </th>
                                <th>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>
                                        {{ $task->id }}
                                    </td>
                                    <td>
                                        {{ $task->task }}
                                    </td>
                                    <td>
                                        <div class="flex justify-end">
                                            <a class="btn btn-sm mr-2" href="{{ route('tasks.show', $task) }}">
                                                <svg class="text-primary w-6 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <path fill="currentColor"
                                                          d="M12 9a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3m0 8a5 5 0 0 1-5-5a5 5 0 0 1 5-5a5 5 0 0 1 5 5a5 5 0 0 1-5 5m0-12.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5Z"/>
                                                </svg>
                                            </a>
                                            <a class="btn btn-sm mr-2"
                                               href="{{ route('tasks.edit', $task) }}">
                                                <svg class="text-primary w-6 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <g fill="currentColor">
                                                        <path
                                                            d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157l3.712 3.712l1.157-1.157a2.625 2.625 0 0 0 0-3.712Zm-2.218 5.93l-3.712-3.712l-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"/>
                                                        <path
                                                            d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"/>
                                                    </g>
                                                </svg>
                                            </a>
                                            <x-delete :route="route('tasks.destroy', $task)"/>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">No entries found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-body">
                    <div class="pt-3">
                        {{ $tasks->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
