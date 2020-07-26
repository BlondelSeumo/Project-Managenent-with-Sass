<form class="pl-3 pr-3" method="post" enctype="multipart/form-data" action="{{ route('plans.update',$plan->id) }}">
    @csrf
    @method('put')
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label for="name">{{ __('Name') }} *</label>
                <input type="text" class="form-control" id="name" name="name" value="{{$plan->name}}" required/>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label for="price">{{ __('Price') }} *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                    </div>
                    <input type="number" min="0" class="form-control" id="price" name="price" value="{{$plan->price}}" required/>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label for="duration">{{ __('Duration') }} *</label>
                <select name="duration" id="duration" class="form-control">
                    @foreach($plan->arrDuration() as $key => $duration)
                        <option value="{{$key}}" @if($plan->duration == $key) selected @endif>{{__($duration)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label for="image">{{ __('Image') }}</label>
                <input type="file" id="image" class="form-control" name="image" accept="image/*"/>
                <span><small>Please upload a valid image file. Size of image should not be more than 2MB.</small></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label for="max_workspaces">{{ __('Maximum Workspaces') }} *</label>
                <input type="number"  class="form-control" id="max_workspaces" name="max_workspaces" value="{{$plan->max_workspaces}}" required/>
                <span><small>{{__('Note: "-1" for Unlimited')}}</small></span>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label for="max_users">{{ __('Maximum Users Per Workspace') }} *</label>
                <input type="number"  class="form-control" id="max_users" name="max_users" value="{{$plan->max_users}}" required/>
                <span><small>{{__('Note: "-1" for Unlimited')}}</small></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <label for="max_clients">{{ __('Maximum Clients Per Workspace') }} *</label>
                <input type="number"  class="form-control" id="max_clients" name="max_clients" value="{{$plan->max_clients}}" required/>
                <span><small>{{__('Note: "-1" for Unlimited')}}</small></span>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label for="max_projects">{{ __('Maximum Projects Per Workspace') }} *</label>
                <input type="number"  class="form-control" id="max_projects" name="max_projects" value="{{$plan->max_projects}}" required/>
                <span><small>{{__('Note: "-1" for Unlimited')}}</small></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <label for="description">{{ __('Description') }}</label>
                <textarea class="form-control" id="description" name="description">{{$plan->description}}</textarea>
            </div>
        </div>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{{ __('Update Plan') }}</button>
    </div>
</form>