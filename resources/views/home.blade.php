@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="selectCountry"> @lang('friends.choiceCountry')</label>
                    <select class="form-control" id="selectCountry">
                        <option value="0">@lang('friends.select')</option>
                        @foreach ($languages as $lang)
                            <option value="{{$lang['language_id']}}">{{$lang['language_name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <button class="btn btn-default col-md-12" id="findFriendDBMarker" style=" margin-top: 10%;">@lang('friends.searchByDatabase') </button>
                    <button class="btn btn-default col-md-12" id="findFriendCache" style=" margin-top: 10%;">@lang('friends.searchByCache')</button>

                </div>
            </div>
        </div>
    </div>
    <div class="container col-md-12">
        <div class="row" id="tableUsers">
        </div>
    </div>
    <script>
        $("#findFriendDBMarker").click(function () {
            getLanguage('dbMarker')
        });
        $("#findFriendCache").click(function () {
            getLanguage('collection');
        });
        function getLanguage(type) {
            var lang = $("#selectCountry").find(":selected").val();
            if (lang == 0) {
                alert("@lang('friends.searchByCache')");
            } else {
                sendRequest({'language': lang, 'type': type})
            }
        }
        function sendRequest(data) {
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: '{!! url("users/get") !!}', // This is the url we gave in the route
                data: data, // a JSON object to send back
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) { // What to do if we succeed

                    response = JSON.parse(response);
                    if (response.clearData == 1) {
                        $('#tableUsers').empty();
                        alert("@lang('friends.changedCountryOrLanguage')");

                    }else if(response.clearData == 2){
                        $('#tableUsers').empty();
                        alert("@lang('friends.somethingGoWrong')");
                    }
                    $.each(response.records, function (i, item) {
                        $('#tableUsers').append("<div class='col-md-6'>" + item.real_name + "</div>")
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) { // What to do if we fail
                }
            });
        }
        function renderRows() {

        }
    </script>
@endsection
