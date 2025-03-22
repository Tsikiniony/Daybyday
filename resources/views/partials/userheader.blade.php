<div class="col-md-6">
    <div class="panel panel-primary contact-header-box">
        <div class="panel-body">
            @if(\Route::getCurrentRoute()->getName() != "users.show")
            <a href="{{route('users.show', isset($contact) ? $contact->external_id : $user->external_id)}}"><i class="ion ion-ios-redo " title="{{ __('Go to user') }}" style="
                float: right;
                margin-right: 1em;
                color:#61788b;
                "></i></a>
            @endif
            <div class="col-sm-2">
                <div class="profilepic"><img class="profilepicsize" src="{{ isset($contact) ? $contact->avatar : $user->avatar }}"/></div>
            </div>
            <div class="col-sm-8">
            <?php isset($changeUser) ?: $changeUser = false ?>
            @if($changeUser == false )
                    <p class="name-text">{{ isset($contact) ? $contact->name : $user->name }}</p>
            @else

               <span id="assignee-user" class="siderbar-list-value name-text"> {{ isset($contact) ? $contact->name : $user->name }}
                   @if(Entrust::can('client-update'))
                       <i class="icon ion-md-create"></i>
                   @endif
                    </span>
                @if(Entrust::can('client-update'))
                    <span id="assignee-picker" class="hidden">
                        <form method="POST" action="{{url('clients/updateassign', $client->external_id)}}">
                            {{csrf_field()}}
                            <select name="user_external_id"
                                    class="small-form-control bootstrap-select assignee-selectpicker dropdown-user-selecter pull-right"
                                    id="user-search-select" data-live-search="true"
                                    data-style="btn btn-sm dropdown-toggle btn-light"
                                    data-container="body"
                                    onchange="this.form.submit()">
                                @foreach(\App\Models\User::all()->pluck('nameAndDepartment', 'external_id') as $key => $user)
                                    <option {{(isset($contact) ? $contact->external_id : $user->external_id) == $key ? 'selected' : ''}} data-tokens="{{$user}}" value="{{$key}}">{{$user}}</option>
                                @endforeach
                            </select>
                        </form>
                    </span>
                @endif
            @endif
                <p class="department-text">
                    {{isset($contact) ? $contact->department()->first()->name : $user->department()->first()->name}}
                </p>
                <!--MAIL-->
                @if(isset($contact) ? $contact->email : $user->email)
                    <p class="contact-paragraph">
                        <a href="mailto:{{isset($contact) ? $contact->email : $user->email}}">{{isset($contact) ? $contact->email : $user->email}}</a>
                    </p>
                    <!--Work Phone-->
                @endif
                @if(isset($contact) ? $contact->primary_number : $user->primary_number)
                    <p class="contact-paragraph">
                        <a href="tel:{{isset($contact) ? $contact->primary_number : $user->primary_number}}">{{isset($contact) ? $contact->primary_number : $user->primary_number}}</a>

                        @endif
                        @if((isset($contact) ? $contact->secondary_number : $user->secondary_number) && (isset($contact) ? $contact->primary_number : $user->primary_number))
                            /
                    @endif
                    @if(isset($contact) ? $contact->secondary_number : $user->secondary_number)
                        <!--Personal Phone-->
                            <a href="tel:{{isset($contact) ? $contact->secondary_number : $user->secondary_number}}">{{isset($contact) ? $contact->secondary_number : $user->secondary_number}}</a>
                    </p>
                @endif
            </div>
        </div>

    </div>
</div>

@if($changeUser == true)
@push('scripts')
    <script>
        $(document).ready(function () {
            $('.assignee-selectpicker').selectpicker()
            $('#assignee-user').on('click',function(){
                if($("#assignee-picker").hasClass("hidden")) {
                    $("#assignee-picker").removeClass("hidden");
                    $("#assignee-user").addClass("hidden");
                }
            });

            $('body').on('click',function(e){
                var container = $("#assignee-picker");

                // if the target of the click isn't the container nor a descendant of the container
                if (!container.is(e.target) && container.has(e.target).length === 0)
                {
                    if ($("#assignee-user").is(e.target)) {
                        return
                    }

                    container.addClass("hidden");
                    $("#assignee-user").removeClass("hidden");
                }

            });
        });

    </script>
@endpush
@endif
