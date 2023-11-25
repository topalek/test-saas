<x-app-layout>
    <div class="container">
        <div class="row">
            <div class="card bg-blueGray-100">
                <div class="card-header">
                    <div class="card-header-container">
                        <h6 class="card-title">
                            {{ trans('global.edit') }}
                            {{ trans('cruds.task.title_singular') }}:
                            {{ trans('cruds.task.fields.id') }}
                            {{ $task->id }}
                        </h6>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{route('tasks.update',$task)}}" method="post" class="pt-3">
                        @csrf
                        @method('patch')
                        <div class="form-group mb-2 {{ $errors->has('task.task') ? ' invalid' : '' }}">
                            <label class="form-label required" for="task">{{ trans('cruds.task.fields.task') }}</label>
                            <input class="form-control" type="text" value="{{old('task',$task->task)}}" name="task"
                                   id="task" required>
                            <div class="validation-message">
                                {{ $errors->first('task.task') }}
                            </div>
                            <div class="help-block">
                                {{ trans('cruds.task.fields.task_helper') }}
                            </div>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-success mr-2" type="submit">
                                {{ trans('global.save') }}
                            </button>
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                                {{ trans('global.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
