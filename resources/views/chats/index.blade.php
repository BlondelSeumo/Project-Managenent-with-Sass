@extends('layouts.main')
@push('style')
    <style>
        .chatCard ::-webkit-scrollbar {
            width: 7px;
        }

        /* Track */
        .chatCard ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        .chatCard ::-webkit-scrollbar-thumb {
            background: #a7a7a7;
        }

        /* Handle on hover */
        .chatCard ::-webkit-scrollbar-thumb:hover {
            background: #929292;
        }

        li {
            list-style: none;
        }

        .user-wrapper, .message-wrapper {
            overflow-y: auto;
        }

        /*.user-wrapper {*/
        /*    height: 600px;*/
        /*}*/

        .user {
            cursor: pointer;
            padding: 5px 0;
            position: relative;
        }

        .user:hover {
            background: #f9f9f9 !important;
        }

        .user:last-child {
            margin-bottom: 0;
        }

        .pending {
            position: absolute;
            left: 13px;
            top: 9px;
            background: #b600ff;
            margin: 0;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            line-height: 18px;
            padding-left: 5px;
            color: #ffffff;
            font-size: 12px;
        }

        .media-left {
            margin: 0 10px;
        }

        .media-left img {
            width: 64px;
            border-radius: 64px;
        }

        .media-body p {
            margin: 6px 0;
        }

        .message-wrapper {
            padding: 10px;
            height: 536px;
            background: #f9f9f9 !important;
        }

        .messages .message {
            margin-bottom: 15px;
        }

        .messages .message:last-child {
            margin-bottom: 0;
        }

        .received, .sent {
            width: 90%;
            padding: 3px 10px;
            border-radius: 10px;
        }
        .received {
            background: #ffffff;
        }

        .sent {
            background: #eee;
            float: right;
            text-align: left;
        }

        .message p {
            margin: 0;
            line-height: 1.5;
        }

        .date {
            color: #777777;
            font-size: 10px;
        }

        .active {
            background: #f9f9f9 !important;
        }
    </style>
@endpush
@section('content')
    <section class="section">
        @if($currantWorkspace || Auth::user()->type == 'admin')
            {{--<div class="row mb-2">
                <div class="col-sm-4">
                    <h2 class="section-title">{{ __('Chats') }}</h2>
                </div>
            </div>--}}
            <div class="row mt-4">
                <div class="col-md-12 col-lg-12 col-sm-12">
                    <div class="card chatCard">
                        <div class="card-header">
                            <h4 class="d-inline">{{__('Chats')}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-lg-3 col-sm-3 col-xl-3">
                                    <div class="user-wrapper rounded">
                                        <ul class="list-unstyled list-unstyled-border users">
                                            @foreach ($users as $user)
                                                <li class="media p-3 mb-0 user" id="{{ $user->id }}">
                                                    <img class="mr-3 rounded-circle" width="50" @if($user->avatar) src="{{asset('/storage/avatars/'.$user->avatar)}}" @else avatar="{{ $user->name }}" @endif alt="avatar">
                                                    <div class="media-body">

                                                        <div class="mt-0 mb-1 font-weight-bold">{{ $user->name }}</div>
                                                        <div class="text-small font-weight-600 text-muted">{{$user->email}}</div>
                                                    </div>
                                                    @if($unread = $user->unread($currantWorkspace->id,$user->id))
                                                        <span class="pending">{{ $unread }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-9 col-lg-9 col-sm-9 col-xl-9" id="messages">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
@push('scripts')

    <script>
        var receiver_id = '';
        var my_id = "{{ Auth::id() }}";
        var workspaceId = "{{ $currantWorkspace->id }}";
        {{--var workspaceSlug = "{{ $currantWorkspace->slug }}";--}}

        $(document).ready(function () {
            // ajax setup form csrf token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Enable pusher logging - don't include this in production
            Pusher.logToConsole = false;

            var pusher = new Pusher('{{env('PUSHER_APP_KEY')}}', {
                cluster: '{{env('PUSHER_APP_CLUSTER')}}',
                forceTLS: true
            });

            var channel = pusher.subscribe('{{$currantWorkspace->slug}}');
            channel.bind('chat', function (data) {
                // alert(JSON.stringify(data));
                if (my_id == data.from) {

                } else if (my_id == data.to) {
                    if (receiver_id == data.from) {
                        // if receiver is selected, reload the selected user ...
                        $('#' + data.from).click();
                    } else {
                        // if receiver is not seleted, add notification for that user
                        var pending = parseInt($('#' + data.from).find('.pending').html());

                        if (pending) {
                            $('#' + data.from).find('.pending').html(pending + 1);
                        } else {
                            $('#' + data.from).append('<span class="pending">1</span>');
                        }
                    }
                }
            });

            $('.user').click(function () {
                $('.user').removeClass('active');
                $(this).addClass('active');
                $(this).find('.pending').remove();

                receiver_id = $(this).attr('id');
                $.ajax({
                    type: "get",
                    url: "{{ URL::to('/') }}/" + workspaceId + "/message/" + receiver_id, // need to create this route
                    data: "",
                    cache: false,
                    success: function (data) {
                        $('#messages').html(data);
                        scrollToBottomFunc();
                    }
                });
            });

            $(document).on('keyup', '.chat-box .submit', function (e) {
                var message = $(this).val();
                // check if enter key is pressed and message is not null also receiver is selected
                if (e.keyCode == 13 && message != '' && receiver_id != '' && workspaceId != '') {
                    sand();
                }
            });
            $(document).on('click', '.chat-box button', function (e) {
                var message = $('.chat-box .submit').val();
                // check if enter key is pressed and message is not null also receiver is selected
                if (message != '' && receiver_id != '' && workspaceId != '') {
                    sand();
                }
            });
        });

        function sand() {
            var message = $('.chat-box .submit').val();
            $('.chat-box .submit').val(''); // while pressed enter text box will be empty

            var datastr = "workspace_id=" + workspaceId + "&receiver_id=" + receiver_id + "&message=" + message;
            $.ajax({
                type: "post",
                url: "message", // need to create this post route
                data: datastr,
                cache: false,
                success: function (data) {
                    $('#' + data.to).click();
                },
                error: function (jqXHR, status, err) {
                },
                complete: function () {
                    scrollToBottomFunc();
                }
            })

        }

        // make a function to scroll down auto
        function scrollToBottomFunc() {
            $('.message-wrapper').animate({
                scrollTop: $('.message-wrapper').get(0).scrollHeight
            }, 50);
        }
    </script>
@endpush
