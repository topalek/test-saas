<x-app-layout>
    <div class="container">
        <div class="row">
            <div class="card bg-blueGray-100 mt-4">
                <div class="card-header">
                    <div class="card-header-container">
                        <h6 class="card-title">
                            {{ trans('global.view') }}
                            {{ trans('cruds.task.title_singular') }}:
                            {{ trans('cruds.task.fields.id') }}
                            {{ $task->id }}
                        </h6>
                    </div>
                </div>

                <div class="card-body">
                    <div class="pt-3">
                        <table class="table table-view">
                            <tbody class="bg-white">
                            <tr>
                                <th>
                                    {{ trans('cruds.task.fields.id') }}
                                </th>
                                <td>
                                    {{ $task->id }}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    {{ trans('cruds.task.fields.task') }}
                                </th>
                                <td>
                                    {{ $task->task }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary mr-2">
                            {{ trans('global.edit') }}
                        </a>
                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                            {{ trans('global.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
