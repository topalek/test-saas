<x-app-layout>
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
                                            <a class="btn btn-sm btn-info mr-2" href="{{ route('tasks.show', $task) }}">
                                                {{ trans('global.view') }}
                                            </a>
                                            <a class="btn btn-sm btn-success mr-2"
                                               href="{{ route('tasks.edit', $task) }}">
                                                {{ trans('global.edit') }}
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
