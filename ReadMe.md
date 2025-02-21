Router that hopefully can be used for as many server setup as possible. 

Still WIP tho.

Why? Because for some reason route class in any framework always ended up dictating the project's structure and architecture in a small or big way that i don't enjoy very much.

#example

-To set it up

Request::dispatch($uri, $request_method, $some_packet_that_you_may_want_to_use_in_your_app_like_header_and_stuff_which_i_would_advise_to_use_class)

-To register new route
Route::add('/:id')
    ->on(['post'], function($your_packet, $url_param_if_exist){
        #your_logic
    })