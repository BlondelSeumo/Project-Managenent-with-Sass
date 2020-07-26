@extends('layouts.main')

@section('content')

    @auth('client')
        <?php
        $permisions = Auth::user()->getPermission($project->id);
        ?>
    @endauth

    <section class="section">

        <div class="row mb-2">
            <div class="col-sm-4">
                <h2 class="section-title">
                    {{ __('Bug Report')}}
                </h2>
            </div>
            <div class="col-sm-8">
                <div class="text-sm-right">
                    <div class="mt-4">
                        <a href="{{route('projects.show',[$currantWorkspace->slug,$project->id])}}" class="btn btn-primary ml-3"><i class="mdi mdi-arrow-left"></i> {{ __('Back')}}</a>
                        @if($currantWorkspace && $currantWorkspace->permission == 'Owner')
                            <a href="#" class="btn btn-primary" data-ajax-popup="true" data-size="lg"
                               data-title="{{ __('Create New Bug') }}"
                               data-url="{{route('projects.bug.report.create',[$currantWorkspace->slug,$project->id])}}"><i class="mdi mdi-plus"></i> {{ __('Add New')}}</a>
                        @elseif(isset($permisions) && in_array('create bug report',$permisions))
                            <a href="#" class="btn btn-primary" data-ajax-popup="true" data-size="lg"
                               data-title="{{ __('Create New Bug') }}"
                               data-url="{{route('client.projects.bug.report.create',[$currantWorkspace->slug,$project->id])}}"><i class="mdi mdi-plus"></i> {{ __('Add New')}}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($project && $currantWorkspace)
            <div class="row">
                <div class="col-12">

                    <div class="board" data-plugin="dragula" data-containers='{{json_encode($statusClass)}}'>

                        @foreach($stages as $stage)
                            <div class="tasks animated">
                                <div class="mt-0 task-header text-uppercase">{{__(ucwords($stage->name))}} (<span class="count">{{count($stage->bugs)}}</span>)</div>
                                <div id="{{'task-list-'.str_replace(' ','_',$stage->id)}}" data-status="{{$stage->id}}" class="task-list-items">
                                @foreach($stage->bugs as $taskDetail)

                                    <div class="card mb-0" id="{{$taskDetail->id}}">
                                        <div class="card-body p-3">
                                            @if($currantWorkspace->permission == 'Owner' || isset($permisions))
                                            <div class="dropdown float-right">
                                                <a href="#" class="dropdown-toggle text-muted" data-toggle="dropdown" aria-expanded="false">
                                                    <i class="dripicons-gear"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                @if($currantWorkspace->permission == 'Owner')
                                                    <a href="#" class="dropdown-item" data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Bug') }}" data-url="{{route('projects.bug.report.edit',[$currantWorkspace->slug,$taskDetail->project_id,$taskDetail->id])}}">
                                                        <i class="mdi mdi-pencil mr-1"></i>{{__('Edit')}}</a>
                                                    <a href="#" class="dropdown-item" onclick="(confirm('{{__('Are you sure ?')}}')?document.getElementById('delete-form-{{$taskDetail->id}}').submit(): '');">
                                                        <i class="mdi mdi-delete mr-1"></i>{{__('Delete')}}</a>
                                                    <form id="delete-form-{{$taskDetail->id}}" action="{{ route('projects.bug.report.destroy',[$currantWorkspace->slug,$taskDetail->project_id,$taskDetail->id]) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @elseif(isset($permisions))
                                                    @if(in_array('edit bug report',$permisions))
                                                        <a href="#" class="dropdown-item" data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Bug') }}" data-url="{{route('client.projects.bug.report.edit',[$currantWorkspace->slug,$taskDetail->project_id,$taskDetail->id])}}">
                                                            <i class="mdi mdi-pencil mr-1"></i>{{__('Edit')}}
                                                        </a>
                                                    @endif
                                                    @if(in_array('delete bug report',$permisions))
                                                        <a href="#" class="dropdown-item" onclick="(confirm('{{__('Are you sure ?')}}')?document.getElementById('delete-form-{{$taskDetail->id}}').submit(): '');">
                                                            <i class="mdi mdi-delete mr-1"></i>{{__('Delete')}}
                                                        </a>
                                                        <form id="delete-form-{{$taskDetail->id}}" action="{{ route('client.projects.bug.report.destroy',[$currantWorkspace->slug,$taskDetail->project_id,$taskDetail->id]) }}" method="POST" style="display: none;">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    @endif
                                                @endif
                                                </div>
                                            </div>
                                            @endif
                                            <div>
                                                <a href="#" data-ajax-popup="true" data-size="lg" data-title="{{$taskDetail->title}} @if($taskDetail->priority=="High")<span class='badge badge-danger ml-2'>{{ __('High')}}</span>@elseif($taskDetail->priority=="Medium")<span class='badge badge-info'>{{ __('Medium')}}</span>@else<span class='badge badge-success'>{{ __('Low')}}</span>@endif" data-url="@auth('web'){{route('projects.bug.report.show',[$currantWorkspace->slug,$taskDetail->project_id,$taskDetail->id])}}@elseauth{{route('client.projects.bug.report.show',[$currantWorkspace->slug,$taskDetail->project_id,$taskDetail->id])}}@endauth"
                                                   class="text-body">{{$taskDetail->title}}</a>
                                            </div>
                                            @if($taskDetail->priority=="High")
                                                <span class="badge badge-danger">{{ __('High')}}</span>
                                            @elseif($taskDetail->priority=="Medium")
                                                <span class="badge badge-info">{{ __('Medium')}}</span>
                                            @else
                                                <span class="badge badge-success">{{ __('Low')}}</span>
                                            @endif

                                            <p class="mt-2 mb-2">
                                                <span class="text-nowrap d-inline-block">
                                                    <i class="mdi mdi-comment-multiple-outline text-muted"></i>
                                                    <b>{{count($taskDetail->comments)}}</b> {{ __('Comments')}}
                                                </span>
                                            </p>

                                            <small class="float-right text-muted mt-2">{{Utility::dateFormat($taskDetail->due_date)}}</small>
                                            @if($currantWorkspace->permission == 'Owner' || isset($permisions))
                                                <figure class="avatar mr-2 avatar-sm animated" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{$taskDetail->user->name}}">
                                                    <img @if($taskDetail->user->avatar) src="{{asset('/storage/avatars/'.$taskDetail->user->avatar)}}" @else avatar="{{ $taskDetail->user->name }}"@endif class="rounded-circle">
                                                </figure>
                                                <span class="align-middle">{{$taskDetail->user->name}}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div> <!-- end .board-->
                </div> <!-- end col -->
            </div>
        @else
            <div class="container mt-5">
                <div class="page-error">
                    <div class="page-inner">
                        <h1>404</h1>
                        <div class="page-description">
                            {{ __('Page Not Found') }}
                        </div>
                        <div class="page-search">
                            <p class="text-muted mt-3">{{ __('It\'s looking like you may have taken a wrong turn. Don\'t worry... it happens to the best of us. Here\'s a little tip that might help you get back on track.')}}</p>
                            <div class="mt-3">
                                <a class="btn btn-info mt-3" href="{{route('home')}}"><i class="mdi mdi-reply"></i> {{ __('Return Home')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


    </section>

@endsection
@if($project && $currantWorkspace)
    @push('style')
        <style>
            .task-list-items:before {
                content: "{{__('No Tasks')}}";
            }
        </style>
    @endpush
@push('scripts')
    <!-- third party js -->
    <script src="{{ asset('assets/js/vendor/dragula.min.js') }}"></script>
    <script>
        !function (a) {
            "use strict";
            var t = function () {
                this.$body = a("body")
            };
            t.prototype.init = function () {
                a('[data-plugin="dragula"]').each(function () {
                    var t = a(this).data("containers"), n = [];
                    if (t) for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]); else n = [a(this)[0]];
                    var r = a(this).data("handleclass");
                    r ? dragula(n, {
                        moves: function (a, t, n) {
                            return n.classList.contains(r)
                        }
                    }) : dragula(n).on('drop', function (el, target, source, sibling) {
                        // console.log(el);
                        // console.log(source);
                        // console.log(target);
                        // console.log(sibling);

                        var sort = [];
                        $("#"+target.id+" > div").each(function () {
                            sort[$(this).index()]=$(this).attr('id');
                        });

                        var id = el.id;
                        var old_status = $("#"+source.id).data('status');
                        var new_status = $("#"+target.id).data('status');
                        var project_id = '{{$project->id}}';

                        $("#"+source.id).parent().find('.count').text($("#"+source.id+" > div").length);
                        $("#"+target.id).parent().find('.count').text($("#"+target.id+" > div").length);
                        $.ajax({
                            url:'@auth('web'){{route('projects.bug.report.update.order',[$currantWorkspace->slug,$project->id])}}@elseauth{{route('client.projects.bug.report.update.order',[$currantWorkspace->slug,$project->id])}}@endauth',
                            type:'PUT',
                            data:{id:id,sort:sort,new_status:new_status,old_status:old_status,project_id:project_id,"_token":$('meta[name="csrf-token"]').attr('content')},
                            success: function(data){
                                // console.log(data);
                            }
                        });
                        // console.log(id);
                        // console.log(status);
                        // console.log(project_id);

                    });


                })
            }, a.Dragula = new t, a.Dragula.Constructor = t
        }(window.jQuery), function (a) {
            "use strict";
            @auth('client')
                @if(isset($permisions) && in_array('move bug report',$permisions))
                    a.Dragula.init()
                @endif
            @elseauth
            a.Dragula.init()
            @endauth
        }(window.jQuery);
    </script>
    <!-- third party js ends -->
    <script>
        $(document).on('click','#form-comment button',function (e) {
            var comment = $.trim($("#form-comment textarea[name='comment']").val());
            if(comment != ''){
                $.ajax({
                    url:$("#form-comment").data('action'),
                    data:{comment:comment,"_token":$('meta[name="csrf-token"]').attr('content')},
                    type:'POST',
                    success:function (data) {
                        data = JSON.parse(data);

                        if(data.user_type == 'Client'){
                            var avatar = "avatar='"+data.client.name+"'";
                            var html = "<li class='media'>" +
                                "                    <img class='mr-3 avatar-sm rounded-circle img-thumbnail' width='60' "+avatar+" alt='"+data.client.name+"'>" +
                                "                    <div class='media-body'>" +
                                "                        <h5 class='mt-0'>"+data.client.name+"</h5>" +
                                "                        "+data.comment +
                                "                    </div>" +
                                "                </li>";
                        }else{
                            var avatar = (data.user.avatar)?"src='{{asset('/storage/avatars/')}}/"+data.user.avatar+"'":"avatar='"+data.user.name+"'";
                            var html = "<li class='media'>" +
                                "                    <img class='mr-3 avatar-sm rounded-circle img-thumbnail' width='60' "+avatar+" alt='"+data.user.name+"'>" +
                                "                    <div class='media-body'>" +
                                "                        <h5 class='mt-0'>"+data.user.name+"</h5>" +
                                "                        "+data.comment +
                                "                           <div class='float-right'>"+
                                "                               <a href='#' class='text-danger text-muted delete-comment' data-url='"+data.deleteUrl+"'>"+
                                "                                   <i class='dripicons-trash'></i>"+
                                "                               </a>"+
                                "                           </div>"+
                                "                    </div>" +
                                "                </li>";
                        }




                        $("#comments").prepend(html);
                        LetterAvatar.transform();
                        $("#form-comment textarea[name='comment']").val('');
                        toastr('Success','{{ __("Comment Added Successfully!")}}','success');
                    },
                    error:function (data) {
                        toastr('{{__('Error')}}', '{{ __("Some Thing Is Wrong!")}}', 'error');
                    }
                });
            }
            else{
                toastr('{{__('Error')}}','{{ __("Please write comment!")}}','error');
            }
        });
        $(document).on("click",".delete-comment",function(){
            if(confirm('{{__('Are you sure ?')}}')) {
                var btn = $(this);
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'DELETE',
                    data: {_token: $('meta[name="csrf-token"]').attr('content')},
                    dataType: 'JSON',
                    success: function (data) {
                        toastr('Success', '{{ __("Comment Deleted Successfully!")}}', 'success');
                        btn.closest('.media').remove();
                    },
                    error: function (data) {
                        data = data.responseJSON;
                        if (data.message) {
                            toastr('{{__('Error')}}', data.message, 'error');
                        } else {
                            toastr('{{__('Error')}}', '{{ __("Some Thing Is Wrong!")}}', 'error');
                        }
                    }
                });
            }
        });
        $(document).on('submit','#form-file',function (e) {

            e.preventDefault();
            $.ajax({
                url: $("#form-file").data('action'),
                type: 'POST',
                data: new FormData(this),
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success: function(data)
                {
                   toastr('Success','{{ __("Comment Added Successfully!")}}','success');
                    // console.log(data);
                    var delLink = '';

                    if(data.deleteUrl.length > 0){
                        delLink = "<a href='#' class='text-danger text-muted delete-comment-file'  data-url='"+data.deleteUrl+"'>" +
                            "                                        <i class='dripicons-trash'></i>" +
                            "                                    </a>";
                    }

                    var html = "<div class='card mb-1 shadow-none border'>" +
                        "                        <div class='card-body pt-2 pb-2'>" +
                        "                            <div class='row align-items-center'>" +
                        "                                <div class='col-auto'>" +
                        "                                    <div class='avatar-sm'>" +
                        "                                        <span class='avatar-title rounded text-uppercase'>" +
                        data.extension +
                        "                                        </span>" +
                        "                                    </div>" +
                        "                                </div>" +
                        "                                <div class='col pl-0'>" +
                        "                                    <a href='#' class='text-muted font-weight-bold'>"+data.name+"</a>" +
                        "                                    <p class='mb-0'>"+data.file_size+"</p>" +
                        "                                </div>" +
                        "                                <div class='col-auto'>" +
                        "                                    <!-- Button -->" +
                        "                                    <a download href='{{asset('/storage/tasks/')}}/"+data.file+"' class='btn btn-link text-muted'>" +
                        "                                        <i class='dripicons-download'></i>" +
                        "                                    </a>" +
                        delLink +
                        "                                </div>" +
                        "                            </div>" +
                        "                        </div>" +
                        "                    </div>";
                    $("#comments-file").prepend(html);
                },
                error: function(data)
                {
                    data = data.responseJSON;
                    if(data.message) {
                        toastr('{{__('Error')}}', data.message, 'error');
                        $('#file-error').text(data.errors.file[0]).show();
                    }
                    else{
                       toastr('{{__('Error')}}', '{{ __("Some Thing Is Wrong!")}}', 'error');
                    }
                }
            });
        });
        $(document).on("click",".delete-comment-file",function(){
            if(confirm('{{__('Are you sure ?')}}')) {
                var btn = $(this);
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'DELETE',
                    data: {_token: $('meta[name="csrf-token"]').attr('content')},
                    dataType: 'JSON',
                    success: function (data) {
                        toastr('Success', '{{ __("File Deleted Successfully!")}}', 'success');
                        btn.closest('.border').remove();
                    },
                    error: function (data) {
                        data = data.responseJSON;
                        if (data.message) {
                            toastr('{{__('Error')}}', data.message, 'error');
                        } else {
                            toastr('{{__('Error')}}', '{{ __("Some Thing Is Wrong!")}}', 'error');
                        }
                    }
                });
            }
        });
    </script>
@endpush
@endif
